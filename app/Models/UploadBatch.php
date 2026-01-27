<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UploadBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'label',
        'meta_csv_path',
        'intelbras_xlsx_path',
        'parse_stats',
        'created_by',
        'parsed_at',
    ];

    protected $casts = [
        'parse_stats' => 'array',
        'parsed_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function metaMonthlies(): HasMany
    {
        return $this->hasMany(MetaMonthly::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function getDisplayLabelAttribute(): string
    {
        if ($this->label) {
            return $this->label;
        }

        return sprintf('%04d-%02d', $this->year, $this->month);
    }
}
