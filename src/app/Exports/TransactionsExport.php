<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TransactionsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $transactions;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    public function collection()
    {
        return $this->transactions->map(function ($transaction) {
            return [
                'date' => $transaction->date->format('M d, Y'),
                'description' => $transaction->description,
                'category' => $transaction->category ? $transaction->category->name : '-',
                'notes' => $transaction->notes ?? '-',
                'withdraw' => $transaction->withdraw ? '$' . number_format($transaction->withdraw, 2) : '-',
                'deposit' => $transaction->deposit ? '$' . number_format($transaction->deposit, 2) : '-',
                'balance' => '$' . number_format($transaction->balance, 2),
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
            'Balance'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E9ECEF']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ],
            'A:G' => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
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
