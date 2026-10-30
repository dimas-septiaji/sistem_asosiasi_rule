<?php

namespace Tests\Feature;

use App\Models\Dataset;
use App\Services\DatasetImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class DatasetImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_import_csv_with_duplicate_items_summed(): void
    {
        /** @var DatasetImportService $service */
        $service = app(DatasetImportService::class);

        $csvContent = "transaction_id,item,quantity,transaction_date\n"
            . "T1,Roti,1,2026-01-01\n"
            . "T1,Roti,2,2026-01-01\n" // Duplicate in same transaction -> quantity should sum to 3
            . "T1,Susu,1,2026-01-01\n"
            . "T2,Roti,1,2026-01-01\n"
            . "T2,Mentega,1,2026-01-01\n";

        $tmpFile = tempnam(sys_get_temp_dir(), 'csv_test_');
        file_put_contents($tmpFile, $csvContent);

        $dataset = $service->import(
            $tmpFile,
            'csv',
            'Test Dataset',
            'Description',
            'test.csv',
            [
                'transaction_id' => 'transaction_id',
                'item' => 'item',
                'quantity' => 'quantity',
                'transaction_date' => 'transaction_date',
            ]
        );

        unlink($tmpFile);

        $this->assertInstanceOf(Dataset::class, $dataset);
        $this->assertEquals(2, $dataset->total_transactions);
        $this->assertEquals(3, $dataset->total_items);

        // Verify T1 has Roti with quantity = 3
        $t1 = $dataset->transactions()->where('transaction_code', 'T1')->first();
        $this->assertNotNull($t1);
        $rotiItem = $t1->items()->where('name', 'Roti')->first();
        $this->assertNotNull($rotiItem);
        $this->assertEquals(3.00, (float) $rotiItem->pivot->quantity);
    }

    public function test_import_fails_on_empty_transaction_id(): void
    {
        /** @var DatasetImportService $service */
        $service = app(DatasetImportService::class);

        $csvContent = "transaction_id,item,quantity\n"
            . ",Roti,1\n";

        $tmpFile = tempnam(sys_get_temp_dir(), 'csv_fail_');
        file_put_contents($tmpFile, $csvContent);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Baris #2: ID Transaksi (transaction_id) tidak boleh kosong.');

        try {
            $service->import(
                $tmpFile,
                'csv',
                'Failed Dataset',
                null,
                'fail.csv',
                ['transaction_id' => 'transaction_id', 'item' => 'item', 'quantity' => 'quantity']
            );
        } finally {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }

    /**
     * Test Windows Excel UTF-16LE export with BOM (\xFF\xFE).
     * Prevents "simbol simbol gajelas" (strange binary symbols).
     */
    public function test_can_import_utf16le_encoded_csv(): void
    {
        /** @var DatasetImportService $service */
        $service = app(DatasetImportService::class);

        $rawText = "transaction_id,item,quantity\n"
            . "INV001,Teh Celup,2\n"
            . "INV001,Gula Pasir,1\n"
            . "INV002,Teh Celup,1\n";

        // Convert to UTF-16LE and prepend BOM \xFF\xFE
        $utf16le = "\xFF\xFE" . mb_convert_encoding($rawText, 'UTF-16LE', 'UTF-8');
        $tmpFile = tempnam(sys_get_temp_dir(), 'utf16_');
        file_put_contents($tmpFile, $utf16le);

        $preview = $service->previewFile($tmpFile, 'csv');
        $this->assertEquals(['transaction_id', 'item', 'quantity'], $preview['headers']);
        $this->assertEquals('INV001', $preview['rows'][0]['transaction_id']);
        $this->assertEquals('Teh Celup', $preview['rows'][0]['item']);

        $dataset = $service->import(
            $tmpFile,
            'csv',
            'UTF-16 Dataset',
            null,
            'utf16.csv',
            ['transaction_id' => 'transaction_id', 'item' => 'item', 'quantity' => 'quantity']
        );

        unlink($tmpFile);

        $this->assertEquals(2, $dataset->total_transactions);
        $this->assertEquals(2, $dataset->total_items);
    }

    /**
     * Test Semicolon-delimited CSV (standard Excel export in Indonesia/Europe).
     */
    public function test_can_import_semicolon_delimited_csv(): void
    {
        /** @var DatasetImportService $service */
        $service = app(DatasetImportService::class);

        $content = "transaction_id;item;quantity;transaction_date\n"
            . "TRX-101;Kopi Bubuk;1;2026-02-01\n"
            . "TRX-101;Susu Kental;2;2026-02-01\n"
            . "TRX-102;Kopi Bubuk;3;2026-02-02\n";

        $tmpFile = tempnam(sys_get_temp_dir(), 'semi_');
        file_put_contents($tmpFile, $content);

        $preview = $service->previewFile($tmpFile, 'csv');
        $this->assertEquals(['transaction_id', 'item', 'quantity', 'transaction_date'], $preview['headers']);

        $dataset = $service->import(
            $tmpFile,
            'csv',
            'Semicolon Dataset',
            null,
            'semicolon.csv',
            ['transaction_id' => 'transaction_id', 'item' => 'item', 'quantity' => 'quantity']
        );

        unlink($tmpFile);

        $this->assertEquals(2, $dataset->total_transactions);
        $this->assertEquals(2, $dataset->total_items);
    }

    /**
     * Test Real Excel (.xlsx) file upload.
     */
    public function test_can_import_real_xlsx_file(): void
    {
        /** @var DatasetImportService $service */
        $service = app(DatasetImportService::class);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'transaction_id');
        $sheet->setCellValue('B1', 'item');
        $sheet->setCellValue('C1', 'quantity');

        $sheet->setCellValue('A2', 'XLS01');
        $sheet->setCellValue('B2', 'Beras');
        $sheet->setCellValue('C2', 5);

        $sheet->setCellValue('A3', 'XLS01');
        $sheet->setCellValue('B3', 'Minyak Goreng');
        $sheet->setCellValue('C3', 2);

        $sheet->setCellValue('A4', 'XLS02');
        $sheet->setCellValue('B4', 'Beras');
        $sheet->setCellValue('C4', 1);

        $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpFile);

        $preview = $service->previewFile($tmpFile, 'xlsx');
        $this->assertEquals(['transaction_id', 'item', 'quantity'], $preview['headers']);
        $this->assertEquals('XLS01', $preview['rows'][0]['transaction_id']);
        $this->assertEquals('Beras', $preview['rows'][0]['item']);

        $dataset = $service->import(
            $tmpFile,
            'xlsx',
            'Excel XLSX Dataset',
            null,
            'sample.xlsx',
            ['transaction_id' => 'transaction_id', 'item' => 'item', 'quantity' => 'quantity']
        );

        unlink($tmpFile);

        $this->assertEquals(2, $dataset->total_transactions);
        $this->assertEquals(2, $dataset->total_items);
    }
}
