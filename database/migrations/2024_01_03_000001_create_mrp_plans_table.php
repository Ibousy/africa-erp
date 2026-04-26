<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mrp_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity_needed', 12, 2);
            $table->decimal('quantity_available', 12, 2)->default(0);
            $table->decimal('shortage', 12, 2)->default(0);
            $table->date('planned_date');
            $table->enum('status', ['ouvert', 'confirme', 'cloture'])->default('ouvert');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mrp_plans');
    }
};
