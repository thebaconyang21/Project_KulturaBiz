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
//         Schema::create('orders', function (Blueprint $table) {
//             $table->id();
//             $table->timestamps();
//         });
//     }

//     /**
//      * Reverse the migrations.
//      */
//     public function down(): void
//     {
//         Schema::dropIfExists('orders');
//     }
// };

// -----------------------------------------------------------------------------

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // Customer who placed the order
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Unique order reference number
            $table->string('order_number')->unique();
            
            // Delivery information
            $table->string('recipient_name');
            $table->text('delivery_address');
            $table->string('contact_number');
            $table->string('city');
            $table->string('province');
            $table->string('postal_code')->nullable();
            
            // Payment
            $table->enum('payment_method', ['cod', 'gcash', 'bank_transfer'])->default('cod');
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            
            // Order status - logistics tracking
            $table->enum('status', [
                'pending',      
                'processing',   
                'shipped',      
                'delivered',    
                'cancelled'     
            ])->default('pending');
            
            // Financial
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            
            // Logistics simulation
            $table->string('courier_name')->nullable();          
            $table->string('tracking_number')->nullable();
            $table->timestamp('estimated_delivery')->nullable();
            $table->text('notes')->nullable();                   
            
            // Status timestamps for tracking history
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
