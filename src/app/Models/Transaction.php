<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    /**
     * The attributes that are mass assignable.
     * ONLY classification fields are fillable - original bank data is protected.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'notes',
    ];

    /**
     * The attributes that should be protected from mass assignment.
     * Original bank transaction data must never be modified.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'bank_name',
        'date',
        'description',
        'withdraw',
        'deposit',
        'balance',
        'reference_number',
        'year',
        'month',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'withdraw' => 'decimal:2',
            'deposit' => 'decimal:2',
            'balance' => 'decimal:2',
        ];
    }

    /**
     * Get the category that owns the transaction.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
