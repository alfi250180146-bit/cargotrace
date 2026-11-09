<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number', 30)->unique();
            $table->foreignId('customer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('carrier_id')->constrained('carriers')->restrictOnDelete();
            $table->foreignId('origin_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('destination_id')->constrained('locations')->restrictOnDelete();
            $table->string('cargo_type');
            $table->decimal('weight_kg', 10, 2);
            $table->decimal('volume_cbm', 10, 2)->nullable();
            $table->dateTime('expected_departure');
            $table->dateTime('expected_arrival');
            $table->dateTime('estimated_arrival')->nullable();
            $table->string('current_status', 20)->default('CREATED');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['current_status', 'carrier_id']);
        });

        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->string('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('weight_kg', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
        Schema::dropIfExists('shipments');
    }
};