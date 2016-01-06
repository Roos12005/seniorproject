<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\App;
use App\Models\Call_Detail_Records;
use App\Models\Users;
use File;

class AnalysisController extends Controller{

    public function getIndex() {
       return view('analysis.analysis');
   }
   public function test() {
       // $cdr = DB::table('call_detail_records')->where('b_no','2')->pluck('mobile_no');
       $name = Users::all()->pluck('number');
       return $name;
   }

    //Get all CDR
    public function getCDR() {
        $users = Users::all();
        $cdr_list = array();
        foreach($users as $user) {
            $user_info = [
              'number' => $user->Number,
            ];
            array_push($cdr_list, $user_info);
        }

        return  response()->json(['cdr_list' => $cdr_list]);
    } 

    // TODO : This function will be removed when my experiment is done!
   public function testgraph() {
    return view('experiment.testgraph');
    }
}
?>