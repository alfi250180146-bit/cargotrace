<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->string('country');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('type', 20)->default('seaport'); // seaport, airport, warehouse
            $table->timestamps();

            $table->index('country');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};