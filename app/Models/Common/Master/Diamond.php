<?php

namespace App\Models\Common\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diamond extends Model
{
    use HasFactory;

    protected $primaryKey = 'diamond_amount_id';

    protected $table = 'diamond_amounts';

    protected $fillable = ['diamond_amount_id', 'amount', 'active', 'created_by', 'updated_by', 'created_at', 'updated_at'];
}
