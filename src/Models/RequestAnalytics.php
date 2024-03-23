<?php

namespace MeShaon\RequestAnalytics\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestAnalytics extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;
    public const CREATED_AT = null;

    protected $guarded = ['id', 'created_at', 'updated_at'];

}
