<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('organized_by');
            $table->string('title');
            $table->text('agenda')->nullable();
            $table->text('minutes')->nullable();
            $table->dateTime('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->json('departments');
            $table->string('location')->nullable();
            $table->string('status', 20)->default('scheduled'); // scheduled, done, cancelled
            $table->timestamps();
            $table->index('tenant_id');
        });
    }

    public function down(): void { Schema::dropIfExists('meetings'); }
};
