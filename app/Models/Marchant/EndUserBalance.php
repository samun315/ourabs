<?php

namespace App\Models\Marchant;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EndUserBalance extends Model
{
    use HasFactory;

    protected $primaryKey = 'balance_transfer_id';

    protected $table = 'end_user_balance_transfers';

    protected $fillable = [
        'balance_transfer_id',
        'amount',
        'request_by',
        'imo_id',
        'phone_number',
        'request_to',
        'order_id',
        'message',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'request_by', 'id');
    }
}
