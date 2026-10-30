<?php

namespace App\Services;

class DatasetValidationService
{
    /**
     * Validate an extracted row from the dataset file.
     *
     * @param array $row Associative array with mapped keys
     * @param int $rowNumber 1-based index in the file
     * @return array<string> List of error messages for this row, empty if valid
     */
    public function validateRow(array $row, int $rowNumber): array
    {
        $errors = [];

        $txId = trim((string) ($row['transaction_id'] ?? ''));
        $item = trim((string) ($row['item'] ?? ''));
        $qty = $row['quantity'] ?? null;
        $date = trim((string) ($row['transaction_date'] ?? ''));

        if ($txId === '') {
            $errors[] = "Baris #{$rowNumber}: ID Transaksi (transaction_id) tidak boleh kosong.";
        }

        if ($item === '') {
            $errors[] = "Baris #{$rowNumber}: Nama Produk (item) tidak boleh kosong.";
        }

        if ($qty !== null && $qty !== '') {
            if (!is_numeric($qty) || (float) $qty <= 0) {
                $errors[] = "Baris #{$rowNumber}: Kuantitas (quantity) harus berupa angka positif.";
            }
        }

        if ($date !== '') {
            // Check if valid date format
            $parsedDate = strtotime($date);
            if ($parsedDate === false) {
                $errors[] = "Baris #{$rowNumber}: Format tanggal '{$date}' tidak valid.";
            }
        }

        return $errors;
    }
}
