<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('material_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('production_order_id')->nullable();
            $table->unsignedBigInteger('requested_by'); // user_id
            $table->unsignedBigInteger('handled_by')->nullable(); // logistics user_id
            $table->string('reference', 30);
            $table->enum('status', ['pending', 'approved', 'rejected', 'partial'])->default('pending');
            $table->text('notes')->nullable();
            $table->text('logistics_notes')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('material_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('material_request_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity_needed', 12, 3);
            $table->decimal('quantity_available', 12, 3)->nullable();
            $table->enum('item_status', ['pending', 'available', 'insufficient', 'unavailable'])->default('pending');
            $table->string('logistics_note', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_request_items');
        Schema::dropIfExists('material_requests');
    }
};
