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
        Schema::table('feeds', function (Blueprint $table) {
            // 1. Добавляем nullable столбец
            $table->unsignedBigInteger('order_id')->nullable()->after('id');

            // 2. Добавляем внешний ключ
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feeds', function (Blueprint $table) {
            // 1. Удаляем внешний ключ
            $table->dropForeign(['order_id']);
            
            // 2. Удаляем столбец
            $table->dropColumn('order_id');
        });
    }
};
