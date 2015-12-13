<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\App;
class AnalysisController extends Controller{

    public function getIndex() {
        return view('analysis.analysis');
    }
   
}
?>