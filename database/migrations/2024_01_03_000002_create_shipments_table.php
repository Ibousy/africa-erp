<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50);
            $table->enum('type', ['entrant', 'sortant']);
            $table->string('carrier')->nullable();
            $table->string('contact_name');
            $table->string('origin_destination');
            $table->enum('status', ['en_attente', 'en_transit', 'livre', 'annule'])->default('en_attente');
            $table->date('departure_date')->nullable();
            $table->date('arrival_date')->nullable();
            $table->decimal('weight_kg', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
