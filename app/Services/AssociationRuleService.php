<?php

namespace App\Services;

class AssociationRuleService
{
    /**
     * Generate association rules from frequent itemsets.
     *
     * @param array<string, array{count: int, support: float, items: array<int>}> $allFrequentItemsets
     *        Map of itemset_key (e.g. "1,2,3") to frequent itemset details
     * @param float $minConfidence Ratio 0.0 - 1.0
     * @param float|null $minLift Optional minimum lift ratio
     * @return array<array{
     *     antecedent_key: string,
     *     consequent_key: string,
     *     union_key: string,
     *     antecedent_items: array<int>,
     *     consequent_items: array<int>,
     *     support_count: int,
     *     support: float,
     *     confidence: float,
     *     lift: float
     * }>
     */
    public function generateRules(
        array $allFrequentItemsets,
        float $minConfidence,
        ?float $minLift = null
    ): array {
        $rules = [];

        // Pre-index frequent itemsets by their key ("1,2,3") for O(1) support lookup
        // Note: each key is comma-separated sorted item IDs
        foreach ($allFrequentItemsets as $unionKey => $itemsetData) {
            $items = $itemsetData['items'];
            $n = count($items);

            // Only itemsets of size >= 2 can produce rules
            if ($n < 2) {
                continue;
            }

            $unionSupport = $itemsetData['support'];
            $unionCount = $itemsetData['count'];

            // Generate all non-empty proper subsets A of items
            $subsets = $this->generateNonEmptyProperSubsets($items);

            foreach ($subsets as $antItems) {
                $antKey = implode(',', $antItems);

                // Get consequent items: items in $items not in $antItems
                $antMap = array_fill_keys($antItems, true);
                $conItems = [];
                foreach ($items as $item) {
                    if (!isset($antMap[$item])) {
                        $conItems[] = $item;
                    }
                }
                sort($conItems, SORT_NUMERIC);
                $conKey = implode(',', $conItems);

                // Look up support of antecedent A and consequent B
                // Because of Apriori property, all subsets of a frequent itemset are frequent!
                if (!isset($allFrequentItemsets[$antKey]) || !isset($allFrequentItemsets[$conKey])) {
                    continue;
                }

                $antSupport = $allFrequentItemsets[$antKey]['support'];
                $conSupport = $allFrequentItemsets[$conKey]['support'];

                if ($antSupport <= 0.0 || $conSupport <= 0.0) {
                    continue;
                }

                // Confidence(A -> B) = Support(A U B) / Support(A)
                $confidence = $unionSupport / $antSupport;

                // Precision check: rule accepted if confidence >= minConfidence
                // Account for potential tiny floating point inaccuracy with epsilon
                if ($confidence + 1e-9 < $minConfidence) {
                    continue;
                }

                // Lift(A -> B) = Confidence(A -> B) / Support(B)
                $lift = $confidence / $conSupport;

                // Optional minimum lift filtering
                if ($minLift !== null && $lift + 1e-9 < $minLift) {
                    continue;
                }

                $rules[] = [
                    'antecedent_key' => $antKey,
                    'consequent_key' => $conKey,
                    'union_key' => $unionKey,
                    'antecedent_items' => $antItems,
                    'consequent_items' => $conItems,
                    'support_count' => $unionCount,
                    'support' => $unionSupport,
                    'confidence' => $confidence,
                    'lift' => $lift,
                ];
            }
        }

        return $rules;
    }

    /**
     * Generate all non-empty proper subsets of an array of items.
     *
     * @param array<int> $items Sorted array of item IDs
     * @return array<array<int>>
     */
    public function generateNonEmptyProperSubsets(array $items): array
    {
        $n = count($items);
        $total = 1 << $n; // 2^n
        $subsets = [];

        // Exclude empty set (mask = 0) and full set (mask = 2^n - 1)
        for ($mask = 1; $mask < $total - 1; $mask++) {
            $subset = [];
            for ($i = 0; $i < $n; $i++) {
                if ($mask & (1 << $i)) {
                    $subset[] = $items[$i];
                }
            }
            sort($subset, SORT_NUMERIC);
            $subsets[] = $subset;
        }

        return $subsets;
    }
}
