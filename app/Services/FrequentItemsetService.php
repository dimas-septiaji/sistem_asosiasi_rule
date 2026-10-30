<?php

namespace App\Services;

class FrequentItemsetService
{
    /**
     * Compute C1 (1-itemset candidates) and filter to L1 based on min_support.
     *
     * @param array<int, array<int>> $transactions Array of transactions, each transaction is a list of unique item IDs
     * @param int $totalTransactions
     * @param float $minSupport Ratio 0.0 - 1.0
     * @return array{candidates: array<string, int>, frequent: array<string, array{count: int, support: float, items: array<int>}>}
     */
    public function generateL1(array $transactions, int $totalTransactions, float $minSupport): array
    {
        $candidateCounts = [];

        foreach ($transactions as $tx) {
            foreach ($tx as $itemId) {
                $candidateCounts[$itemId] = ($candidateCounts[$itemId] ?? 0) + 1;
            }
        }

        // Sort by key (item ID) for consistency
        ksort($candidateCounts);

        $candidates = [];
        $frequent = [];

        foreach ($candidateCounts as $itemId => $count) {
            $key = (string) $itemId;
            $candidates[$key] = $count;
            $support = $totalTransactions > 0 ? $count / $totalTransactions : 0.0;

            if ($support >= $minSupport) {
                $frequent[$key] = [
                    'count' => $count,
                    'support' => $support,
                    'items' => [$itemId],
                ];
            }
        }

        return [
            'candidates' => $candidates,
            'frequent' => $frequent,
        ];
    }

    /**
     * Generate C_k candidates using Join and Prune steps from L_{k-1}.
     *
     * @param array<string, array{items: array<int>}> $prevFrequent L_{k-1}
     * @param int $k Current itemset size (>= 2)
     * @return array<string, array<int>> Map of candidate_key => sorted item IDs
     */
    public function generateCandidates(array $prevFrequent, int $k): array
    {
        $frequentItemsets = array_values(array_map(fn($item) => $item['items'], $prevFrequent));
        $frequentKeys = array_fill_keys(array_keys($prevFrequent), true);
        $numFrequent = count($frequentItemsets);
        $candidates = [];

        // Join Step: L_{k-1} JOIN L_{k-1}
        for ($i = 0; $i < $numFrequent; $i++) {
            for ($j = $i + 1; $j < $numFrequent; $j++) {
                $itemset1 = $frequentItemsets[$i];
                $itemset2 = $frequentItemsets[$j];

                // Verify first k-2 elements are identical
                $canJoin = true;
                for ($m = 0; $m < $k - 2; $m++) {
                    if ($itemset1[$m] !== $itemset2[$m]) {
                        $canJoin = false;
                        break;
                    }
                }

                if (!$canJoin) {
                    continue;
                }

                // Join the two itemsets: combine and sort
                $candidate = array_values(array_unique(array_merge($itemset1, $itemset2)));
                sort($candidate, SORT_NUMERIC);

                if (count($candidate) !== $k) {
                    continue;
                }

                // Prune Step: check if all (k-1) subsets of candidate are in prevFrequent (L_{k-1})
                if ($this->hasAllFrequentSubsets($candidate, $k, $frequentKeys)) {
                    $key = implode(',', $candidate);
                    $candidates[$key] = $candidate;
                }
            }
        }

        return $candidates;
    }

    /**
     * Check Apriori pruning property: all subsets of size k-1 must be frequent.
     */
    protected function hasAllFrequentSubsets(array $candidate, int $k, array $frequentKeys): bool
    {
        // For k = 2, each 1-item is trivially in L1 because it was joined from L1
        if ($k === 2) {
            return true;
        }

        // Generate all subsets of size k - 1 by dropping one item at a time
        $size = count($candidate);
        for ($i = 0; $i < $size; $i++) {
            $subset = $candidate;
            unset($subset[$i]);
            $subsetKey = implode(',', array_values($subset));

            if (!isset($frequentKeys[$subsetKey])) {
                return false; // Pruned!
            }
        }

        return true;
    }

    /**
     * Count support for candidates across all transactions and filter frequent itemsets.
     *
     * @param array<string, array<int>> $candidates
     * @param array<int, array<int>> $transactions List of transactions where each transaction has item IDs as keys for O(1) lookup
     * @param int $totalTransactions
     * @param float $minSupport
     * @return array{candidates_count: int, frequent: array<string, array{count: int, support: float, items: array<int>}>}
     */
    public function countSupportAndFilter(
        array $candidates,
        array $transactionsWithKeys,
        int $totalTransactions,
        float $minSupport
    ): array {
        $candidateCount = count($candidates);
        $frequent = [];

        foreach ($candidates as $key => $items) {
            $count = 0;

            foreach ($transactionsWithKeys as $txMap) {
                $containsAll = true;
                foreach ($items as $itemId) {
                    if (!isset($txMap[$itemId])) {
                        $containsAll = false;
                        break;
                    }
                }

                if ($containsAll) {
                    $count++;
                }
            }

            $support = $totalTransactions > 0 ? $count / $totalTransactions : 0.0;

            if ($support >= $minSupport) {
                $frequent[$key] = [
                    'count' => $count,
                    'support' => $support,
                    'items' => $items,
                ];
            }
        }

        return [
            'candidates_count' => $candidateCount,
            'frequent' => $frequent,
        ];
    }
}
