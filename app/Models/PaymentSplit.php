<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSplit extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentSplitFactory> */
    use HasFactory;
    
    protected $fillable = [
        'expense_id',
        'user_id',
        'amount'
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
