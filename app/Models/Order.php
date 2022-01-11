<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'user_id', 'project_id', 'midtrans_snap_token', 'gross_amount', 'status'];
}
