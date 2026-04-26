<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('db_name')->nullable()->after('slug');
            $table->unsignedInteger('max_users')->default(5)->after('plan');
            $table->date('subscription_ends_at')->nullable()->after('subscribed_at');
            $table->decimal('monthly_price', 8, 2)->default(0)->after('subscription_ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['db_name', 'max_users', 'subscription_ends_at', 'monthly_price']);
        });
    }
};
