<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    /**
     * Display the home page.
     */
    public function index(): View
    {
        $banks = Transaction::select('bank_name')
            ->distinct()
            ->orderBy('bank_name')
            ->pluck('bank_name');
        
        $years = Transaction::select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');
        
        $categories = Category::orderBy('name')->get();
        $currencySymbol = config('app.currency_symbol', '$');
        
        return view('home', compact('banks', 'years', 'categories', 'currencySymbol'));
    }

    /**
     * Get transactions filtered by bank, year, and month.
     */
    public function getTransactions(Request $request): JsonResponse
    {
        $request->validate([
            'bank' => 'required|string',
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $transactions = Transaction::with('category')
            ->where('bank_name', $request->bank)
            ->where('year', $request->year)
            ->where('month', $request->month)
            ->orderByDesc('date')
            ->get();

        return response()->json([
            'success' => true,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Update transaction classification.
     */
    public function updateTransaction(Request $request, Transaction $transaction): JsonResponse
    {
        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $transaction->update([
            'category_id' => $request->category_id,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction updated successfully.',
        ]);
    }
}
