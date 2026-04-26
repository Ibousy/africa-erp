<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('energy_consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('kwh_consumed', 10, 3);
            $table->decimal('cost_per_kwh', 8, 4)->default(0.1);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->integer('hours_used')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_consumptions');
    }
};
