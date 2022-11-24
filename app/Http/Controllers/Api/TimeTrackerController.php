<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\TimeTracker;
use Carbon\Carbon;

class TimeTrackerController extends Controller
{
    public $currentDate;
    public $workedHour, $workedMin, $workedSec, $breakHour, $breakMin, $breakSec;

    public function __construct()
    {
        $this->currentDate = Carbon::now();
        $this->workedHour = 0;
        $this->workedMin  = 0;
        $this->workedSec  = 0;
        $this->breakHour  = 0;
        $this->breakMin   = 0;
        $this->breakSec   = 0;
    }

    public function index(Request $request)
    {
        $trackers     = $this->getTimeTrackerOfDateByUser($this->currentDate->format('Y-m-d'), $request->user()->id);
                
        if($trackers->count() == 0)
            User::where('id', $request->user()->id)->update(['is_time_tracker_started' => 0]);

        list($this->workedHour, $this->workedMin, $this->workedSec, 
             $this->breakHour, $this->breakMin, $this->breakSec, 
             $intervalList) = $this->calculateListTrackersResult($trackers);

        $data   = array ( 
            'hour' => $this->workedHour,
            'min'  => $this->workedMin,
            'sec'  => $this->workedSec,
            'user' => $request->user(),
            'intervalList' => $intervalList 
        );

        return $data;
    }

    public function store(Request $request)
    {
        $total = $this->getTimeTrackerOfDateByUser($this->currentDate->format('Y-m-d'), $request->user()->id)->count();

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

    public function getTimeTrackerOfDateByUser(string $date, int $user_id)
    {
        return TimeTracker::where(['date' => $date, 'user_id' => $user_id])->get();
    }


    public function report(Request $request)
    {
        $today    = Carbon::now()->format('Y-m-d');
        $fromDate = $request->input('dateFrom') ?: Carbon::now()->subDays(1)->format('Y-m-d');
        $toDate   = $request->input('dateTo') ?: $today;
        
        $usersGroupByDate   = TimeTracker::select('user_id','date')
                                        ->whereBetween('date', [$fromDate, $toDate])
                                        ->groupBy(['user_id', 'date'])
                                        ->get();

        $data = [];
        
        foreach($usersGroupByDate as $row)
        {
            $user     = User::where('id', $row->user_id)->first();

            if(!$user->exists())
                continue;

            $trackers = $this->getTimeTrackerOfDateByUser($row->date, $row->user_id);
            
            $interval = $this->calculateListTrackersResult($trackers);

            $lastTrack = 'Missed Check-out';

            if($trackers->last()->is_check_in == 0)
                $lastTrack = $trackers->last()->tracked_at;

            if($trackers->last()->is_check_in == 1 && $trackers->last()->date == $today)
                $lastTrack = 'Reporting';

            $data[] = array(
                'date'     => $row->date,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email'  => $user->email,
                'workedHour ' => $interval[0],
                'workedMin  ' => $interval[1],
                'workedSec  ' => $interval[2],
                'breakHour  ' => $interval[3],
                'breakMin   ' => $interval[4],
                'breakSec   ' => $interval[5],
                'workedTimeFormat' => sprintf('%s:%s:%s', 
                    str_pad($interval[0], 2, "0", STR_PAD_LEFT),
                    str_pad($interval[1], 2, "0", STR_PAD_LEFT),
                    str_pad($interval[2], 2, "0", STR_PAD_LEFT)
                ),
                'breakTimeFormat' => sprintf('%s:%s:%s', 
                    str_pad($interval[3], 2, "0", STR_PAD_LEFT),
                    str_pad($interval[4], 2, "0", STR_PAD_LEFT),
                    str_pad($interval[5], 2, "0", STR_PAD_LEFT)
                ),
                'initialTrack' => $trackers->first()->tracked_at,
                'lastTrack'    => $lastTrack
            );
        }
        
        return $data;
    }



    public function calculateListTrackersResult($trackers)
    {
        $intervalList = [];
        $workedHour = 0;
        $workedMin  = 0;
        $workedSec  = 0;
        $breakHour  = 0;
        $breakMin   = 0;
        $breakSec   = 0;

        if($trackers->count() > 0)
        {
            $initialCheckIn = Carbon::parse($trackers->firstOrFail()->tracked_at);
            
            //If pause is the last item then diff with this tracked_at 
            if($trackers[$trackers->count()-1]->is_check_in == 0){
                $clock = Carbon::parse($trackers[$trackers->count()-1]->tracked_at)->diff($initialCheckIn);
            }else{
                $clock = Carbon::now()->diff($initialCheckIn);
            }

            list($workedSec, $workedMin, $workedHour) = array($clock->s, $clock->i, $clock->h);

            foreach($trackers->sortBy(['tracked_at', 'asc']) as $index => $tracker)
            {
                if($index > 0 && $tracker->is_check_in == 1 && $trackers[$index - 1]->is_check_in == 0) {

                    $breakTaken = Carbon::parse($tracker->tracked_at)->diff(Carbon::parse($trackers[$index - 1]->tracked_at));

                    $trackers[$index - 1]->duration = str_pad($breakTaken->h, 2, 0, STR_PAD_LEFT) . ":" . str_pad($breakTaken->i, 2, 0, STR_PAD_LEFT) . ":" . str_pad($breakTaken->s, 2, 0, STR_PAD_LEFT);

                    if($workedHour > $breakTaken->h){
                        $workedHour -= $breakTaken->h;
                    }else{
                        $workedMin -= round($breakTaken->h / 60);
                    }
                    if($workedMin > $breakTaken->i){
                        $workedMin -= $breakTaken->i;
                    }else{
                        $workedSec -= round($breakTaken->i / 60);
                    }
                    if($workedSec > $breakTaken->s)
                        $workedSec -= $breakTaken->s;
                
                    $breakHour+= $breakTaken->h;
                    $breakMin += $breakTaken->i;
                    $breakSec += $breakTaken->s;
                }                               
                $intervalList[] = $tracker;
            }

            //RECALCULATE BREAK TIME
            if($breakSec >= 60) {
                $sec = $breakSec / 60;
                $min = floor($sec);
                $sec = $sec - $min;
                $breakMin+= $min;
                $breakSec = round($sec * 60);
            }
            if($breakMin >= 60) {
                $min = $breakMin / 60;
                $hour= floor($min);
                $min = $min - $hour;
                $breakHour += $hour;
                $breakMin = round($min * 60);
            }
        }

        return array($workedHour, 
                    $workedMin, 
                    $workedSec, 
                    $breakHour,
                    $breakMin,
                    $breakSec,
                    $intervalList);
    }



    


}
