<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'user_id', 'project_id', 'midtrans_snap_token', 'gross_amount', 'status', 'subscription_id', 'expired_at', 'is_active', 'type'];
    protected $appends = ['hashed_project_id', 'is_expired'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getHashedProjectIdAttribute($value)
    {
        return Hashids::encode($this->project_id);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function getIsExpiredAttribute()
    {
        return Carbon::parse($this->expired_at)->startOfDay()->lt(Carbon::now()->startOfDay());
    }

    public function projectTemporary()
    {
        return $this->hasOne(ProjectTemporary::class);
    }
}
