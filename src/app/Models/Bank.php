<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Get transactions for this bank
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'bank_name', 'name');
    }

    /**
     * Check if bank can be deleted (no transactions)
     */
    public function canBeDeleted(): bool
    {
        return $this->transactions()->count() === 0;
    }
}
