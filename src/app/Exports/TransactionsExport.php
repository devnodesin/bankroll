<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExport implements FromCollection, WithColumnWidths, WithHeadings, WithStyles
{
    protected $transactions;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    public function collection()
    {
        $currencySymbol = config('app.currency_symbol', '$');

        return $this->transactions->map(function ($transaction) use ($currencySymbol) {
            return [
                'date' => $transaction->date->format('d/m/Y'),
                'description' => $transaction->description,
                'category' => $transaction->category ? $transaction->category->name : '-',
                'notes' => $transaction->notes ?? '-',
                'withdraw' => $transaction->withdraw ? $currencySymbol.number_format($transaction->withdraw, 2) : '-',
                'deposit' => $transaction->deposit ? $currencySymbol.number_format($transaction->deposit, 2) : '-',
                'balance' => $currencySymbol.number_format($transaction->balance, 2),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Date',
            'Description',
            'Category',
            'Notes',
            'Withdraw',
            'Deposit',
            'Balance',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Calculate the actual data range to avoid iterating over millions of empty rows
        $rowCount = $this->transactions->count() + 1; // +1 for header row

        // Apply styles only to the actual data range
        $dataRange = 'A1:G'.$rowCount;

        // Style the header row
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E9ECEF'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Apply borders to all data cells (if there's data)
        if ($rowCount > 1) {
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 40,
            'C' => 20,
            'D' => 30,
            'E' => 12,
            'F' => 12,
            'G' => 12,
        ];
    }
}
