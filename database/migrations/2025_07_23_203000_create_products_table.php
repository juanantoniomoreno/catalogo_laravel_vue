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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('main_image_url')->nullable(); // URL de la imagen principal
            $table->string('status')->default('inactive'); // 'active' o 'inactive'
            $table->string('type')->default('simple'); // 'simple', 'option_group', 'pack'
            $table->decimal('price'); //  precio del producto
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
