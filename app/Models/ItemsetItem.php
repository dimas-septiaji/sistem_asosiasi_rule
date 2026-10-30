<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemsetItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'itemset_id',
        'item_id',
    ];

    public function itemset(): BelongsTo
    {
        return $this->belongsTo(Itemset::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
