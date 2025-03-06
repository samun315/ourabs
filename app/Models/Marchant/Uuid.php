<?php

namespace App\Models\Marchant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Uuid extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $table = 'uuids';

    protected $fillable = [
        'id',
        'uuid',
        'created_at',
        'updated_at'
    ];
}
