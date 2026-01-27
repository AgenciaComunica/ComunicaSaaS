<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upload_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->string('label')->nullable();
            $table->string('meta_csv_path')->nullable();
            $table->string('intelbras_xlsx_path')->nullable();
            $table->json('parse_stats')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('parsed_at')->nullable();
            $table->timestamps();

            $table->unique(['year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_batches');
    }
};
