<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\TimeTracker;
use Carbon\Carbon;
use Carbon\CarbonInterval;


class TimeTrackerController extends Controller
{
    public $currentDate;
    public $sec, $min, $hour;

    public function __construct()
    {
        $this->currentDate = Carbon::now();
        $this->sec = 0;
        $this->min = 0;
        $this->hour= 0;
    }

    public function index(Request $request)
    {

        $trackers     = $this->getTimeTrackerOfDateByUser($request);
        
        $intervalList = [];
        
        if($trackers->count() == 0)
            User::where('id', $request->user()->id)->update(['is_time_tracker_started' => 0]);

        if($trackers->count() > 0)
        {
            $initialCheckIn = Carbon::parse($trackers->firstOrFail()->tracked_at);
            
            //If pause is the last item then diff with this tracked_at 
            if($trackers[$trackers->count()-1]->is_check_in == 0){
                $clock = Carbon::parse($trackers[$trackers->count()-1]->tracked_at)->diff($initialCheckIn);
            }else{
                $clock = Carbon::now()->diff($initialCheckIn);
            }

            list($this->sec, $this->min, $this->hour) = array($clock->s, $clock->i, $clock->h);

            foreach($trackers->sortBy(['tracked_at', 'asc']) as $index => $tracker)
            {
                if($index > 0 && $tracker->is_check_in == 1 && $trackers[$index - 1]->is_check_in == 0) {

                    $interval = Carbon::parse($tracker->tracked_at)->diff(Carbon::parse($trackers[$index - 1]->tracked_at));

                    $trackers[$index - 1]->duration = str_pad($interval->h, 2, 0, STR_PAD_LEFT) . ":" . str_pad($interval->i, 2, 0, STR_PAD_LEFT) . ":" . str_pad($interval->s, 2, 0, STR_PAD_LEFT);

                    if($this->hour > $interval->h){
                        $this->hour -= $interval->h;
                    }else{
                        $this->min -= round($interval->h / 60);
                    }

                    if($this->min > $interval->i){
                        $this->min -= $interval->i;
                    }else{
                        $this->sec -= round($interval->i / 60);
                    }

                    if($this->sec > $interval->s)
                        $this->sec -= $interval->s;
                }                                
                $intervalList[] = $tracker;
            }

        }

        $data   = array ( 
            'sec' => $this->sec,
            'min' => $this->min,
            'hour' => $this->hour,
            'user' => $request->user(),
            'intervalList' => $intervalList 
        );

        return $data;
    }

    public function store(Request $request)
    {
        $total = $this->getTimeTrackerOfDateByUser($request)->count();

        User::where('id', $request->user()->id)->update([
            'is_time_tracker_started' => ($request->user()->is_time_tracker_started == 1) ? 0 : 1 
        ]);
                
        TimeTracker::create([
            'date' => $this->currentDate,
            'user_id' => $request->user()->id,
            'tracked_at' => Carbon::now(),
            'note' => 'Break',
            'is_check_in' => $request->is_check_in 
        ]);
    }


    public function edit(Request $request, $id)
    {
        $data = TimeTracker::findOrFail($request->id)->update(['note' => $request->note]);
        return $data; 
    }

    public function getTimeTrackerOfDateByUser(Request $request)
    {
        return TimeTracker::where(['date' => $this->currentDate->format('Y-m-d'), 
                                   'user_id' => $request->user()->id])
                            ->get();
    }

}
