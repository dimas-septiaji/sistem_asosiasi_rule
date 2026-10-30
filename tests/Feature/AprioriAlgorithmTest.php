<?php

namespace Tests\Feature;

use App\Models\Analysis;
use App\Models\Dataset;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\AprioriService;
use App\Services\FrequentItemsetService;
use App\Services\AssociationRuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AprioriAlgorithmTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the exact benchmark dataset from prompt specifications:
     * 5 transactions:
     * T1: Roti, Susu
     * T2: Roti, Mentega
     * T3: Roti, Susu, Mentega
     * T4: Susu, Mentega
     * T5: Roti, Susu
     *
     * Min Support = 40% (0.40)
     * Min Confidence = 60% (0.60)
     */
    public function test_apriori_benchmark_dataset(): void
    {
        $dataset = Dataset::create([
            'name' => 'Benchmark Dataset',
            'original_filename' => 'benchmark.csv',
            'file_type' => 'csv',
            'status' => 'imported',
            'total_rows' => 11,
            'total_transactions' => 5,
            'total_items' => 3,
        ]);

        $itemRoti = Item::create(['dataset_id' => $dataset->id, 'name' => 'Roti', 'normalized_name' => 'roti']);
        $itemSusu = Item::create(['dataset_id' => $dataset->id, 'name' => 'Susu', 'normalized_name' => 'susu']);
        $itemMentega = Item::create(['dataset_id' => $dataset->id, 'name' => 'Mentega', 'normalized_name' => 'mentega']);

        $transactionsData = [
            'T1' => [$itemRoti, $itemSusu],
            'T2' => [$itemRoti, $itemMentega],
            'T3' => [$itemRoti, $itemSusu, $itemMentega],
            'T4' => [$itemSusu, $itemMentega],
            'T5' => [$itemRoti, $itemSusu],
        ];

        foreach ($transactionsData as $code => $items) {
            $tx = Transaction::create([
                'dataset_id' => $dataset->id,
                'transaction_code' => $code,
            ]);

            foreach ($items as $item) {
                TransactionItem::create([
                    'transaction_id' => $tx->id,
                    'item_id' => $item->id,
                    'quantity' => 1,
                ]);
            }
        }

        $analysis = Analysis::create([
            'dataset_id' => $dataset->id,
            'name' => 'Benchmark Apriori Analysis',
            'algorithm' => 'apriori',
            'min_support' => 0.40, // 40%
            'min_confidence' => 0.60, // 60%
            'status' => 'pending',
        ]);

        /** @var AprioriService $service */
        $service = app(AprioriService::class);
        $result = $service->run($analysis);

        // 1. Verify Analysis completion
        $this->assertEquals('completed', $result->status);
        $this->assertEquals(5, $result->total_transactions);

        // 2. Verify Frequent Itemsets:
        // L1: 3 items (Roti: 4/5=80%, Susu: 4/5=80%, Mentega: 3/5=60%)
        // L2: 3 pairs (Roti, Susu: 3/5=60%; Roti, Mentega: 2/5=40%; Susu, Mentega: 2/5=40%)
        // L3: 0 (Roti, Susu, Mentega has support 1/5 = 20% < 40% -> pruned)
        // Total frequent itemsets = 3 + 3 = 6
        $this->assertEquals(6, $result->total_frequent_itemsets);

        $itemsets = $result->itemsets()->with('items')->get();

        // Check L1
        $l1 = $itemsets->where('itemset_size', 1);
        $this->assertCount(3, $l1);

        $rotiItemset = $l1->first(fn($is) => $is->items->first()->name === 'Roti');
        $this->assertNotNull($rotiItemset);
        $this->assertEquals(4, $rotiItemset->support_count);
        $this->assertEqualsWithDelta(0.80, $rotiItemset->support, 0.0001);

        $susuItemset = $l1->first(fn($is) => $is->items->first()->name === 'Susu');
        $this->assertNotNull($susuItemset);
        $this->assertEquals(4, $susuItemset->support_count);
        $this->assertEqualsWithDelta(0.80, $susuItemset->support, 0.0001);

        $mentegaItemset = $l1->first(fn($is) => $is->items->first()->name === 'Mentega');
        $this->assertNotNull($mentegaItemset);
        $this->assertEquals(3, $mentegaItemset->support_count);
        $this->assertEqualsWithDelta(0.60, $mentegaItemset->support, 0.0001);

        // Check L2
        $l2 = $itemsets->where('itemset_size', 2);
        $this->assertCount(3, $l2);

        // Check 3-itemset does NOT exist
        $l3 = $itemsets->where('itemset_size', 3);
        $this->assertCount(0, $l3);

        // 3. Verify Association Rules
        $rules = $result->associationRules()->with(['antecedentItemset.items', 'consequentItemset.items'])->get();

        // Total accepted rules should be 4
        $this->assertCount(4, $rules);

        // Verify Roti -> Susu rule
        $rotiToSusu = $rules->first(function ($r) {
            return $r->antecedentItemset->items->first()->name === 'Roti'
                && $r->consequentItemset->items->first()->name === 'Susu';
        });
        $this->assertNotNull($rotiToSusu);
        $this->assertEqualsWithDelta(0.60, $rotiToSusu->support, 0.0001);
        $this->assertEqualsWithDelta(0.75, $rotiToSusu->confidence, 0.0001);
        $this->assertEqualsWithDelta(0.9375, $rotiToSusu->lift, 0.0001);

        // Verify Susu -> Roti rule
        $susuToRoti = $rules->first(function ($r) {
            return $r->antecedentItemset->items->first()->name === 'Susu'
                && $r->consequentItemset->items->first()->name === 'Roti';
        });
        $this->assertNotNull($susuToRoti);
        $this->assertEqualsWithDelta(0.60, $susuToRoti->support, 0.0001);
        $this->assertEqualsWithDelta(0.75, $susuToRoti->confidence, 0.0001);
        $this->assertEqualsWithDelta(0.9375, $susuToRoti->lift, 0.0001);

        // Verify that Roti -> Mentega (Conf 50%) is NOT present
        $rotiToMentega = $rules->first(function ($r) {
            return $r->antecedentItemset->items->first()->name === 'Roti'
                && $r->consequentItemset->items->first()->name === 'Mentega';
        });
        $this->assertNull($rotiToMentega, 'Roti -> Mentega must NOT be included because Confidence 50% < 60%');

        // Check rule explanation string
        $this->assertStringContainsString('Sebanyak 75.00% transaksi yang membeli Roti juga membeli Susu.', $rotiToSusu->explanation);
    }

    /**
     * Test when no items meet minimum support.
     */
    public function test_apriori_when_no_item_meets_minimum_support(): void
    {
        $dataset = Dataset::create([
            'name' => 'Sparse Dataset',
            'original_filename' => 'sparse.csv',
            'file_type' => 'csv',
            'status' => 'imported',
            'total_rows' => 3,
            'total_transactions' => 3,
            'total_items' => 3,
        ]);

        $itemA = Item::create(['dataset_id' => $dataset->id, 'name' => 'A', 'normalized_name' => 'a']);
        $itemB = Item::create(['dataset_id' => $dataset->id, 'name' => 'B', 'normalized_name' => 'b']);
        $itemC = Item::create(['dataset_id' => $dataset->id, 'name' => 'C', 'normalized_name' => 'c']);

        // Each item appears only once in 3 transactions (support = 33.3%)
        foreach (['T1' => $itemA, 'T2' => $itemB, 'T3' => $itemC] as $code => $item) {
            $tx = Transaction::create(['dataset_id' => $dataset->id, 'transaction_code' => $code]);
            TransactionItem::create(['transaction_id' => $tx->id, 'item_id' => $item->id, 'quantity' => 1]);
        }

        // Set min support = 50%
        $analysis = Analysis::create([
            'dataset_id' => $dataset->id,
            'name' => 'Sparse Analysis',
            'algorithm' => 'apriori',
            'min_support' => 0.50,
            'min_confidence' => 0.50,
            'status' => 'pending',
        ]);

        /** @var AprioriService $service */
        $service = app(AprioriService::class);
        $result = $service->run($analysis);

        $this->assertEquals('completed', $result->status);
        $this->assertEquals(0, $result->total_frequent_itemsets);
        $this->assertEquals(0, $result->total_rules);
        $this->assertEquals(1, $result->iterations()->count());
    }

    /**
     * Test association rule generation with 3-item frequent itemsets:
     * Antecedent can have 1 or 2 items, consequent can have 1 or 2 items.
     */
    public function test_association_rule_service_partitions_multi_item_sets(): void
    {
        /** @var AssociationRuleService $service */
        $service = app(AssociationRuleService::class);

        // Frequent itemsets map:
        // Items: 1=A, 2=B, 3=C
        // Assume all subsets have support = 0.5, union has support = 0.4
        $frequent = [
            '1' => ['items' => [1], 'support' => 0.5, 'count' => 5],
            '2' => ['items' => [2], 'support' => 0.5, 'count' => 5],
            '3' => ['items' => [3], 'support' => 0.5, 'count' => 5],
            '1,2' => ['items' => [1, 2], 'support' => 0.45, 'count' => 4],
            '1,3' => ['items' => [1, 3], 'support' => 0.45, 'count' => 4],
            '2,3' => ['items' => [2, 3], 'support' => 0.45, 'count' => 4],
            '1,2,3' => ['items' => [1, 2, 3], 'support' => 0.4, 'count' => 4],
        ];

        // For itemset {1, 2, 3}, all non-empty proper subsets:
        // 1 -> 2,3
        // 2 -> 1,3
        // 3 -> 1,2
        // 1,2 -> 3
        // 1,3 -> 2
        // 2,3 -> 1
        // (total 6 rules from {1,2,3} plus rules from 2-itemsets)
        $rules = $service->generateRules($frequent, 0.50);

        $threeItemUnionRules = array_filter($rules, fn($r) => $r['union_key'] === '1,2,3');
        $this->assertCount(6, $threeItemUnionRules);

        // Check rule {1, 2} -> {3}
        $r12to3 = array_filter($threeItemUnionRules, fn($r) => $r['antecedent_key'] === '1,2' && $r['consequent_key'] === '3');
        $this->assertNotEmpty($r12to3);
        $first = reset($r12to3);
        // Confidence = Support(1,2,3) / Support(1,2) = 0.4 / 0.45 = 0.8888
        $this->assertEqualsWithDelta(0.4 / 0.45, $first['confidence'], 0.001);
    }
}
