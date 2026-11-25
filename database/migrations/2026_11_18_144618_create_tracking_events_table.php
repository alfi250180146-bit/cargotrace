<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->string('event_type', 20); // STATUS_CHANGE, CHECKPOINT, EXCEPTION
            $table->string('status', 20);     // status shipment SESUDAH event ini
            $table->dateTime('occurred_at');
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_exception')->default(false);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['shipment_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_events');
    }
};