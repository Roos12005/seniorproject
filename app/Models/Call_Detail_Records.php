<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Call_Detail_Records extends Model
{
    protected $table = 'call_detail_records';
    public $timestamps = false;
    protected $fillable = [
        'mobile_no','start_date_time','call_day','b_no','duration','rncode'
    ];
}
