<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'upload_batch_id',
        'year',
        'month',
        'name',
        'phone',
        'email',
        'first_message',
        'origin',
        'temperature',
        'valor_venda',
        'venda_concluida',
        'raw',
    ];

    protected $casts = [
        'venda_concluida' => 'boolean',
        'raw' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(UploadBatch::class, 'upload_batch_id');
    }
}
