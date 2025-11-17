<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Exports\TransactionsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    /**
     * Export transactions to Excel
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'bank' => 'required|string',
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $transactions = $this->getFilteredTransactions(
            $request->bank,
            $request->year,
            $request->month
        );

        $filename = $this->generateFilename($request->bank, $request->year, $request->month, 'xlsx');

        return Excel::download(new TransactionsExport($transactions), $filename);
    }

    /**
     * Export transactions to CSV
     */
    public function exportCsv(Request $request)
    {
        $request->validate([
            'bank' => 'required|string',
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $transactions = $this->getFilteredTransactions(
            $request->bank,
            $request->year,
            $request->month
        );

        $filename = $this->generateFilename($request->bank, $request->year, $request->month, 'csv');

        return Excel::download(new TransactionsExport($transactions), $filename, \Maatwebsite\Excel\Excel::CSV);
    }

    /**
     * Export transactions to PDF
     */
    public function exportPdf(Request $request)
    {
        $request->validate([
            'bank' => 'required|string',
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $transactions = $this->getFilteredTransactions(
            $request->bank,
            $request->year,
            $request->month
        );

        $monthName = date('F', mktime(0, 0, 0, $request->month, 1));
        $title = "Bank Transactions - {$request->bank} {$monthName} {$request->year}";
        
        $pdf = Pdf::loadView('exports.transactions-pdf', [
            'transactions' => $transactions,
            'title' => $title,
            'bank' => $request->bank,
            'month' => $monthName,
            'year' => $request->year,
        ]);

        $pdf->setPaper('a4', 'landscape');

        $filename = $this->generateFilename($request->bank, $request->year, $request->month, 'pdf');

        return $pdf->download($filename);
    }

    /**
     * Get filtered transactions
     */
    private function getFilteredTransactions($bank, $year, $month)
    {
        return Transaction::with('category')
            ->where('bank_name', $bank)
            ->where('year', $year)
            ->where('month', $month)
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Generate filename for export
     */
    private function generateFilename($bank, $year, $month, $extension)
    {
        $bankSlug = strtolower(str_replace(' ', '_', $bank));
        return "transactions_{$bankSlug}_{$year}_{$month}.{$extension}";
    }
}
