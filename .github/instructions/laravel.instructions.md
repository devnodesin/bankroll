# Laravel Development Instructions for Bankroll

## Laravel Version
- Use latest stable Laravel version (10.x or 11.x)
- Follow Laravel's official documentation and best practices

## Project Structure

### Directory Organization
```
src/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   ├── HomeController.php
│   │   │   ├── TransactionController.php
│   │   │   ├── CategoryController.php
│   │   │   └── ImportController.php
│   │   └── Middleware/
│   ├── Models/
│   │   ├── Transaction.php
│   │   └── Category.php
│   └── Services/           # Business logic services
├── config/
│   └── app.php            # App name and version here
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── auth/
│       │   └── login.blade.php
│       └── home.blade.php
└── routes/
    └── web.php
```

## Coding Standards

### Controllers
- Keep controllers thin, move business logic to services
- Use resource controllers for CRUD operations
- Return views for page requests, JSON for AJAX
- Validate all inputs using Form Requests or inline validation

**Example:**
```php
class TransactionController extends Controller
{
    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'notes' => 'nullable|string|max:500',
        ]);
        
        // Only update classification fields
        $transaction->update($validated);
        
        return response()->json(['success' => true]);
    }
}
```

### Models
- Use Eloquent relationships
- Define fillable or guarded properties
- Add accessors/mutators for computed attributes
- Use casts for data type conversion

**Critical for Bankroll:**
```php
class Transaction extends Model
{
    // ONLY allow classification fields to be fillable
    protected $fillable = ['category_id', 'notes'];
    
    // Protect original bank data
    protected $guarded = ['date', 'description', 'withdraw', 'deposit', 'balance'];
    
    protected $casts = [
        'date' => 'date',
        'withdraw' => 'decimal:2',
        'deposit' => 'decimal:2',
        'balance' => 'decimal:2',
    ];
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
```

### Migrations
- Use descriptive names with timestamps
- Add indexes for commonly queried fields
- Include foreign key constraints
- Make migrations reversible (implement down method)

**Example:**
```php
Schema::create('transactions', function (Blueprint $table) {
    $table->id();
    $table->string('bank_name')->index();
    $table->date('date')->index();
    $table->text('description');
    $table->decimal('withdraw', 10, 2)->nullable();
    $table->decimal('deposit', 10, 2)->nullable();
    $table->decimal('balance', 10, 2);
    $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
    $table->integer('year')->index();
    $table->integer('month')->index();
    $table->timestamps();
    
    $table->index(['bank_name', 'year', 'month']);
});
```

### Routes
- Use named routes for easier reference
- Group related routes
- Apply middleware appropriately
- Use resource routes where applicable

**Example:**
```php
Route::middleware(['auth'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::post('/transactions/import', [ImportController::class, 'import'])->name('transactions.import');
    Route::patch('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::resource('categories', CategoryController::class)->except(['show']);
});
```

## Data Integrity Rules

### Transaction Data Protection
1. **Never allow direct mass assignment of bank data fields**
2. **Set bank data fields only during import**
3. **Make bank data read-only in forms and updates**

```php
// GOOD: Only classification fields
$transaction->update([
    'category_id' => $categoryId,
    'notes' => $notes,
]);

// BAD: Never allow this
$transaction->update([
    'description' => $newDescription,  // NO!
    'amount' => $newAmount,            // NO!
]);
```

## Validation

### Form Validation
- Always validate on backend, even if frontend validates
- Use Laravel's validation rules
- Return clear error messages

```php
$request->validate([
    'file' => 'required|mimes:xlsx,xls,csv|max:5120',
    'bank_name' => 'required|string|max:100',
]);
```

### Import Validation
- Validate file structure before processing
- Check required columns exactly match
- Validate each row's data types
- Use database transactions for atomicity

```php
DB::beginTransaction();
try {
    // Import logic here
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    return back()->withErrors(['error' => 'Import failed: ' . $e->getMessage()]);
}
```

## Authentication

### Simple Auth Setup
- Use Laravel's built-in auth scaffolding
- Single user authentication
- Protect all routes except login

```php
// In controller
public function login(Request $request)
{
    $credentials = $request->validate([
        'username' => 'required',
        'password' => 'required',
    ]);
    
    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('/');
    }
    
    return back()->withErrors([
        'username' => 'Invalid credentials.',
    ]);
}
```

## Performance Considerations

### Query Optimization
- Use eager loading to avoid N+1 queries
- Add indexes on filtered columns
- Use pagination for large datasets
- Select only needed columns

```php
// GOOD: Eager load relationships
$transactions = Transaction::with('category')
    ->where('bank_name', $bank)
    ->where('year', $year)
    ->where('month', $month)
    ->orderBy('date', 'desc')
    ->get();

// GOOD: Pagination for large datasets
$transactions = Transaction::where(...)
    ->paginate(50);
```

### File Processing
- Use Laravel Excel for imports/exports
- Consider queuing for large files
- Stream large exports

```php
// For large exports
return Excel::download(new TransactionsExport($transactions), 'transactions.xlsx');
```

## Error Handling

### User-Friendly Errors
- Catch exceptions and return meaningful messages
- Log errors for debugging
- Don't expose sensitive information

```php
try {
    // Operation
} catch (\Exception $e) {
    Log::error('Import failed', ['error' => $e->getMessage()]);
    return back()->withErrors(['error' => 'Import failed. Please check your file format.']);
}
```

## Testing Checklist
- [ ] All routes are protected with auth middleware
- [ ] Original transaction data cannot be modified
- [ ] File imports validate structure correctly
- [ ] Database transactions prevent partial imports
- [ ] AJAX endpoints return proper JSON responses
- [ ] Validation works on all forms
- [ ] N+1 queries are avoided
- [ ] Large datasets are paginated

## Common Packages

### Required Packages
```bash
composer require maatwebsite/laravel-excel    # Excel/CSV import/export
composer require barryvdh/laravel-dompdf      # PDF generation
```

## Configuration

### App Configuration
In `config/app.php`:
```php
'name' => env('APP_NAME', 'Bankroll'),
'version' => '1.0.0',  // Add this for footer display
```

### Environment Variables
```env
APP_NAME=Bankroll
DB_CONNECTION=mysql
DB_DATABASE=bankroll
```

## Remember
1. Protect original transaction data at all costs
2. Validate everything on the backend
3. Use Eloquent relationships properly
4. Follow RESTful conventions
5. Keep controllers thin
6. Use Laravel's built-in features
7. Write migrations that can be reversed
8. Always use database transactions for critical operations
