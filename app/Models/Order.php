<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'user_id', 'project_id', 'midtrans_snap_token', 'gross_amount', 'status'];
    protected $appends = ['hashed_project_id'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getHashedProjectIdAttribute($value)
    {
        return Hashids::encode($this->project_id);
    }
}
