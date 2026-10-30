<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\Dataset;
use App\Models\Item;
use App\Models\Itemset;
use App\Models\Transaction;
use App\Models\AssociationRule;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Real database statistics
        $totalDatasets = Dataset::count();
        $totalTransactions = Transaction::count();
        $totalItems = Item::count();
        $totalAnalyses = Analysis::count();
        $totalFrequentItemsets = Itemset::count();
        $totalRules = AssociationRule::count();

        // Recent analyses
        $recentAnalyses = Analysis::with('dataset')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Recent datasets
        $recentDatasets = Dataset::withCount('transactions', 'items')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'totalDatasets',
            'totalTransactions',
            'totalItems',
            'totalAnalyses',
            'totalFrequentItemsets',
            'totalRules',
            'recentAnalyses',
            'recentDatasets'
        ));
    }
}
