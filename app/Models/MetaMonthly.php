<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaMonthly extends Model
{
    use HasFactory;

    protected $fillable = [
        'upload_batch_id',
        'year',
        'month',
        'spend',
        'impressions',
        'clicks',
        'ctr',
        'cpc',
        'leads',
        'results',
        'raw_totals',
    ];

    protected $casts = [
        'raw_totals' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(UploadBatch::class, 'upload_batch_id');
    }
}
