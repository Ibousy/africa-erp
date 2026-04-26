<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('department_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('from_user_id');
            $table->string('from_department', 50);
            $table->string('to_department', 50);
            $table->text('body')->nullable();
            $table->enum('type', ['text', 'voice'])->default('text');
            $table->string('voice_path', 255)->nullable();
            $table->unsignedInteger('voice_duration')->nullable(); // seconds
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'to_department', 'read_at']);
        });
    }

    public function down(): void { Schema::dropIfExists('department_messages'); }
};
