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
//         Schema::create('products', function (Blueprint $table) {
//             $table->id();
//             $table->timestamps();
//         });
//     }

//     /**
//      * Reverse the migrations.
//      */
//     public function down(): void
//     {
//         Schema::dropIfExists('products');
//     }
// };

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('user_id')->constrained()->onDelete('cascade');      // artisan
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            
            // Basic product info
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            
            // Product images (stored as JSON array of paths)
            $table->json('images')->nullable();
            
            // Cultural documentation fields
            $table->text('cultural_background')->nullable();   
            $table->string('origin_location')->nullable();     
            $table->text('materials_used')->nullable();       
            
            // Status
            $table->enum('status', ['active', 'inactive', 'out_of_stock'])->default('active');
            
            // Ratings (calculated)
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('review_count')->default(0);
            
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
