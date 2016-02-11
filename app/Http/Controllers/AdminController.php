<?php


namespace App\Http\Controllers;
use Request;
use Neoxygen\NeoClient\ClientBuilder;
use Carbon;
use \App\Http\Helpers\DateHelper as DateHelper;
use \App\Http\Helpers\ExecHelper as ExecHelper;
use \App\Http\Helpers\UnaryHelper as UnaryHelper;

use \App\Http\Classes\Neo4JTabular as Neo4JTabular;
class AdminController extends Controller{

    public function index() {
        // Instantiate Neo4JConnector - Neo4JTabular 
        $neo = new Neo4JTabular();
        $neo->connectDatabase('default', 'http', 'localhost', 7474, 'neo4j', 'aiscu');
        
        // Query All Data needed
        $preprocess_settings = $neo->getReadablePreprocessSettings();
        $preprocess_jobs = $neo->getReadablePreprocessJobs();
        $batch_jobs = $neo->getReadableBatchJobs();

        return view('admin.adminpanel', [
                    'preprocess_settings' => $preprocess_settings, 
                    'preprocess_jobs' => $preprocess_jobs, 
                    'batch_jobs' => $batch_jobs
                    ]);
       //  $client = ClientBuilder::create()
       //      ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
       //      ->setAutoFormatResponse(true)
       //      ->build();
        
       //  $q = 'MATCH (n:table) RETURN n, ID(n) as tid';
       //  $results_table = $client->sendCypherQuery($q)->getResult()->getTableFormat();
       //  $ret_table = array();
       //  foreach($results_table as $r) {
            
       //      $formatted_date = DateHelper::getStartEndDate(substr($r['n']['startDate'],0,6), substr($r['n']['startDate'],6));
       //      $progress = ExecHelper::calculateProgress($r['n']['startExecTime'], $r['n']['estimatedExecTime']);
       //      $tmp = [
       //          'id' => $r['tid'],
       //          'description' => $r['n']['description'],
       //          'noOfCall' => 'Not yet Support!',
       //          'duration' => $r['n']['durationMax'] == -1? 
       //                          'More than ' . $r['n']['durationMin'] : $r['n']['durationMin'] . ' - ' . $r['n']['durationMax'],
       //          'date' => DateHelper::formatDate($formatted_date['startDate']) . ' - ' . DateHelper::formatDate($formatted_date['endDate']),
       //          'customers' => number_format($r['n']['customers']),
       //          'size' => $r['n']['size'],
       //          'carrier' => UnaryHelper::unaryToCarrierReadable($r['n']['rnCode']),
       //          'days' => UnaryHelper::unaryToDaysReadable($r['n']['callDay']),
       //          'period' => $r['n']['endTime'] == -1? 
       //                          'After ' . number_format($r['n']['startTime'], 2, '.', '') : number_format($r['n']['startTime'], 2, '.', '') . ' - ' . number_format($r['n']['endTime'], 2, '.', ''),
       //          'mode' => $r['n']['mode'],
       //          'progress' => $progress['progress'],
       //          'status' => $progress['status'],
       //          'type' => $r['n']['type']
       //      ];
       //      array_push($ret_table, $tmp);
       //  }

       //  $q = 'MATCH (n:preprocess) RETURN n, ID(n) as pid';
       //  $results_preprocess = $client->sendCypherQuery($q)->getResult()->getTableFormat();
       //  $ret_preprocess = array();
       //  $priority_text = ['Low', 'Medium', 'High'];
       //  foreach($results_preprocess as $r) {
       //      $tmp = [
       //          'id' => $r['pid'],
       //          'date' => $r['n']['startDate'],
       //          'priority' => $priority_text[$r['n']['priority']],
       //          'description' => $r['n']['description'],
       //          'carrier' => UnaryHelper::unaryToCarrierReadable($r['n']['rnCode']),
       //          'days' => UnaryHelper::unaryToDaysReadable($r['n']['callDay']),
       //          'period' => $r['n']['endTime'] == -1? 
       //                          'More than ' . $r['n']['startTime'] : $r['n']['startTime'] . ' - ' . $r['n']['endTime'],
       //          'noOfCall' => /*$r['n']['noOfCallMax'] == -1? 
       //                          '> ' . $r['n']['noOfcallMin'] : $r['n']['noOfcallMin'] . ' - ' . $r['n']['noOfCallMax']*/ 1,
       //          'duration' => $r['n']['durationMax'] == -1? 
       //                          'More than ' . $r['n']['durationMin'] : $r['n']['durationMin'] . ' - ' . $r['n']['durationMax']
       //      ];
       //      array_push($ret_preprocess, $tmp);
       //  }

            
       // return view('admin.adminpanel', ['table' => $ret_table, 'preprocess' => $ret_preprocess]);
   }

   public function getEstimation() {
        // TODO : flag Mode in processing
        $rec = Request::all();
        $filters = $rec['filter'];
        $type = $rec['type'];
        $mode = UnaryHelper::unaryToMode($rec['mode']);

        if($type == 'batch') {
            $filters = ExecHelper::prepareData($filters);
            $info = ExecHelper::estimateResource($filters);
            $ret = [
                'customers' => $info['customers']['nodes'],
                'execTime' => $info['execTime']
            ];
            return $ret;
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
            $q = "CREATE (n:BatchJob {
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
            $filters = ExecHelper::prepareData($filters);
            // $mode = \App\Http\Helpers\UnaryHelper::unaryToMode($rec['mode']);
            // $res = \App\Http\Helpers\ExecHelper::beginProcess($filters, $results_table[0]['nid']);
            
            // prepare data to display
            $filters['startDate'] = DateHelper::formatDate($filters['startDate'][0]) . ' - ' . \App\Http\Helpers\DateHelper::formatDate($filters['startDate'][1]);

            $filters['callDay'] = UnaryHelper::arrToReadable($filters['callDay']);
            $filters['rnCode'] = UnaryHelper::arrToReadable($filters['rnCode']);
            $filters['duration'] = UnaryHelper::rangeToReadable($filters['duration'], '');
            $filters['startTime'] = UnaryHelper::rangeToReadable($filters['startTime'], 'time');
            // $filters['mode'] = \App\Http\Helpers\UnaryHelper::arrToUnary($rec['mode']);

            $ret = [
                'filters' => $filters,
                'nid' => $results_table[0]['nid'],
                'mode' => $rec['mode']
            ];

            return $ret;
        } elseif ($type == 'preprocess') {
            
            $q = "CREATE (n:PreprocessSetting {
                    startDate: " . $filters['startDate'] . ",
                    callDay : " . $filters['callDay'] . ",
                    rnCode : " . $filters['rnCode'] . ",
                    startTime : " . $filters['startTime'][0] . ",
                    endTime: " . $filters['startTime'][1] . ",
                    durationMin: " . $filters['duration'][0] . ",
                    durationMax: " . $filters['duration'][1] . ",
                    description : '" . $rec['description'] . "',
                    mode : " . $rec['mode'] . ",
                    priority : " . $filters['priority'] . "
                }) RETURN ID(n) as nid";
            $results_table = $client->sendCypherQuery($q)->getResult()->getTableFormat();
            $filters = ExecHelper::prepareData($filters);
            return ['f' => $filters];
        } else {
            // should not be triggered
        }
        return $rec;
   }

   public function startProcess() {
        $rec = Request::all();
        $filters = $rec['filter'];
        $filters = ExecHelper::prepareData($filters);
        $res = ExecHelper::beginProcess($filters, $rec['nid']);
        return "";
   }
}


?>
