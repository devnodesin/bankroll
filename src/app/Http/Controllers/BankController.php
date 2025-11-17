<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankController extends Controller
{
    /**
     * Get all banks
     */
    public function index(): JsonResponse
    {
        $banks = Bank::orderBy('name')->get();
        return response()->json([
            'success' => true,
            'banks' => $banks,
        ]);
    }

    /**
     * Store a new bank
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:banks,name',
        ]);

        $bank = Bank::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bank added successfully.',
            'bank' => $bank,
        ]);
    }

    /**
     * Delete a bank (only if no transactions exist)
     */
    public function destroy(Bank $bank): JsonResponse
    {
        $transactionCount = Transaction::where('bank_name', $bank->name)->count();

        if ($transactionCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete bank '{$bank->name}'. It has {$transactionCount} transaction(s).",
            ], 400);
        }

        $bank->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bank deleted successfully.',
        ]);
    }
}
