<?php


namespace App\Http\Controllers;
use Request;
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

        $q = 'MATCH (n:preprocess) RETURN n, ID(n) as pid';
        $results_preprocess = $client->sendCypherQuery($q)->getResult()->getTableFormat();
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

   public function getEstimation() {
        // TODO : flag Mode in processing
        $rec = Request::all();
        $filters = $rec['filter'];
        $type = $rec['type'];
        $startEndDate = \App\Http\Helpers\DateHelper::getStartEndDate(substr($filters['startDate'],0,6), substr($filters['startDate'],6));
        $filters['startDate'] = [$startEndDate['startDate'], $startEndDate['endDate']];
        $mode = \App\Http\Helpers\UnaryHelper::unaryToMode($rec['mode']);

        if($type == 'batch') {
            $filters = \App\Http\Helpers\ExecHelper::prepareData($filters);
            $info = \App\Http\Helpers\ExecHelper::estimateResource($filters);
            $ret = [
                'customers' => $info['nodes'],
                'execTime' => 100,
                'filters' => $filters
            ];
            return $ret;
        } elseif ($type == 'preprocess') {
            
        } else {
            // should not be triggered
        }
        return $res;
   }

   public function processSetup() {
        // TODO : flag Mode in processing
        $rec = Request::all();
        $filters = $rec['filter'];
        $type = $rec['type'];
        $startEndDate = \App\Http\Helpers\DateHelper::getStartEndDate(substr($filters['startDate'],0,6), substr($filters['startDate'],6));
        $filters['startDate'] = [$startEndDate['startDate'], $startEndDate['endDate']];
        $mode = \App\Http\Helpers\UnaryHelper::unaryToMode($rec['mode']);

        if($type == 'batch') {
            $filters = \App\Http\Helpers\ExecHelper::prepareData($filters);
            $res = \App\Http\Helpers\ExecHelper::beginProcess($filters);
            $info = \App\Http\Helpers\ExecHelper::estimateResource($filters);
            $ret = [
                'customers' => $info['nodes'],
                'execTime' => 100,
                'filters' => $filters
            ];
            return $ret;
        } elseif ($type == 'preprocess') {
            
        } else {
            // should not be triggered
        }
        return $res;
   }
}
?>
