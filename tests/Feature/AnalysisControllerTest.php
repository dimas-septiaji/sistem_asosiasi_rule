<?php

namespace Tests\Feature;

use App\Models\Analysis;
use App\Models\Dataset;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalysisControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function createSampleDataset(): Dataset
    {
        $dataset = Dataset::create([
            'name' => 'Sample Dataset for Analysis',
            'original_filename' => 'sample.csv',
            'file_type' => 'csv',
            'total_rows' => 11,
            'total_transactions' => 5,
            'total_items' => 3,
        ]);

        $roti = Item::create(['dataset_id' => $dataset->id, 'name' => 'Roti', 'normalized_name' => 'roti']);
        $susu = Item::create(['dataset_id' => $dataset->id, 'name' => 'Susu', 'normalized_name' => 'susu']);
        $mentega = Item::create(['dataset_id' => $dataset->id, 'name' => 'Mentega', 'normalized_name' => 'mentega']);

        $txData = [
            'T1' => [$roti, $susu],
            'T2' => [$roti, $mentega],
            'T3' => [$roti, $susu, $mentega],
            'T4' => [$susu, $mentega],
            'T5' => [$roti, $susu],
        ];

        foreach ($txData as $code => $items) {
            $tx = Transaction::create(['dataset_id' => $dataset->id, 'transaction_code' => $code]);
            foreach ($items as $item) {
                TransactionItem::create(['transaction_id' => $tx->id, 'item_id' => $item->id, 'quantity' => 1]);
            }
        }

        return $dataset;
    }

    public function test_analysis_create_form_renders(): void
    {
        $dataset = $this->createSampleDataset();
        $response = $this->get(route('analyses.create'));
        $response->assertStatus(200);
        $response->assertSee('Parameter Analisis Apriori');
    }

    public function test_can_run_analysis_via_http(): void
    {
        $dataset = $this->createSampleDataset();

        $response = $this->post(route('analyses.store'), [
            'name' => 'Uji HTTP Analisis',
            'dataset_id' => $dataset->id,
            'min_support' => 40, // 40%
            'min_confidence' => 60, // 60%
        ]);

        $analysis = Analysis::where('name', 'Uji HTTP Analisis')->first();
        $this->assertNotNull($analysis);
        $response->assertRedirect(route('analyses.show', $analysis));

        $this->assertEquals('completed', $analysis->status);
        $this->assertEquals(6, $analysis->total_frequent_itemsets);
        $this->assertEquals(4, $analysis->total_rules);
    }

    public function test_analysis_result_tabs_render(): void
    {
        $dataset = $this->createSampleDataset();
        $this->post(route('analyses.store'), [
            'name' => 'Tab View Test',
            'dataset_id' => $dataset->id,
            'min_support' => 40,
            'min_confidence' => 60,
        ]);

        $analysis = Analysis::where('name', 'Tab View Test')->first();

        // Overview tab
        $resOverview = $this->get(route('analyses.show', [$analysis, 'tab' => 'overview']));
        $resOverview->assertStatus(200);
        $resOverview->assertSee('Ringkasan (Overview)');

        // Itemsets tab
        $resItemsets = $this->get(route('analyses.show', [$analysis, 'tab' => 'itemsets']));
        $resItemsets->assertStatus(200);
        $resItemsets->assertSee('Frequent Itemset');

        // Rules tab
        $resRules = $this->get(route('analyses.show', [$analysis, 'tab' => 'rules']));
        $resRules->assertStatus(200);
        $resRules->assertSee('Association Rule');

        // Iterations tab
        $resIterations = $this->get(route('analyses.show', [$analysis, 'tab' => 'iterations']));
        $resIterations->assertStatus(200);
        $resIterations->assertSee('Detail Iterasi');
    }

    public function test_reports_and_export(): void
    {
        $dataset = $this->createSampleDataset();
        $this->post(route('analyses.store'), [
            'name' => 'Report Test Analysis',
            'dataset_id' => $dataset->id,
            'min_support' => 40,
            'min_confidence' => 60,
        ]);

        $analysis = Analysis::where('name', 'Report Test Analysis')->first();

        // Print view
        $resPrint = $this->get(route('analyses.print', $analysis));
        $resPrint->assertStatus(200);
        $resPrint->assertSee('Laporan Market Basket Analysis');

        // Excel Export
        $resExcel = $this->get(route('analyses.export.excel', $analysis));
        $resExcel->assertStatus(200);

        // PDF Export
        $resPdf = $this->get(route('analyses.export.pdf', $analysis));
        $resPdf->assertStatus(200);
    }
}
