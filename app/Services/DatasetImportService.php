<?php

namespace App\Services;

use App\Models\Dataset;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class DatasetImportService
{
    public function __construct(
        protected DatasetValidationService $validationService
    ) {}

    /**
     * Parse and preview uploaded file (first N rows).
     *
     * @param string $filePath Absolute file path
     * @param string $extension
     * @param int $maxPreviewRows
     * @return array{headers: array<string>, rows: array<array>, total_rows: int}
     */
    public function previewFile(string $filePath, string $extension, int $maxPreviewRows = 10): array
    {
        $rawRows = $this->readRawRows($filePath, $extension);

        if (empty($rawRows)) {
            return [
                'headers' => [],
                'rows' => [],
                'total_rows' => 0,
            ];
        }

        $headers = array_shift($rawRows);
        $headers = $this->cleanHeaders($headers);

        $totalRows = count($rawRows);
        $previewRows = array_slice($rawRows, 0, $maxPreviewRows);

        $formattedPreview = [];
        foreach ($previewRows as $row) {
            $rowAssoc = [];
            foreach ($headers as $idx => $h) {
                $val = $row[$idx] ?? '';
                $rowAssoc[$h] = is_string($val) ? trim($val) : (string) $val;
            }
            $formattedPreview[] = $rowAssoc;
        }

        return [
            'headers' => $headers,
            'rows' => $formattedPreview,
            'total_rows' => $totalRows,
        ];
    }

    /**
     * Clean header column names: trim, strip control chars, remove trailing empties.
     *
     * @param array $headers
     * @return array<string>
     */
    protected function cleanHeaders(array $headers): array
    {
        // Find last non-empty header index
        $lastIdx = -1;
        foreach ($headers as $idx => $h) {
            if (trim((string) $h) !== '') {
                $lastIdx = $idx;
            }
        }

        if ($lastIdx >= 0) {
            $headers = array_slice($headers, 0, $lastIdx + 1);
        }

        $cleaned = [];
        foreach ($headers as $idx => $h) {
            $cleanName = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', (string) $h));
            if ($cleanName === '') {
                $cleanName = 'Kolom_' . ($idx + 1);
            }
            $cleaned[] = $cleanName;
        }

        return $cleaned;
    }

    /**
     * Guess default column mapping.
     */
    public function guessMapping(array $headers): array
    {
        $mapping = [
            'transaction_id' => '',
            'item' => '',
            'quantity' => '',
            'transaction_date' => '',
        ];

        foreach ($headers as $h) {
            $lower = strtolower(trim($h));
            if (in_array($lower, ['transaction_id', 'transaction_code', 'kode_transaksi', 'id_transaksi', 'invoice_no', 'invoice', 'order_id', 'no_transaksi'])) {
                $mapping['transaction_id'] = $h;
            } elseif (in_array($lower, ['item', 'item_name', 'product', 'product_name', 'nama_produk', 'nama_barang', 'barang', 'produk'])) {
                $mapping['item'] = $h;
            } elseif (in_array($lower, ['quantity', 'qty', 'jumlah', 'kuantitas', 'banyak'])) {
                $mapping['quantity'] = $h;
            } elseif (in_array($lower, ['transaction_date', 'date', 'tanggal', 'tgl', 'created_at'])) {
                $mapping['transaction_date'] = $h;
            }
        }

        return $mapping;
    }

    /**
     * Import dataset with column mapping.
     *
     * @param string $filePath
     * @param string $extension
     * @param string $name Dataset name
     * @param string|null $description
     * @param string $originalFilename
     * @param array{transaction_id: string, item: string, quantity?: string, transaction_date?: string} $columnMapping
     * @param int|null $userId
     * @return Dataset
     * @throws \Exception
     */
    public function import(
        string $filePath,
        string $extension,
        string $name,
        ?string $description,
        string $originalFilename,
        array $columnMapping,
        ?int $userId = null
    ): Dataset {
        $rawRows = $this->readRawRows($filePath, $extension);

        if (empty($rawRows)) {
            throw new \InvalidArgumentException('File kosong atau tidak memiliki data.');
        }

        $headers = array_shift($rawRows);
        $headers = $this->cleanHeaders($headers);
        $headerMap = array_flip($headers);

        $txCol = $columnMapping['transaction_id'] ?? null;
        $itemCol = $columnMapping['item'] ?? null;
        $qtyCol = $columnMapping['quantity'] ?? null;
        $dateCol = $columnMapping['transaction_date'] ?? null;

        if (!$txCol || !isset($headerMap[$txCol])) {
            throw new \InvalidArgumentException("Kolom ID Transaksi '{$txCol}' tidak ditemukan pada file.");
        }
        if (!$itemCol || !isset($headerMap[$itemCol])) {
            throw new \InvalidArgumentException("Kolom Produk '{$itemCol}' tidak ditemukan pada file.");
        }

        $txIdx = $headerMap[$txCol];
        $itemIdx = $headerMap[$itemCol];
        $qtyIdx = ($qtyCol && isset($headerMap[$qtyCol])) ? $headerMap[$qtyCol] : null;
        $dateIdx = ($dateCol && isset($headerMap[$dateCol])) ? $headerMap[$dateCol] : null;

        $errors = [];
        $parsedTransactions = []; // tx_code => ['date' => date, 'items' => [normalized_item => ['name' => clean_name, 'qty' => sum]]]
        $uniqueNormalizedItems = []; // normalized => clean_name
        $rowNumber = 1; // 1 was header, row 2 in spreadsheet is index 0

        foreach ($rawRows as $index => $row) {
            $rowNumber++;
            // Skip completely empty rows
            if (empty(array_filter($row, fn($v) => trim((string) $v) !== ''))) {
                continue;
            }

            $txCode = trim((string) ($row[$txIdx] ?? ''));
            $rawItem = trim((string) ($row[$itemIdx] ?? ''));
            $qtyVal = $qtyIdx !== null ? ($row[$qtyIdx] ?? 1) : 1;
            $dateVal = $dateIdx !== null ? trim((string) ($row[$dateIdx] ?? '')) : null;

            $rowErrors = $this->validationService->validateRow([
                'transaction_id' => $txCode,
                'item' => $rawItem,
                'quantity' => $qtyVal,
                'transaction_date' => $dateVal,
            ], $rowNumber);

            if (!empty($rowErrors)) {
                $errors = array_merge($errors, $rowErrors);
                // Stop collecting if too many errors to prevent memory exhaustion
                if (count($errors) > 50) {
                    $errors[] = "Terdapat terlalu banyak baris tidak valid (melebihi 50 error). Import dibatalkan.";
                    break;
                }
                continue;
            }

            // Clean item display name and normalized key
            // Clean consecutive whitespaces:
            $cleanItemName = preg_replace('/\s+/', ' ', $rawItem);
            $normalizedItemName = mb_strtolower($cleanItemName, 'UTF-8');
            $numericQty = ($qtyVal !== null && is_numeric($qtyVal)) ? (float) $qtyVal : 1.0;

            $parsedDate = null;
            if ($dateVal !== null && $dateVal !== '') {
                $timestamp = strtotime($dateVal);
                if ($timestamp !== false) {
                    $parsedDate = date('Y-m-d H:i:s', $timestamp);
                }
            }

            if (!isset($parsedTransactions[$txCode])) {
                $parsedTransactions[$txCode] = [
                    'date' => $parsedDate,
                    'items' => [],
                ];
            }

            // Aggregate multiple identical items in the same transaction
            if (!isset($parsedTransactions[$txCode]['items'][$normalizedItemName])) {
                $parsedTransactions[$txCode]['items'][$normalizedItemName] = [
                    'name' => $cleanItemName,
                    'quantity' => $numericQty,
                ];
            } else {
                $parsedTransactions[$txCode]['items'][$normalizedItemName]['quantity'] += $numericQty;
            }

            if (!isset($uniqueNormalizedItems[$normalizedItemName])) {
                $uniqueNormalizedItems[$normalizedItemName] = $cleanItemName;
            }
        }

        if (!empty($errors)) {
            throw new \DomainException(implode("\n", $errors));
        }

        if (empty($parsedTransactions)) {
            throw new \InvalidArgumentException('Tidak ada data transaksi valid yang ditemukan dalam file.');
        }

        // Database atomic insertion
        return DB::transaction(function () use (
            $name,
            $description,
            $originalFilename,
            $extension,
            $userId,
            $rawRows,
            $parsedTransactions,
            $uniqueNormalizedItems
        ) {
            $dataset = Dataset::create([
                'user_id' => $userId,
                'name' => $name,
                'description' => $description,
                'original_filename' => $originalFilename,
                'file_type' => $extension,
                'total_rows' => count($rawRows),
                'total_transactions' => count($parsedTransactions),
                'total_items' => count($uniqueNormalizedItems),
                'status' => 'imported',
                'imported_at' => now(),
            ]);

            // Bulk create Items
            $itemModels = [];
            foreach ($uniqueNormalizedItems as $norm => $cleanName) {
                $item = Item::create([
                    'dataset_id' => $dataset->id,
                    'name' => $cleanName,
                    'normalized_name' => $norm,
                ]);
                $itemModels[$norm] = $item->id;
            }

            // Insert Transactions and TransactionItems
            foreach ($parsedTransactions as $txCode => $txData) {
                $tx = Transaction::create([
                    'dataset_id' => $dataset->id,
                    'transaction_code' => $txCode,
                    'transaction_at' => $txData['date'],
                ]);

                $txItemRows = [];
                foreach ($txData['items'] as $norm => $itemDetail) {
                    $itemId = $itemModels[$norm];
                    $txItemRows[] = [
                        'transaction_id' => $tx->id,
                        'item_id' => $itemId,
                        'quantity' => $itemDetail['quantity'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($txItemRows)) {
                    TransactionItem::insert($txItemRows);
                }
            }

            return $dataset->fresh();
        });
    }

    /**
     * Read raw rows from CSV, TXT, XLSX, or XLS robustly.
     * Handles binary spreadsheets, UTF-16, UTF-8 BOM, Windows-1252, semicolons, tabs, etc.
     *
     * @param string $filePath
     * @param string $extension
     * @return array<array>
     */
    protected function readRawRows(string $filePath, string $extension): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return [];
        }

        // 1. Check Magic Bytes for Binary Spreadsheets (XLSX, XLS, ODS)
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return [];
        }
        $magic = fread($handle, 8);
        fclose($handle);

        $isZipSpreadsheet = str_starts_with($magic, "PK\x03\x04");
        $isLegacyExcel = str_starts_with($magic, "\xD0\xCF\x11\xE0");
        $extLower = strtolower($extension);

        if ($isZipSpreadsheet || $isLegacyExcel || in_array($extLower, ['xlsx', 'xls', 'ods'])) {
            try {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($filePath);
                $worksheet = $spreadsheet->getActiveSheet();
                $rawRows = $worksheet->toArray(null, true, true, false);

                $cleanRows = [];
                foreach ($rawRows as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $trimmed = array_map(fn($c) => trim((string) $c), $row);
                    if (!empty(array_filter($trimmed, fn($c) => $c !== ''))) {
                        $cleanRows[] = $trimmed;
                    }
                }

                // If Excel has all comma/semicolon-separated data pasted in Column A, auto-split
                if (!empty($cleanRows) && count($cleanRows[0]) === 1) {
                    $cleanRows = $this->autoSplitSingleColumnRows($cleanRows);
                }

                return $cleanRows;
            } catch (\Throwable $e) {
                Log::warning('PhpSpreadsheet failed to read file, falling back to text CSV reader: ' . $e->getMessage());
            }
        }

        // 2. Text-based files (CSV, TSV, TXT, Unicode)
        $content = file_get_contents($filePath);
        if ($content === false || $content === '') {
            return [];
        }

        // Handle BOM & Encodings
        if (str_starts_with($content, "\xFF\xFE")) {
            // UTF-16LE BOM (often exported by Windows Excel)
            $content = mb_convert_encoding(substr($content, 2), 'UTF-8', 'UTF-16LE');
        } elseif (str_starts_with($content, "\xFE\xFF")) {
            // UTF-16BE BOM
            $content = mb_convert_encoding(substr($content, 2), 'UTF-8', 'UTF-16BE');
        } elseif (str_starts_with($content, "\xEF\xBB\xBF")) {
            // UTF-8 BOM
            $content = substr($content, 3);
        } elseif (!mb_check_encoding($content, 'UTF-8')) {
            // Non-UTF-8 fallback (Windows-1252 / ISO-8859-1)
            $enc = mb_detect_encoding($content, ['UTF-8', 'Windows-1252', 'ISO-8859-1', 'ASCII'], true) ?: 'Windows-1252';
            $content = mb_convert_encoding($content, 'UTF-8', $enc);
        }

        // Strip remaining null bytes (common in UTF-16 corruptions or binary remnants)
        $content = str_replace("\x00", '', $content);

        // Normalize line endings (\r\n and \r to \n)
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        // Detect delimiter from sample lines
        $lines = array_filter(explode("\n", $content), fn($l) => trim($l) !== '');
        if (empty($lines)) {
            return [];
        }

        $sample = implode("\n", array_slice($lines, 0, 5));
        $candidates = [
            ',' => substr_count($sample, ','),
            ';' => substr_count($sample, ';'),
            "\t" => substr_count($sample, "\t"),
            '|' => substr_count($sample, '|'),
        ];
        arsort($candidates);
        $delimiter = key($candidates);
        if ($candidates[$delimiter] === 0) {
            $delimiter = ',';
        }

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        $rows = [];
        while (($data = fgetcsv($stream, 0, $delimiter)) !== false) {
            if (!is_array($data)) {
                continue;
            }
            $trimmed = array_map(fn($c) => trim((string) $c), $data);
            if (!empty(array_filter($trimmed, fn($c) => $c !== ''))) {
                $rows[] = $trimmed;
            }
        }
        fclose($stream);

        // If CSV parsing somehow resulted in a single column that still contains delimiters
        if (!empty($rows) && count($rows[0]) === 1) {
            $rows = $this->autoSplitSingleColumnRows($rows);
        }

        return $rows;
    }

    /**
     * If all data was pasted/stored into Column A, detect delimiter and split into multiple columns.
     *
     * @param array<array<string>> $rows
     * @return array<array<string>>
     */
    protected function autoSplitSingleColumnRows(array $rows): array
    {
        if (empty($rows) || count($rows[0]) !== 1) {
            return $rows;
        }

        $sample = '';
        foreach (array_slice($rows, 0, 5) as $r) {
            $sample .= ($r[0] ?? '') . "\n";
        }

        $delims = [
            ',' => substr_count($sample, ','),
            ';' => substr_count($sample, ';'),
            "\t" => substr_count($sample, "\t"),
            '|' => substr_count($sample, '|'),
        ];
        arsort($delims);
        $bestDelim = key($delims);

        if ($delims[$bestDelim] === 0) {
            return $rows;
        }

        $splitRows = [];
        foreach ($rows as $r) {
            $cell = $r[0] ?? '';
            $parts = str_getcsv($cell, $bestDelim);
            $trimmed = array_map(fn($c) => trim((string) $c), $parts);
            if (!empty(array_filter($trimmed, fn($c) => $c !== ''))) {
                $splitRows[] = $trimmed;
            }
        }

        return !empty($splitRows) ? $splitRows : $rows;
    }

    /**
     * Generate template CSV content.
     */
    public function getTemplateCsvContent(): string
    {
        $csv = "transaction_id,item,quantity,transaction_date\n";
        $csv .= "TR001,Roti,2,2026-01-01\n";
        $csv .= "TR001,Susu,1,2026-01-01\n";
        $csv .= "TR002,Roti,1,2026-01-01\n";
        $csv .= "TR002,Mentega,2,2026-01-01\n";
        $csv .= "TR003,Roti,1,2026-01-02\n";
        $csv .= "TR003,Susu,1,2026-01-02\n";
        $csv .= "TR003,Mentega,1,2026-01-02\n";
        $csv .= "TR004,Susu,2,2026-01-02\n";
        $csv .= "TR004,Mentega,1,2026-01-02\n";
        $csv .= "TR005,Roti,1,2026-01-03\n";
        $csv .= "TR005,Susu,1,2026-01-03\n";

        return $csv;
    }
}
