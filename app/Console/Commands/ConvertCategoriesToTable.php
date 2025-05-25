<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConvertCategoriesToTable extends Command
{
    protected $signature = 'convert:categories';
    protected $description = 'Convert JSON categories to relational structure';

    public function handle()
    {
        DB::table('article_category_pivot')->truncate();
        Category::query()->delete();

        $count = 0;
        $processedCategories = [];

        Article::whereNotNull('categories')->chunk(200, function ($articles) use (&$count, &$processedCategories) {
            foreach ($articles as $article) {
                $rawCategories = $this->getRawCategories($article);
                $categories = $this->normalizeCategories($rawCategories);

                foreach ($categories as $originalName) {
                    // Разделяем составные категории
                    $subCategories = preg_split('/[\/,;|]+/', $originalName);
                    
                    foreach ($subCategories as $name) {
                        $name = $this->cleanCategoryName($name);
                        if (empty($name)) continue;

                        // Приводим первую букву к верхнему регистру
                        $name = mb_convert_case(mb_substr($name, 0, 1), MB_CASE_TITLE, 'UTF-8') . mb_strtolower(mb_substr($name, 1));


                        // Проверяем существование категории (без учета регистра)
                        $normalizedName = mb_strtolower($name);
                        
                        if (!isset($processedCategories[$normalizedName])) {
                            $slug = $this->generateUniqueSlug($name, $processedCategories);
                            
                            try {
                                $category = Category::firstOrCreate(
                                    ['name' => $name], // Уникальность по name
                                    ['slug' => $slug]
                                );
                                $processedCategories[$normalizedName] = $category->id;
                            } catch (\Exception $e) {
                                $this->error("Ошибка создания категории '{$name}': " . $e->getMessage());
                                continue;
                            }
                        }

                        // Связываем статью с категорией
                        try {
                            DB::table('article_category_pivot')->insertOrIgnore([
                                'article_id' => $article->id,
                                'category_id' => $processedCategories[$normalizedName]
                            ]);
                            $count++;
                        } catch (\Exception $e) {
                            $this->warn("Ошибка связи для статьи {$article->id}: " . $e->getMessage());
                        }
                    }
                }
            }
        });

        $this->info("Добавлено связей: {$count}");
        $this->info("Текущее состояние:");
        $this->line("Статей с категориями: " . Article::whereNotNull('categories')->count());
        $this->line("Всего категорий: " . Category::count());
        $this->line("Связей: " . DB::table('article_category_pivot')->count());
    }

    protected function getRawCategories($article): string
    {
        $raw = $article->getRawOriginal('categories');
        return trim($raw, '[]"\'');
    }

    protected function normalizeCategories(string $rawData): array
    {
        if (str_contains($rawData, '", "')) {
            return array_filter(explode('", "', $rawData), fn($item) => !empty(trim($item, '" ')));
        }

        if (str_contains($rawData, ', ')) {
            return array_filter(explode(', ', $rawData), fn($item) => !empty(trim($item)));
        }

        return [trim($rawData)];
    }

    protected function cleanCategoryName(string $name): string
    {
        $name = trim($name, '", ');
        $name = preg_replace('/\s+/', ' ', $name);
        return $name;
    }

    protected function generateUniqueSlug(string $name, array $existingCategories): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        $existingSlugs = array_column($existingCategories, 'slug');
        while (in_array($slug, $existingSlugs)) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }
}