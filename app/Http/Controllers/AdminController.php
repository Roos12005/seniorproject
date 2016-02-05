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
        foreach($results_table as $r) {
            
            $formatted_date = \App\Http\Helpers\DateHelper::getStartEndDate(substr($r['n']['startDate'],0,6), substr($r['n']['startDate'],6));
            $progress = \App\Http\Helpers\ExecHelper::calculateProgress($r['n']['startExecTime'], $r['n']['estimatedExecTime']);
            $tmp = [
                'id' => $r['tid'],
                'description' => $r['n']['description'],
                'noOfCall' => 'Not yet Support!',
                'duration' => $r['n']['durationMax'] == -1? 
                                'More than ' . $r['n']['durationMin'] : $r['n']['durationMin'] . ' - ' . $r['n']['durationMax'],
                'date' => $formatted_date['startDate'] . ' - ' . $formatted_date['endDate'],
                'customers' => number_format($r['n']['customers']),
                'size' => $r['n']['size'],
                'carrier' => \App\Http\Helpers\UnaryHelper::unaryToCarrierReadable($r['n']['rnCode']),
                'days' => \App\Http\Helpers\UnaryHelper::unaryToDaysReadable($r['n']['callDay']),
                'period' => $r['n']['endTime'] == -1? 
                                'More than ' . $r['n']['startTime'] : $r['n']['startTime'] . ' - ' . $r['n']['endTime'],
                'mode' => $r['n']['mode'],
                'progress' => $progress['progress'],
                'status' => $progress['status'],
                'type' => $r['n']['type']
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
        $mode = \App\Http\Helpers\UnaryHelper::unaryToMode($rec['mode']);

        if($type == 'batch') {
            $filters = \App\Http\Helpers\ExecHelper::prepareData($filters);
            $info = \App\Http\Helpers\ExecHelper::estimateResource($filters);
            $ret = [
                'customers' => $info['customers']['nodes'],
                'execTime' => $info['execTime']
            ];
            return $ret;
        } elseif ($type == 'preprocess') {
            
        } else {
            // should not be triggered
        }
        return $res;
   }

   public function processSetup() {
        $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
            ->setAutoFormatResponse(true)
            ->build();
        $rec = Request::all();
        $filters = $rec['filter'];
        $type = $rec['type'];
        

        if($type == 'batch') {
            $q = "CREATE (n:table {
                    type: 1, 
                    startDate: " . $filters['startDate'] . ",
                    callDay : " . $filters['callDay'] . ",
                    rnCode : " . $filters['rnCode'] . ",
                    startTime : " . $filters['startTime'][0] . ",
                    endTime: " . $filters['startTime'][1] . ",
                    durationMin: " . $filters['duration'][0] . ",
                    durationMax: " . $filters['duration'][1] . ",
                    description : '" . $rec['description'] . "',
                    mode : " . $rec['mode'] . ",
                    customers : " . $rec['others']['customers'] .",
                    startExecTime: " . Carbon\Carbon::now()->timestamp . ",
                    size: '- ',
                    estimatedExecTime: " . $rec['others']['estimatedExecTime'] . "
                }) RETURN ID(n) as nid";
            $results_table = $client->sendCypherQuery($q)->getResult()->getTableFormat();
            $filters = \App\Http\Helpers\ExecHelper::prepareData($filters);
            $mode = \App\Http\Helpers\UnaryHelper::unaryToMode($rec['mode']);
            $res = \App\Http\Helpers\ExecHelper::beginProcess($filters, $results_table[0]['nid']);


            $ret = [
                'filters' => $filters,
                'nid' => $results_table[0]['nid'],
                'mode' => $mode
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
