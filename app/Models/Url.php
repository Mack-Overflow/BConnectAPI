<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    use HasFactory;

    protected $table = 'urls';

    protected $foreignKey = 'subscriberId';
    // protected $foreignKey = 'businessId';

    protected $fillable = [
        'businessId',
        'subscriberId',
        'fullUrl',
        'shortUrl',
    ];
}
