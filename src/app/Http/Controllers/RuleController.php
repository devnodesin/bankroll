<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Rule;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RuleController extends Controller
{
    /**
     * Display a listing of rules.
     */
    public function index(): View
    {
        $rules = Rule::with('category')->orderBy('created_at', 'desc')->get();
        $categories = Category::orderBy('name')->get();
        
        return view('rules.index', compact('rules', 'categories'));
    }

    /**
     * Store a newly created rule.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'description_match' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'transaction_type' => 'required|in:withdraw,deposit,both',
        ], [
            'description_match.required' => 'Description match is required.',
            'description_match.max' => 'Description match cannot exceed 255 characters.',
            'category_id.required' => 'Category is required.',
            'category_id.exists' => 'Selected category does not exist.',
            'transaction_type.required' => 'Transaction type is required.',
            'transaction_type.in' => 'Transaction type must be withdraw, deposit, or both.',
        ]);

        $rule = Rule::create($validated);
        $rule->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Rule created successfully.',
            'rule' => $rule,
        ], 201);
    }

    /**
     * Update the specified rule.
     */
    public function update(Request $request, Rule $rule): JsonResponse
    {
        $validated = $request->validate([
            'description_match' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'transaction_type' => 'required|in:withdraw,deposit,both',
        ], [
            'description_match.required' => 'Description match is required.',
            'description_match.max' => 'Description match cannot exceed 255 characters.',
            'category_id.required' => 'Category is required.',
            'category_id.exists' => 'Selected category does not exist.',
            'transaction_type.required' => 'Transaction type is required.',
            'transaction_type.in' => 'Transaction type must be withdraw, deposit, or both.',
        ]);

        $rule->update($validated);
        $rule->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Rule updated successfully.',
            'rule' => $rule,
        ]);
    }

    /**
     * Remove the specified rule.
     */
    public function destroy(Rule $rule): JsonResponse
    {
        $rule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rule deleted successfully.',
        ]);
    }

    /**
     * Apply rules to transactions for a specific bank, year, and month.
     */
    public function applyRules(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bank' => 'required|string',
            'year' => 'required|integer|min:1900|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'overwrite' => 'required|boolean',
        ], [
            'bank.required' => 'Bank is required.',
            'year.required' => 'Year is required.',
            'year.integer' => 'Year must be a valid number.',
            'year.min' => 'Year must be 1900 or later.',
            'year.max' => 'Year must be 2100 or earlier.',
            'month.required' => 'Month is required.',
            'month.integer' => 'Month must be a valid number.',
            'month.min' => 'Month must be between 1 and 12.',
            'month.max' => 'Month must be between 1 and 12.',
            'overwrite.required' => 'Overwrite option is required.',
            'overwrite.boolean' => 'Overwrite must be true or false.',
        ]);

        try {
            $totalUpdated = 0;
            $rules = Rule::with('category')->get();

            if ($rules->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No rules found. Please create rules first.',
                ], 422);
            }

            DB::beginTransaction();

            foreach ($rules as $rule) {
                $updated = $rule->applyToTransactions(
                    $validated['bank'],
                    $validated['year'],
                    $validated['month'],
                    $validated['overwrite']
                );
                $totalUpdated += $updated;
            }

            DB::commit();

            $message = $totalUpdated > 0
                ? "Rules applied successfully. {$totalUpdated} transaction(s) updated."
                : "No transactions matched the rules.";

            return response()->json([
                'success' => true,
                'message' => $message,
                'count' => $totalUpdated,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply rules: ' . $e->getMessage(),
            ], 500);
        }
    }
}
