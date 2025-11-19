<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display all categories
     */
    public function index()
    {
        $systemCategories = Category::where('is_custom', false)->orderBy('name')->get();
        $customCategories = Category::where('is_custom', true)->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'system' => $systemCategories,
            'custom' => $customCategories
        ]);
    }

    /**
     * Store a new custom category
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('categories', 'name')->whereNull('deleted_at')
            ]
        ], [
            'name.required' => 'Category name is required.',
            'name.max' => 'Category name cannot exceed 50 characters.',
            'name.unique' => 'A category with this name already exists.'
        ]);

        $category = Category::create([
            'name' => trim($request->name),
            'is_custom' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => "Category '{$category->name}' has been added successfully.",
            'category' => $category
        ], 201);
    }

    /**
     * Delete a custom category
     */
    public function destroy(Category $category)
    {
        // Prevent deletion of system categories
        if (!$category->is_custom) {
            return response()->json([
                'success' => false,
                'message' => "System category '{$category->name}' cannot be deleted. Only custom categories can be removed."
            ], 403);
        }

        // Check if category is in use
        $usageCount = $category->transactions()->count();

        if ($usageCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete '{$category->name}' because it is assigned to {$usageCount} transaction(s). Remove the category from all transactions first.",
                'usage_count' => $usageCount
            ], 400);
        }

        $categoryName = $category->name;
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => "Category '{$categoryName}' has been deleted successfully."
        ]);
    }

    /**
     * Get all categories for dropdown
     */
    public function getAll()
    {
        $categories = Category::orderBy('is_custom')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'categories' => $categories
        ]);
    }
}
