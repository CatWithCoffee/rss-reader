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
        Schema::create('feeds', function (Blueprint $table) {
            // Основные поля
            $table->id();
            $table->string('title');
            $table->string('url')->unique();
            $table->string('site_url');
            $table->text('description')->nullable();
            $table->string('language')->nullable();
            $table->string('category')->nullable();

            // Поля для отображения
            $table->string('favicon')->nullable(); // Иконка
            // $table->string('logo')->nullable();
            $table->string('image')->nullable(); // Обложка
            $table->string('color')->nullable(); // Цветовой акцент

            // Технические поля
            $table->timestamp('last_fetched_at')->nullable(); // Время последнего парсинга
            $table->integer('update_frequency')->default(60); // Частота обновления (в минутах)
            $table->integer('items_count')->nullable(); // Количество записей
            $table->boolean('is_active')->default(true); // Активен ли канал
            $table->string('etag')->nullable(); // Для кеширования (HTTP-заголовок)
            $table->string('content_hash', 32)->nullable(); // Для кеширования (содержимое)
            $table->string('last_modified')->nullable(); // Дата последнего изменения ленты
            $table->timestamps(); // Поля created_at и updated_at
            $table->softDeletes(); // Поле deleted_at вместо удаления записей
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feeds');
    }
};
