<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rule extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'description_match',
        'category_id',
        'transaction_type',
    ];

    /**
     * Get the category that owns the rule.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    
    /**
     * Apply this rule to matching transactions.
     * 
     * @param string $bank
     * @param int $year
     * @param int $month
     * @param bool $overwrite Whether to overwrite existing classifications
     * @return int Number of transactions updated
     */
    public function applyToTransactions(string $bank, int $year, int $month, bool $overwrite = false): int
    {
        $query = Transaction::where('bank_name', $bank)
            ->where('year', $year)
            ->where('month', $month)
            ->where('description', 'LIKE', '%' . $this->description_match . '%');
        
        // Filter by transaction type
        if ($this->transaction_type === 'withdraw') {
            $query->whereNotNull('withdraw')->where('withdraw', '>', 0);
        } elseif ($this->transaction_type === 'deposit') {
            $query->whereNotNull('deposit')->where('deposit', '>', 0);
        }
        
        // If not overwriting, only update transactions without a category
        if (!$overwrite) {
            $query->whereNull('category_id');
        }
        
        return $query->update(['category_id' => $this->category_id]);
    }
}
