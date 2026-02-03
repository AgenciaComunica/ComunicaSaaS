<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upload_batches', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('parse_stats');
            $table->unsignedTinyInteger('progress')->default(0)->after('status');
            $table->text('error_message')->nullable()->after('progress');
        });
    }

    public function down(): void
    {
        Schema::table('upload_batches', function (Blueprint $table) {
            $table->dropColumn(['status', 'progress', 'error_message']);
        });
    }
};
