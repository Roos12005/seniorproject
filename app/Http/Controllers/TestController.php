<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\App;
use App\Models\Call_Detail_Records;
use App\Models\Users;
use Request;
use File;
use Neoxygen\NeoClient\ClientBuilder;
use Plupload;
use Log;


use \App\Http\Classes\KKLayout as KKLayout;
use \App\Http\Classes\Neo4JConnector as Neo4JConnector;
use \App\Http\Classes\Neo4JValidator as Neo4JValidator;

class TestController extends Controller{
    
    public function index() {
        
        return view('test.index');
    }

    public function getXY() {
        $nodes = array();

        /* Create 11 random nodes */ 
        for($i=0;$i<=100;$i++)
        {
            $Connections = ""; $RandCx1 = rand(0,1);
            for($j=0;$j<=$RandCx1;$j++)
            {
                $RandCx2 = rand(0,100);
                if ( $RandCx2 != $j )
                { $Connections[] = $RandCx2; }
            }
            $nodes[$i] = array('idx' => $i, "conn" => $Connections);
        }
        $kklayout = new KKLayout ( $nodes );
        $kklayout->initialize ();
        while ( ! $kklayout->done () ) {
            $kklayout->step ();
        }

        return response()->json($kklayout->xydata);
    }  
}
?>