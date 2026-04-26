<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('erp_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('user_id');
            $table->string('type', 50); // low_stock, material_request, request_response, message, etc.
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('link')->nullable();
            $table->string('icon', 20)->default('bell'); // bell, warning, message, truck, etc.
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read_at']);
            $table->index('tenant_id');
        });
    }

    public function down(): void { Schema::dropIfExists('erp_notifications'); }
};
