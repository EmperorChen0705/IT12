<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->string('item_id', 12)->primary(); 
            $table->string('itemctgry_id', 10);       

            $table->string('name', 150)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->string('unit', 30)->nullable();  
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('itemctgry_id');

            $table->foreign('itemctgry_id')
                  ->references('itemctgry_id')->on('item_categories')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};