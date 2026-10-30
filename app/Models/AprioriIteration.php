<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AprioriIteration extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'analysis_id',
        'k',
        'candidate_count',
        'frequent_count',
        'duration_ms',
        'created_at',
    ];

    protected $casts = [
        'k' => 'integer',
        'candidate_count' => 'integer',
        'frequent_count' => 'integer',
        'duration_ms' => 'integer',
        'created_at' => 'datetime',
    ];

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(Analysis::class);
    }
}
