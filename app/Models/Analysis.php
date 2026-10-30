<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Analysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'dataset_id',
        'user_id',
        'name',
        'algorithm',
        'min_support',
        'min_confidence',
        'min_lift',
        'status',
        'total_transactions',
        'total_frequent_itemsets',
        'total_rules',
        'started_at',
        'completed_at',
        'duration_ms',
        'error_message',
    ];

    protected $casts = [
        'min_support' => 'float',
        'min_confidence' => 'float',
        'min_lift' => 'float',
        'total_transactions' => 'integer',
        'total_frequent_itemsets' => 'integer',
        'total_rules' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration_ms' => 'integer',
    ];

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function itemsets(): HasMany
    {
        return $this->hasMany(Itemset::class);
    }

    public function associationRules(): HasMany
    {
        return $this->hasMany(AssociationRule::class);
    }

    public function iterations(): HasMany
    {
        return $this->hasMany(AprioriIteration::class)->orderBy('k');
    }
}
