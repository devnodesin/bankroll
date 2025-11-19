<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the home page.
     */
    public function index(): View
    {
        // Get banks from banks table, fallback to transaction data if none exist
        $banks = \App\Models\Bank::orderBy('name')->pluck('name');
        
        if ($banks->isEmpty()) {
            $banks = Transaction::select('bank_name')
                ->distinct()
                ->orderBy('bank_name')
                ->pluck('bank_name');
        }

        $years = Transaction::select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $categories = Category::orderBy('name')->get();
        $currencySymbol = config('app.currency_symbol', '$');

        return view('home', compact('banks', 'years', 'categories', 'currencySymbol'));
    }

    /**
     * Get available months for a given bank and year
     */
    public function getAvailableMonths(Request $request): JsonResponse
    {
        $request->validate([
            'bank' => 'required|string',
            'year' => 'required|integer',
        ]);

        $months = Transaction::where('bank_name', $request->bank)
            ->where('year', $request->year)
            ->select('month')
            ->distinct()
            ->orderBy('month')
            ->pluck('month');

        return response()->json([
            'success' => true,
            'months' => $months,
        ]);
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
            ->orderBy('date')
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

        // Only update fields that are present in the request
        $updateData = [];
        
        if ($request->has('category_id')) {
            $updateData['category_id'] = $request->category_id;
        }
        
        if ($request->has('notes')) {
            $updateData['notes'] = $request->notes;
        }

        $transaction->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Transaction updated successfully.',
        ]);
    }
}
