<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->enum('status', ['new', 'accepted', 'rejected'])->default('new');
            $table->string('title');
            $table->string('description');
            $table->string('favicon');
            $table->string('color');
            $table->timestamps();

            $table->foreignId('user_id')
                ->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
