<?php

namespace App\Services;

use App\Models\Analysis;
use App\Models\AprioriIteration;
use App\Models\AssociationRule;
use App\Models\Dataset;
use App\Models\Item;
use App\Models\Itemset;
use App\Models\ItemsetItem;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AprioriService
{
    public function __construct(
        protected FrequentItemsetService $frequentItemsetService,
        protected AssociationRuleService $associationRuleService
    ) {}

    /**
     * Run Apriori Analysis on a given dataset and save all results.
     *
     * @param Analysis $analysis
     * @return Analysis
     * @throws \Throwable
     */
    public function run(Analysis $analysis): Analysis
    {
        $startTime = microtime(true);

        $analysis->update([
            'status' => 'processing',
            'started_at' => now(),
            'error_message' => null,
        ]);

        try {
            DB::beginTransaction();

            $dataset = $analysis->dataset;

            // 1. Fetch transactions with their item IDs
            // Ensure each item only appears once per transaction for frequency counting
            $transactionsData = DB::table('transactions')
                ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
                ->where('transactions.dataset_id', $dataset->id)
                ->select('transactions.id as transaction_id', 'transaction_items.item_id')
                ->orderBy('transactions.id')
                ->get();

            $transactionsList = [];
            $transactionsWithKeys = [];

            foreach ($transactionsData as $row) {
                $txId = (int) $row->transaction_id;
                $itemId = (int) $row->item_id;

                if (!isset($transactionsWithKeys[$txId])) {
                    $transactionsWithKeys[$txId] = [];
                }
                $transactionsWithKeys[$txId][$itemId] = true;
            }

            foreach ($transactionsWithKeys as $txId => $itemsMap) {
                $items = array_keys($itemsMap);
                sort($items, SORT_NUMERIC);
                $transactionsList[] = $items;
            }

            $totalTransactions = count($transactionsList);

            if ($totalTransactions === 0) {
                throw new \RuntimeException('Dataset tidak memiliki transaksi yang valid untuk dianalisis.');
            }

            $minSupport = (float) $analysis->min_support;
            $minConfidence = (float) $analysis->min_confidence;
            $minLift = $analysis->min_lift !== null ? (float) $analysis->min_lift : null;

            // 2. Apriori Iterations
            $allFrequentItemsets = []; // key => data
            $iterationRecords = [];
            $k = 1;

            // k = 1: C1 and L1
            $iterStart = microtime(true);
            $l1Result = $this->frequentItemsetService->generateL1($transactionsList, $totalTransactions, $minSupport);
            $iterDuration = (int) round((microtime(true) - $iterStart) * 1000);

            $iterationRecords[] = [
                'analysis_id' => $analysis->id,
                'k' => 1,
                'candidate_count' => count($l1Result['candidates']),
                'frequent_count' => count($l1Result['frequent']),
                'duration_ms' => $iterDuration,
                'created_at' => now(),
            ];

            $currentFrequent = $l1Result['frequent'];
            foreach ($currentFrequent as $key => $val) {
                $allFrequentItemsets[$key] = $val;
            }

            // k >= 2: Loop until no frequent itemsets or candidates
            while (!empty($currentFrequent)) {
                $k++;
                $iterStart = microtime(true);

                // Candidate Generation (Join + Prune)
                $candidates = $this->frequentItemsetService->generateCandidates($currentFrequent, $k);

                if (empty($candidates)) {
                    // Stop condition
                    break;
                }

                // Support Counting & Filtering
                $stepResult = $this->frequentItemsetService->countSupportAndFilter(
                    $candidates,
                    $transactionsWithKeys,
                    $totalTransactions,
                    $minSupport
                );

                $iterDuration = (int) round((microtime(true) - $iterStart) * 1000);

                $iterationRecords[] = [
                    'analysis_id' => $analysis->id,
                    'k' => $k,
                    'candidate_count' => $stepResult['candidates_count'],
                    'frequent_count' => count($stepResult['frequent']),
                    'duration_ms' => $iterDuration,
                    'created_at' => now(),
                ];

                $currentFrequent = $stepResult['frequent'];
                if (!empty($currentFrequent)) {
                    foreach ($currentFrequent as $key => $val) {
                        $allFrequentItemsets[$key] = $val;
                    }
                }
            }

            // Save Iterations
            if (!empty($iterationRecords)) {
                AprioriIteration::insert($iterationRecords);
            }

            // 3. Save Itemsets into Database
            // Map itemset_key => saved itemset_id
            $savedItemsetIds = [];

            foreach ($allFrequentItemsets as $key => $data) {
                $itemsetSize = count($data['items']);
                $itemsetHash = hash('sha256', $key);

                $itemsetModel = Itemset::create([
                    'analysis_id' => $analysis->id,
                    'itemset_size' => $itemsetSize,
                    'itemset_hash' => $itemsetHash,
                    'support_count' => $data['count'],
                    'support' => $data['support'],
                ]);

                $savedItemsetIds[$key] = $itemsetModel->id;

                // Insert itemset_items relations
                $itemsetItemsRows = [];
                foreach ($data['items'] as $itemId) {
                    $itemsetItemsRows[] = [
                        'itemset_id' => $itemsetModel->id,
                        'item_id' => $itemId,
                    ];
                }

                if (!empty($itemsetItemsRows)) {
                    ItemsetItem::insert($itemsetItemsRows);
                }
            }

            // 4. Generate and Save Association Rules
            $rules = $this->associationRuleService->generateRules(
                $allFrequentItemsets,
                $minConfidence,
                $minLift
            );

            $ruleRecords = [];
            foreach ($rules as $rule) {
                $antId = $savedItemsetIds[$rule['antecedent_key']] ?? null;
                $conId = $savedItemsetIds[$rule['consequent_key']] ?? null;
                $unionId = $savedItemsetIds[$rule['union_key']] ?? null;

                if ($antId && $conId && $unionId) {
                    $ruleRecords[] = [
                        'analysis_id' => $analysis->id,
                        'antecedent_itemset_id' => $antId,
                        'consequent_itemset_id' => $conId,
                        'union_itemset_id' => $unionId,
                        'support_count' => $rule['support_count'],
                        'support' => $rule['support'],
                        'confidence' => $rule['confidence'],
                        'lift' => $rule['lift'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($ruleRecords)) {
                // Chunk insert if many rules
                foreach (array_chunk($ruleRecords, 100) as $chunk) {
                    AssociationRule::insert($chunk);
                }
            }

            $totalDurationMs = (int) round((microtime(true) - $startTime) * 1000);

            // 5. Update Analysis completion
            $analysis->update([
                'status' => 'completed',
                'total_transactions' => $totalTransactions,
                'total_frequent_itemsets' => count($allFrequentItemsets),
                'total_rules' => count($ruleRecords),
                'completed_at' => now(),
                'duration_ms' => $totalDurationMs,
            ]);

            DB::commit();

            return $analysis->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Apriori analysis failed: ' . $e->getMessage(), [
                'analysis_id' => $analysis->id,
                'trace' => $e->getTraceAsString(),
            ]);

            $totalDurationMs = (int) round((microtime(true) - $startTime) * 1000);

            $analysis->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
                'duration_ms' => $totalDurationMs,
            ]);

            throw $e;
        }
    }
}
