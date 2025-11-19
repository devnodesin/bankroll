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
        ], [
            'name.required' => 'Bank name is required.',
            'name.max' => 'Bank name cannot exceed 100 characters.',
            'name.unique' => 'A bank with this name already exists.',
        ]);

        $bank = Bank::create([
            'name' => trim($request->name),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Bank '{$bank->name}' has been added successfully.",
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
                'message' => "Cannot delete '{$bank->name}' because it has {$transactionCount} transaction(s). Remove all transactions first or keep this bank for historical records.",
            ], 400);
        }

        $bankName = $bank->name;
        $bank->delete();

        return response()->json([
            'success' => true,
            'message' => "Bank '{$bankName}' has been deleted successfully.",
        ]);
    }
}
