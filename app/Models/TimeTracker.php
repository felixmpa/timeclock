<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeTracker extends Model
{
    use HasFactory;

    protected $table =  "user_time_trackers";

    protected $fillable = ['user_id', 'date', 'note', 'tracked_at', 'is_check_in'];

}
