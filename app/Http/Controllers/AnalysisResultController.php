<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\Itemset;
use App\Models\AssociationRule;
use Illuminate\Http\Request;

class AnalysisResultController extends Controller
{
    public function show(Request $request, Analysis $analysis)
    {
        $tab = $request->input('tab', 'overview');

        // Available itemset sizes for filtering
        $availableSizes = $analysis->itemsets()->distinct()->pluck('itemset_size')->sort()->values();

        // 1. Frequent Itemsets Query
        $itemsetSize = $request->input('size');
        $itemSearch = $request->input('item_search');

        $itemsetsQuery = $analysis->itemsets()
            ->with('items')
            ->when($itemsetSize, fn($q) => $q->where('itemset_size', $itemsetSize))
            ->when($itemSearch, function ($q, $search) {
                $q->whereHas('items', fn($iq) => $iq->where('name', 'like', "%{$search}%"));
            })
            ->orderBy('itemset_size')
            ->orderByDesc('support');

        $itemsets = $itemsetsQuery->paginate(15, ['*'], 'itemset_page')->withQueryString();

        // 2. Association Rules Query
        $sortBy = $request->input('sort_by', 'confidence');
        $sortDir = $request->input('sort_dir', 'desc');
        $ruleSearch = $request->input('rule_search');
        $filterMinConf = $request->input('filter_min_conf');
        $filterMinSupp = $request->input('filter_min_supp');
        $filterMinLift = $request->input('filter_min_lift');

        $validSorts = ['confidence', 'support', 'lift'];
        if (!in_array($sortBy, $validSorts)) {
            $sortBy = 'confidence';
        }
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $rulesQuery = $analysis->associationRules()
            ->with(['antecedentItemset.items', 'consequentItemset.items'])
            ->when($ruleSearch, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->whereHas('antecedentItemset.items', fn($iq) => $iq->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('consequentItemset.items', fn($iq) => $iq->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filterMinConf, fn($q, $val) => $q->where('confidence', '>=', (float) $val / 100))
            ->when($filterMinSupp, fn($q, $val) => $q->where('support', '>=', (float) $val / 100))
            ->when($filterMinLift, fn($q, $val) => $q->where('lift', '>=', (float) $val))
            ->orderBy($sortBy, $sortDir);

        $rules = $rulesQuery->paginate(15, ['*'], 'rule_page')->withQueryString();

        // 3. Iterations
        $iterations = $analysis->iterations()->get();

        return view('analyses.show', compact(
            'analysis',
            'tab',
            'itemsets',
            'availableSizes',
            'itemsetSize',
            'itemSearch',
            'rules',
            'sortBy',
            'sortDir',
            'ruleSearch',
            'filterMinConf',
            'filterMinSupp',
            'filterMinLift',
            'iterations'
        ));
    }
}
