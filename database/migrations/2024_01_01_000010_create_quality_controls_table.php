<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('check_date');
            $table->decimal('quantity_checked', 12, 2);
            $table->decimal('quantity_defective', 12, 2)->default(0);
            $table->enum('status', ['passe', 'echoue'])->default('passe');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('defects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quality_control_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->text('description')->nullable();
            $table->decimal('quantity', 12, 2)->default(1);
            $table->enum('severity', ['faible', 'moyen', 'grave'])->default('moyen');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('defects');
        Schema::dropIfExists('quality_controls');
    }
};
