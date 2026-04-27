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
//         Schema::create('reviews', function (Blueprint $table) {
//             $table->id();
//             $table->timestamps();
//         });
//     }

//     /**
//      * Reverse the migrations.
//      */
//     public function down(): void
//     {
//         Schema::dropIfExists('reviews');
//     }
// };

// ----------------------------------------------------------

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            $table->integer('rating');              
            $table->string('title')->nullable();
            $table->text('comment')->nullable();
            
            $table->boolean('is_approved')->default(true);
            
            $table->timestamps();
            
            
            $table->unique(['product_id', 'user_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
