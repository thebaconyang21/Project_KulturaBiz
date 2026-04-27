<?php

// use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;

// return new class extends Migration
// {
//     /**
//      * Run the migrations.
//      */
//     public function up(): void
//     {
//         Schema::create('cultural_stories', function (Blueprint $table) {
//             $table->id();
//             $table->timestamps();
//         });
//     }

//     /**
//      * Reverse the migrations.
//      */
//     public function down(): void
//     {
//         Schema::dropIfExists('cultural_stories');
//     }
// };

// -----------------------------------------------------------------

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the cultural_stories table.
     * Dedicated heritage documentation - can exist independently or linked to products.
     */
    public function up(): void
    {
        Schema::create('cultural_stories', function (Blueprint $table) {
            $table->id();
            
            // Optional link to a product
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            
            // Author (artisan or admin)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Story content
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('story');                      
            $table->string('tribe_community');         
            $table->string('location');                 
            $table->string('cultural_significance')->nullable(); 
            $table->text('historical_background')->nullable();   
            
            // Media
            $table->string('cover_image')->nullable();
            $table->json('gallery_images')->nullable();
            
            // Metadata
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cultural_stories');
    }
};