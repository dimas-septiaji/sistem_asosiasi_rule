<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Itemset extends Model
{
    use HasFactory;

    protected $fillable = [
        'analysis_id',
        'itemset_size',
        'itemset_hash',
        'support_count',
        'support',
    ];

    protected $casts = [
        'itemset_size' => 'integer',
        'support_count' => 'integer',
        'support' => 'float',
    ];

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(Analysis::class);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'itemset_items');
    }

    public function antecedentRules(): HasMany
    {
        return $this->hasMany(AssociationRule::class, 'antecedent_itemset_id');
    }

    public function consequentRules(): HasMany
    {
        return $this->hasMany(AssociationRule::class, 'consequent_itemset_id');
    }

    public function unionRules(): HasMany
    {
        return $this->hasMany(AssociationRule::class, 'union_itemset_id');
    }

    /**
     * Helper to get a human-friendly comma-separated list of item names.
     */
    public function getItemsLabelAttribute(): string
    {
        return $this->items->pluck('name')->implode(', ');
    }
}
