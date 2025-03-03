<?php

namespace App\Models\Report;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionHistory extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $table = 'transaction_histories';

    protected $fillable = [
        'id',
        'user_id',
        'type',
        'refference',
        'amount',
        'before_balance',
        'after_balance',
        'refference_table',
        'refference_id',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];
}
