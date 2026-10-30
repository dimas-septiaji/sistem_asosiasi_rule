<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $datasetId = $request->input('dataset_id');
        $search = $request->input('search');

        $datasets = Dataset::orderBy('name')->get();

        $transactions = Transaction::with(['dataset', 'items' => function ($q) {
                $q->withPivot('quantity');
            }])
            ->when($datasetId, fn($q) => $q->where('dataset_id', $datasetId))
            ->when($search, fn($q) => $q->where('transaction_code', 'like', "%{$search}%"))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('transactions.index', compact('transactions', 'datasets', 'datasetId', 'search'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['dataset', 'items' => function ($q) {
            $q->withPivot('quantity');
        }]);

        return view('transactions.show', compact('transaction'));
    }
}
