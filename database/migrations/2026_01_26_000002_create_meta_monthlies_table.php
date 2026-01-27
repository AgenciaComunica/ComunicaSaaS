<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_monthlies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upload_batch_id')->constrained('upload_batches')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('spend', 12, 2)->default(0);
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->decimal('ctr', 8, 6)->default(0);
            $table->decimal('cpc', 12, 6)->default(0);
            $table->unsignedBigInteger('leads')->default(0);
            $table->unsignedBigInteger('results')->default(0);
            $table->json('raw_totals')->nullable();
            $table->timestamps();

            $table->unique(['upload_batch_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_monthlies');
    }
};
