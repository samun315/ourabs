<?php

namespace App\Models\Marchant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiDiamondTransaction extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $table = 'api_imo_diamond_transactions';

    protected $fillable = [
        'api_key',
        'product',
        'imo_id',
        'amount',
        'user',
        'pass',
        'uuid',
        'order_id',
        'result',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];
}
