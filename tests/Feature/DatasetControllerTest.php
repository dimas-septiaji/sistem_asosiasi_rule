<?php

namespace Tests\Feature;

use App\Models\Dataset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatasetControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dataset_index_renders(): void
    {
        Dataset::create([
            'name' => 'Toko Retail A',
            'original_filename' => 'toko_a.csv',
            'file_type' => 'csv',
            'total_rows' => 50,
            'total_transactions' => 10,
            'total_items' => 5,
        ]);

        $response = $this->get(route('datasets.index'));
        $response->assertStatus(200);
        $response->assertSee('Toko Retail A');
    }

    public function test_template_download(): void
    {
        $response = $this->get(route('datasets.template'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_dataset_show_renders(): void
    {
        $dataset = Dataset::create([
            'name' => 'Dataset Detail Test',
            'original_filename' => 'detail.csv',
            'file_type' => 'csv',
            'total_rows' => 10,
            'total_transactions' => 2,
            'total_items' => 2,
        ]);

        $response = $this->get(route('datasets.show', $dataset));
        $response->assertStatus(200);
        $response->assertSee('Dataset Detail Test');
    }

    public function test_dataset_delete(): void
    {
        $dataset = Dataset::create([
            'name' => 'To Be Deleted',
            'original_filename' => 'del.csv',
            'file_type' => 'csv',
        ]);

        $response = $this->delete(route('datasets.destroy', $dataset));
        $response->assertRedirect(route('datasets.index'));
        $this->assertDatabaseMissing('datasets', ['id' => $dataset->id]);
    }
}
