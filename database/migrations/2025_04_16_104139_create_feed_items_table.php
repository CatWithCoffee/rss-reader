<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feed__items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_id')->constrained(); // Связь с RSS-каналом
            $table->string('guid')->unique();            // Уникальный ID статьи (из RSS)
            $table->string('title');                     // Заголовок
            $table->text('description')->nullable();     // Краткое описание
            $table->text('content')->nullable();         // Полный текст (если есть)
            $table->string('link');                       // Ссылка на статью
            $table->timestamp('published_at')->nullable();           // Дата публикации
            $table->string('thumbnail')->nullable();     // Обложка статьи
            $table->json('authors')->nullable();         // Авторы (может быть массивом)
            $table->json('categories')->nullable();      // Теги/категории (например, ['politics', 'usa'])
            $table->json('enclosures')->nullable();      // Вложения
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed__items');
    }
};
