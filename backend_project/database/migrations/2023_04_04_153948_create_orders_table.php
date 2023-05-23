<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->timestamp('orderTime')->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('deliveryTime')->nullable();
            $table->double('price');
            $table->enum('status', ['Created', 'Kitchen', 'Packaging', 'Delivery', 'Delivered', 'Canceled'])->default('Created');
            $table->string('address');
            $table->foreignId('restaurant_id')->constrained('restaurants')->cascadeOnDelete();
            $table->unsignedBigInteger('cook_id')->nullable();
            $table->unsignedBigInteger('courier_id')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
