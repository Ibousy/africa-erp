<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('employee_id');
            $table->string('period', 7);          // YYYY-QN or YYYY
            $table->unsignedTinyInteger('punctuality_score')->default(0);   // 0-5
            $table->unsignedTinyInteger('productivity_score')->default(0);
            $table->unsignedTinyInteger('quality_score')->default(0);
            $table->unsignedTinyInteger('teamwork_score')->default(0);
            $table->unsignedTinyInteger('initiative_score')->default(0);
            $table->text('strengths')->nullable();
            $table->text('improvements')->nullable();
            $table->text('notes')->nullable();
            $table->date('reviewed_at');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    public function down(): void { Schema::dropIfExists('performance_reviews'); }
};
