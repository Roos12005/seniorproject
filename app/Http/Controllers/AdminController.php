<?php


namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Neoxygen\NeoClient\ClientBuilder;
use Carbon;

class AdminController extends Controller{

    public function index() {
        $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
            ->setAutoFormatResponse(true)
            ->build();
        

        $q = 'MATCH (n:table) RETURN n, ID(n) as tid';
        $results_table = $client->sendCypherQuery($q)->getResult()->getTableFormat();
        $ret_table = array();
        $curr_time = Carbon\Carbon::now()->timestamp;
        foreach($results_table as $r) {

            // startDate   2015/09/01
            // status  1
            // noOfcallMin 0
            // durationMin 0
            // estimatedExecTime   102
            // endDate 2015/09/15
            // noOfCallMax -1
            // durationMax -1
            // customers   1230123
            // period  Day
            // size    5.03
            // startExecTime   1453739369
            // description This is for testing via command line
            // carrier AIS
            // day 1111111

            $tmp_prog = ($curr_time - $r['n']['startExecTime'])/$r['n']['estimatedExecTime'];
            $progress = $tmp_prog > 1? '100' : $tmp_prog*100;
            $status = $tmp_prog >= 1? 'Ready' : 'Processing'; 

            $tmp = [
                'id' => $r['tid'],
                'description' => $r['n']['description'],
                'noOfCall' => $r['n']['noOfCallMax'] == -1? 
                                '> ' . $r['n']['noOfcallMin'] : $r['n']['noOfcallMin'] . ' - ' . $r['n']['noOfCallMax'],
                'duration' => $r['n']['durationMax'] == -1? 
                                '> ' . $r['n']['durationMin'] : $r['n']['durationMin'] . ' - ' . $r['n']['durationMax'],
                'date' => $r['n']['startDate'] . ' - ' . $r['n']['endDate'],
                'customers' => number_format($r['n']['customers']),
                'size' => $r['n']['size'] . ' GB',
                'carrier' => $r['n']['carrier'],
                'days' => '',
                'period' => '',
                'status' => $status,
                'progress' => $progress

            ];
            array_push($ret_table, $tmp);
        }
        // print_r($ret_table);
        // exit();

        $q = 'MATCH (n:preprocess) RETURN n, ID(n) as pid';
        $results_preprocess = $client->sendCypherQuery($q)->getResult()->getTableFormat();
        // startDate   2015/09/01
        // priority    High
        // description This is for testing via command line
        // noOfcallMin 0
        // carrier AIS
        // durationMin 0
        // noOfCallMax -1
        // endDate 2015/09/15
        // day 1111111
        // durationMax -1
        // period  Day
        $ret_preprocess = array();
        foreach($results_preprocess as $r) {
            $tmp = [
                'id' => $r['pid'],
                'date' => $r['n']['startDate'] . ' - ' . $r['n']['endDate'],
                'priority' => $r['n']['priority'],
                'description' => $r['n']['description'],
                'carrier' => $r['n']['carrier'],
                'period' => '',
                'noOfCall' => $r['n']['noOfCallMax'] == -1? 
                                '> ' . $r['n']['noOfcallMin'] : $r['n']['noOfcallMin'] . ' - ' . $r['n']['noOfCallMax'],
                'duration' => $r['n']['durationMax'] == -1? 
                                '> ' . $r['n']['durationMin'] : $r['n']['durationMin'] . ' - ' . $r['n']['durationMax']
            ];
            array_push($ret_preprocess, $tmp);
        }

            
       return view('admin.adminpanel', ['table' => $ret_table, 'preprocess' => $ret_preprocess]);
   }
}
?>
