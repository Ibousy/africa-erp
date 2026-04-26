<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->string('zoom_meeting_id')->nullable()->after('status');
            $table->string('zoom_join_url', 1000)->nullable()->after('zoom_meeting_id');
            $table->string('zoom_start_url', 2000)->nullable()->after('zoom_join_url');
            $table->string('zoom_password')->nullable()->after('zoom_start_url');
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn(['zoom_meeting_id', 'zoom_join_url', 'zoom_start_url', 'zoom_password']);
        });
    }
};
