<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssociationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'analysis_id',
        'antecedent_itemset_id',
        'consequent_itemset_id',
        'union_itemset_id',
        'support_count',
        'support',
        'confidence',
        'lift',
    ];

    protected $casts = [
        'support_count' => 'integer',
        'support' => 'float',
        'confidence' => 'float',
        'lift' => 'float',
    ];

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(Analysis::class);
    }

    public function antecedentItemset(): BelongsTo
    {
        return $this->belongsTo(Itemset::class, 'antecedent_itemset_id');
    }

    public function consequentItemset(): BelongsTo
    {
        return $this->belongsTo(Itemset::class, 'consequent_itemset_id');
    }

    public function unionItemset(): BelongsTo
    {
        return $this->belongsTo(Itemset::class, 'union_itemset_id');
    }

    /**
     * Get human explanation without assuming causality.
     * Example: "Sebanyak 75% transaksi yang membeli Roti juga membeli Susu."
     */
    public function getExplanationAttribute(): string
    {
        $antNames = $this->antecedentItemset ? $this->antecedentItemset->items->pluck('name')->implode(', ') : '-';
        $conNames = $this->consequentItemset ? $this->consequentItemset->items->pluck('name')->implode(', ') : '-';
        $confPct = number_format($this->confidence * 100, 2);

        return "Sebanyak {$confPct}% transaksi yang membeli {$antNames} juga membeli {$conNames}.";
    }

    /**
     * Get Lift interpretation.
     */
    public function getLiftInterpretationAttribute(): array
    {
        $lift = round($this->lift, 4);
        if ($lift > 1) {
            return [
                'type' => 'positive',
                'label' => 'Asosiasi Positif',
                'description' => 'Kemungkinan kedua item dibeli bersamaan lebih tinggi dibandingkan secara acak.',
                'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ];
        } elseif ($lift < 1) {
            return [
                'type' => 'negative',
                'label' => 'Asosiasi Negatif',
                'description' => 'Kemungkinan kedua item dibeli bersamaan lebih rendah (cenderung saling menggantikan).',
                'badge_class' => 'bg-rose-50 text-rose-700 border-rose-200',
            ];
        } else {
            return [
                'type' => 'independent',
                'label' => 'Independen',
                'description' => 'Item dibeli secara independen tanpa keterkaitan statistik tertentu.',
                'badge_class' => 'bg-slate-50 text-slate-700 border-slate-200',
            ];
        }
    }
}
