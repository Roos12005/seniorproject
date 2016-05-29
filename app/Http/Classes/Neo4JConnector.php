<?php 

namespace App\Http\Classes;

use Neoxygen\NeoClient\ClientBuilder;
use Carbon;
use \App\Http\Helpers\DateHelper as DateHelper;
use \App\Http\Helpers\UnaryHelper as UnaryHelper;
use Log;
use \App\Http\Classes\Neo4JValidator as Neo4JValidator;

class Neo4JConnector {

    // ------------------------------------------------ Class Attributes -----------------------------------------

    public static $priority = ['Low', 'Medium', 'High'];

    public $connector;

    // -----------------------------------------------------------------------------------------------------------

    // ------------------------------------------------ Constructor ----------------------------------------------

    public function __construct($db_name, $protocol, $host_name, $port, $username, $password) {
        $this->connector = NULL;
        $this->connectDatabase($db_name, $protocol, $host_name, $port, $username, $password);
    } 

    // ------------------------------------------------------------------------------------------------------------

    // ------------------------------------------------ Public Functions ------------------------------------------

    public function getConnector() {
        return $this->connector;
    }

    public function queryAllBatchJob() {
        // Reject query whenever $this->connector has not been initialized
        $this->checkConnection();

        // Query All Data of Batch Job
        $q = 'MATCH (n:BatchJob) RETURN n, ID(n) as jid';

        return $this->connector->sendCypherQuery($q)->getResult()->getTableFormat();
    }

    public function queryAllPreprocessJob() {
        // Reject query whenever $this->connector has not been initialized
        $this->checkConnection();

        // Query All Data of Preprocess Job
        $q = 'MATCH (n:PreprocessJob) RETURN n, ID(n) as jid';

        return $this->connector->sendCypherQuery($q)->getResult()->getTableFormat();
    }

    public function queryAllPreprocessSetting() {
        // Reject query whenever $this->connector has not been initialized
        $this->checkConnection();

        // Query All Data of Preprocess Setting
        $q = 'MATCH (n:PreprocessSetting) RETURN n, ID(n) as pid';

        return $this->connector->sendCypherQuery($q)->getResult()->getTableFormat();
    }

    public function getAvailableDatabase() {
        return $this->execQuery("MATCH (n:Database) RETURN n, ID(n) as dbid");
    }

    public function getReadableBatchJobs() {
        $raw = $this->queryAllBatchJob();
        return $this->toReadableJob($raw);
    }

    public function getReadablePreprocessJobs() {
        $raw = $this->queryAllPreprocessJob();
        return $this->toReadableJob($raw);
    }

    public function getReadablePreprocessSettings() {
        $raw = $this->queryAllPreprocessSetting();
        return $this->toReadableSetting($raw);
    }

    public function toReadableJob($jobs) {
        /*
         * *************** Return Format ***************
         *  id - Number
         *  description - Text
         *  noOfCall - Not yet support !
         *  duration - 1. More than ##  or 2. ## - ##
         *  date - yyyy/mm/dd - yyyy/mm/dd
         *  customers - Number
         *  size - Number
         *  carrier - First, Second, Third, ...
         *  days - First, Second, Third, ...
         *  period - 1. After ##.##  or 2. ##.## - ##.##
         *  progress - Number (<= 100)
         *  status - In Progress or Ready
         * *********************************************
         */

        $result = array();
        foreach($jobs as $r) {
            if($r['n']['status'] == 'Ready' && $r['n']['customers'] == 'unknown') {
                $q = 'MATCH (n:Processed' . $r['jid'] . ') RETURN COUNT(n) as c_n';
                $tmpres = $this->connector->sendCypherQuery($q)->getResult()->getTableFormat()[0];
                $r['n']['customers'] = $tmpres['c_n'];
                $r['n']['size'] = $tmpres['c_n']; // some calculation
                $q = 'MATCH (n:BatchJob) WHERE ID(n) = ' . $r['jid'] . ' SET n.customers = ' . $tmpres['c_n'] . ' RETURN n;';
                $this->connector->sendCypherQuery($q)->getResult();
            }

            $formatted_date = DateHelper::getStartEndDate(substr($r['n']['startDate'],0,6), substr($r['n']['startDate'],6));
            $tmp = [
            'database' => $r['n']['database'],
            'id' => $r['jid'], 
            'description' => $r['n']['description'], 
            'noOfIncomingCall' => $r['n']['incomingMax'] == -1? 
            'More than ' . $r['n']['incomingMin'] : $r['n']['incomingMin'] . ' - ' . $r['n']['incomingMax'], 
            'noOfOutgoingCall' => $r['n']['outgoingMax'] == -1? 
            'More than ' . $r['n']['outgoingMin'] : $r['n']['outgoingMin'] . ' - ' . $r['n']['outgoingMax'], 
            'duration' => $r['n']['durationMax'] == -1? 
            'More than ' . $r['n']['durationMin'] : $r['n']['durationMin'] . ' - ' . $r['n']['durationMax'],
            'date' => DateHelper::formatDate($formatted_date['startDate']) . ' - ' . DateHelper::formatDate($formatted_date['endDate']),
            'customers' => $r['n']['customers'],
            'size' => $r['n']['size'],
            'carrier' => UnaryHelper::unaryToCarrierReadable($r['n']['rnCode']),
            'days' => UnaryHelper::unaryToDaysReadable($r['n']['callDay']),
            'period' => $r['n']['endTime'] == -1? 
            'After ' . number_format($r['n']['startTime'], 2, '.', '') : number_format($r['n']['startTime'], 2, '.', '') . ' - ' . number_format($r['n']['endTime'], 2, '.', ''),
            'type' => $r['n']['type'],
            'status' => $r['n']['status']
            ];
            array_push($result, $tmp);
        }

        return $result;
    }

    public function toReadableSetting($settings) {

        /*
         * *************** Return Format ***************
         *  id - Number
         *  date - yyyy/mm/dd - yyyy/mm/dd
         *  priority - "High", "Medium", "Low"
         *  description - Text
         *  carrier - First, Second, Third, ...
         *  days - First, Second, Third, ...
         *  period - 1. After ##.##  or 2. ##.## - ##.##
         *  noOfCall - Not yet support !
         *  duration - 1. More than ##  or 2. ## - ##
         * *********************************************
         */

        $result = array();
        foreach($settings as $r) {
            $date = substr($r['n']['startDate'],-1);
            if($date == '0') {
                $date = 'Whole Month';
            } else {
                $date = 'Week ' . $date;
            }

            $tmp = [
            'database' => $r['n']['database'],
            'id' => $r['pid'],
            'date' => $date,
            'priority' => Neo4JConnector::$priority[$r['n']['priority']],
            'description' => $r['n']['description'],
            'carrier' => UnaryHelper::unaryToCarrierReadable($r['n']['rnCode']),
            'days' => UnaryHelper::unaryToDaysReadable($r['n']['callDay']),
            'period' => $r['n']['endTime'] == -1? 
            'After ' . number_format($r['n']['startTime'], 2, '.', '') : number_format($r['n']['startTime'], 2, '.', '') . ' - ' . number_format($r['n']['endTime'], 2, '.', ''),
            'noOfIncomingCall' => $r['n']['incomingMax'] == -1? 
                'More than ' . $r['n']['incomingMin'] : $r['n']['incomingMin'] . ' - ' . $r['n']['incomingMax'],
            'noOfOutgoingCall' => $r['n']['outgoingMax'] == -1? 
                'More than ' . $r['n']['outgoingMin'] : $r['n']['outgoingMin'] . ' - ' . $r['n']['outgoingMax'],
            'duration' => $r['n']['durationMax'] == -1? 
                'More than ' . $r['n']['durationMin'] : $r['n']['durationMin'] . ' - ' . $r['n']['durationMax']
            ];
            array_push($result, $tmp);
        }
            return $result;
        }


        public function setUpBatchProcess($filters, $desc, $db) {
            // Reject query whenever $this->connector has not been initialized
                $this->checkConnection();

            // Prepare Query Statement
                $q = "CREATE (n:BatchJob {
                type: 1, 
                startDate: " . $filters['startDate'] . ",
                callDay : " . $filters['callDay'] . ",
                rnCode : " . $filters['rnCode'] . ",
                startTime : " . $filters['startTime'][0] . ",
                endTime: " . $filters['startTime'][1] . ",
                durationMin: " . $filters['duration'][0] . ",
                durationMax: " . $filters['duration'][1] . ",
                incomingMin: " . $filters['incoming'][0] . ",
                incomingMax: " . $filters['incoming'][1] . ",
                outgoingMin: " . $filters['outgoing'][0] . ",
                outgoingMax: " . $filters['outgoing'][1] . ",
                description : '" . $desc . "',
                startExecTime: " . Carbon\Carbon::now()->timestamp . ",
                customers: 'unknown',
                size: 'unknown',
                database : '" . $db . "',
                status: 'Processing'
            }) RETURN ID(n) as nid";
            
            // Save new Batch Process Information in Neo4J
            $result = $this->connector->sendCypherQuery($q)->getResult()->getTableFormat();

            // Formatting Input for Displaying
            $filters = $this->prepareData($filters);
            $filters['startDate'] = DateHelper::formatDate($filters['startDate'][0]) . ' - ' . DateHelper::formatDate($filters['startDate'][1]);
            $filters['callDay'] = UnaryHelper::arrToReadable($filters['callDay']);
            $filters['rnCode'] = UnaryHelper::arrToReadable($filters['rnCode']);
            $filters['duration'] = UnaryHelper::rangeToReadable($filters['duration'], '');
            $filters['incoming'] = UnaryHelper::rangeToReadable($filters['incoming'], '');
            $filters['outgoing'] = UnaryHelper::rangeToReadable($filters['outgoing'], '');
            $filters['startTime'] = UnaryHelper::rangeToReadable($filters['startTime'], 'time');

            return [
                'filters' => $filters,
                'nid' => $result[0]['nid'],
            ];
        }

        public function setUpPreprocess($filters, $desc, $db) {
            // Reject query whenever $this->connector has not been initialized
            $this->checkConnection();

            // Prepare Query Statement
            $q = "CREATE (n:PreprocessSetting {
            startDate: '" . $filters['startDate'] . "',
            callDay : '" . $filters['callDay'] . "',
            rnCode : '" . $filters['rnCode'] . "',
            startTime : '" . $filters['startTime'][0] . "',
            endTime: '" . $filters['startTime'][1] . "',
            durationMin: " . $filters['duration'][0] . ",
            durationMax: " . $filters['duration'][1] . ",
            incomingMin: " . $filters['incoming'][0] . ",
            incomingMax: " . $filters['incoming'][1] . ",
            outgoingMin: " . $filters['outgoing'][0] . ",
            outgoingMax: " . $filters['outgoing'][1] . ",
            description : '" . $desc . "',
            database : '" . $db . "',
            priority : " . $filters['priority'] . "
            }) RETURN ID(n) as nid";

                // Save Preprocess Setting in Neo4J and Get the ID
            $result = $this->connector->sendCypherQuery($q)->getResult()->getTableFormat();

            $date = substr($filters['startDate'],-1);
            if($date == '0') {
                $date = 'Whole Month';
            } else {
                $date = 'Week ' . $date;
            }

            // Formatting Input for Displaying
            $filters = $this->prepareData($filters);
            $filters['startDate'] = $date;
            $filters['callDay'] = UnaryHelper::arrToReadable($filters['callDay']);
            $filters['rnCode'] = UnaryHelper::arrToReadable($filters['rnCode']);
            $filters['duration'] = UnaryHelper::rangeToReadable($filters['duration'], '');
            $filters['incoming'] = UnaryHelper::rangeToReadable($filters['incoming'], '');
            $filters['outgoing'] = UnaryHelper::rangeToReadable($filters['outgoing'], '');
            $filters['startTime'] = UnaryHelper::rangeToReadable($filters['startTime'], 'time');

            return ['filters' => $filters, 'pid' => $result[0]['nid']];
        }

        public function startBatchProcess($filters, $nid, $db) {
            ignore_user_abort(true);
            $filters = $this->prepareData($filters);
            $res = $this->beginProcess($filters, $nid, $db, false);
        }

        public function deleteData($type, $nid) {

            return $this->deleteSource($type, $nid) | $this->deleteLabel($type, $nid) | $this->deleteCommunities($type, $nid);
        }

        public function doByScheduling() {
            $q = "MATCH (n:PreprocessSetting) RETURN n, ID(n) as nid ORDER BY n.priority DESC";
            $result = $this->connector->sendCypherQuery($q)->getResult()->getTableFormat();

                // prepare for exec

            foreach($result as $key => $r) {

                $filters = [
                'startDate' => $r['n']['startDate'],
                'callDay' => $r['n']['callDay'],
                'startTime' => [$r['n']['startTime'], $r['n']['endTime']],
                'duration' => [$r['n']['durationMin'], $r['n']['durationMax']],
                'incoming' => [$r['n']['incomingMin'], $r['n']['incomingMax']],
                'outgoing' => [$r['n']['outgoingMin'], $r['n']['outgoingMax']],
                'callDay' => $r['n']['callDay'],
                'rnCode' => $r['n']['rnCode']
                ];

                $db = $r['n']['database'];
                $desc = $r['n']['description'];

                $startDate = substr($filters['startDate'],-1);
                $q = "CREATE (n:PreprocessJob {
                    type: 1, 
                    callDay : " . $filters['callDay'] . ",
                    rnCode : " . $filters['rnCode'] . ",
                    startTime : " . $filters['startTime'][0] . ",
                    endTime: " . $filters['startTime'][1] . ",
                    durationMin: " . $filters['duration'][0] . ",
                    durationMax: " . $filters['duration'][1] . ",
                    incomingMin: " . $filters['incoming'][0] . ",
                    incomingMax: " . $filters['incoming'][1] . ",
                    outgoingMin: " . $filters['outgoing'][0] . ",
                    outgoingMax: " . $filters['outgoing'][1] . ",
                    description : '" . $desc . "',
                    database : '" . $db . "',
                    startExecTime: " . Carbon\Carbon::now()->timestamp . ",";
                    

                

                    // Each Scheduler Setting
                $filters = $this->prepareData($filters);

                    // Date Correction - to Current Year and Month
                $filters['startDate'] = $this->toCurrentYearMonth($filters['startDate']);
                $startDate = substr($filters['startDate'][0],0,6) . $startDate;

                $q = $q . 
                   "startDate: " . $startDate . ",
                    customers: 'unknown',
                    size: 'unknown',
                    status: 'Processing'
                }) RETURN ID(n) as nid";
                $ret = $this->execQuery($q)[0];
                $nid = $ret['nid'];

                    // Start Processing
                $this->beginProcess($filters, $nid, $db, true);

            }
        }

        public function queryNodesForCSV($id) {

            $q = 'MATCH (n:Processed' . $id . ') RETURN distinct n.communityID';
            $results = $this->connector->sendCypherQuery($q)->getResult()->getTableFormat();
            $communities_num = count($results);

            $communities_list = array();
            for ($x = 0; $x < $communities_num; $x++) {
              $communities_list[$x] = array();
          }

          $r = 'MATCH (n:Processed' . $id . ') RETURN n, n.communityID';
          $results = $this->connector->sendCypherQuery($r)->getResult()->getTableFormat();
          foreach($results as $key => $result) {
            $user_info = [
            'label' => $result['n']['a_number'],
            'Betweenness Centrality' => $result['n']['betweenness'],
            'Modularity Class' => $result['n']['communityID'],
            'Eccentricity' => $result['n']['eccentricity'],
            'Closeness Centrality' => $result['n']['closeness'],
            'Age' => $result['n']['age'],
            'Gender' => $result['n']['gender'],
            'RnCode' => $result['n']['carrier'],
            'Promotion' => $result['n']['promotion'],
            'NoOfCall' => $result['n']['incoming'],
            'NoOfReceive' => $result['n']['outgoing']
            ];

            array_push($communities_list[$result['n']['communityID']], $user_info);
        }

        for ($x = 0; $x < count($communities_list); $x++) {
          usort($communities_list[$x], function($a,$b){
            if ($a['closeness']==$b['closeness']) return 0;
            return ($a['closeness']>$b['closeness'])?-1:1;
        });
        }

        return $communities_list;
        }

        public function grantLock($db_name) {

            return $this->lockWrite($db_name);
        }

        public function releaseLock() {
            return $this->unlockWrite();
        }

        public function execQuery($query) {
            return $this->connector->sendCypherQuery($query)->getResult()->getTableFormat();
        }

        public function deleteDatabase($db_id) {
            $q = "MATCH (n:Database) WHERE ID(n)=" . $db_id . " DELETE n;";
            $this->execQuery($q);
        }

        public function renameDatabase($db_id, $new_name) {
            $q = "MATCH (n:Database) WHERE ID(n)=" . $db_id . " SET n.name='" . $new_name . "';";
            $this->execQuery($q);
        }

        public function findCommunity($id, $number) {
            $q = "MATCH (n:Processed" . $id .") WHERE n.number='" . $number . "' RETURN n.communityID;";
            return $this->execQuery($q)[0]['n.communityID'];
        }

        public function getNeighbors($id, $selectedNode) {
            $node_list = array();
            $edge_list = array();
            $addedNode = array();
            $q = "MATCH (n:Processed" . $id . ")-[r:aCall]->(m) WHERE r.a_number='" . $selectedNode . "' OR r.b_number='" . $selectedNode . "' return n, ID(n) as n_id, m, ID(m) as m_id, r, ID(r) as r_id";
            $results = $this->execQuery($q);
            foreach ($results as $key => $result) {
                $edge_prop = [
                'duration' => $result['r']['duration'],
                'weight' => $result['r']['weight'],
                'noDayTime' => $result['r']['noDayTime'],
                'noNightTime' => $result['r']['noNightTime']
                ];
                $edge_info = [
                'target' => $result['m_id'],
                'color' => '',
                'label' => 'Daytime : '.$result['r']['noDayTime'].', Nighttime : '.$result['r']['noNightTime'].', Duration : '.$result['r']['duration'],
                'source' => $result['n_id'],
                'attributes' => $edge_prop,
                'id' => $result['r_id'],
                'size' => 1
                ];
                array_push($edge_list, $edge_info);

                if(!in_array($result['n']['number'], $addedNode)) {
                    array_push($addedNode, $result['n']['number']);
                    $user_stat = [
                    'Betweenness Centrality' => $result['n']['betweenness'],
                    'Modularity Class' => $result['n']['communityID'],
                    'Eccentricity' => $result['n']['eccentricity'],
                    'Closeness Centrality' => $result['n']['closeness'],
                    'Age' => $result['n']['age'],
                    'Gender' => $result['n']['gender'],
                    'Carrier' => $result['n']['carrier'],
                    'Arpu' => $result['n']['arpu'],
                    'Promotion' => $result['n']['promotion'],
                    'NoOfOutgoing' => $result['n']['outgoing'],
                    'NoOfIncoming' => $result['n']['incoming']
                    ];
                    $user_info = [
                    'label' => $result['n']['number'],
                    'x' => $key*cos(2 * $key * M_PI),
                    'y' => $key*sin(2 * $key * M_PI),
                    'id' => $result['n_id'],
                    'attributes' => $user_stat,
                    'color' => $result['n']['color'],
                    'size' => 1
                    ];
                    array_push($node_list, $user_info);
                }

                if(!in_array($result['m']['number'], $addedNode)) {
                    array_push($addedNode, $result['m']['number']);
                    $user_stat = [
                    'Betweenness Centrality' => $result['m']['betweenness'],
                    'Modularity Class' => $result['m']['communityID'],
                    'Eccentricity' => $result['m']['eccentricity'],
                    'Closeness Centrality' => $result['m']['closeness'],
                    'Age' => $result['m']['age'],
                    'Gender' => $result['m']['gender'],
                    'Carrier' => $result['m']['carrier'],
                    'Arpu' => $result['m']['arpu'],
                    'Promotion' => $result['m']['promotion'],
                    'NoOfOutgoing' => $result['m']['outgoing'],
                    'NoOfIncoming' => $result['m']['incoming']
                    ];
                    $user_info = [
                    'label' => $result['m']['number'],
                    'x' => $key*cos(2 * $key * M_PI),
                    'y' => $key*sin(2 * $key * M_PI),
                    'id' => $result['m_id'],
                    'attributes' => $user_stat,
                    'color' => $result['m']['color'],
                    'size' => 1
                    ];
                    array_push($node_list, $user_info);
                }
            }
            return ['nodes' => $node_list, 'edges' => $edge_list];
        }

        public function checkJobStatus() {
            $q = 'MATCH (n:BatchJob) RETURN ID(n) as jid, n.status as status';
            return $this->connector->sendCypherQuery($q)->getResult()->getTableFormat();
        }

        // ------------------------------------------------------------------------------------------------------------

        // --------------------------------------------- Private Functions --------------------------------------------

        private function connectDatabase($db_name, $protocol, $host_name, $port, $username, $password) {
            $this->connector = ClientBuilder::create()
            ->addConnection($db_name, $protocol, $host_name, $port, true, $username, $password)
            ->setAutoFormatResponse(true)
            ->setDefaultTimeout(20000)
            ->build();
        }

        private function prepareData($filters) {
            $startEndDate = DateHelper::getStartEndDate(substr($filters['startDate'],0,6), substr($filters['startDate'],6));
            $filters['startDate'] = [$startEndDate['startDate'], $startEndDate['endDate']];
            $filters['callDay'] = UnaryHelper::unaryToDays($filters['callDay']);
            $filters['rnCode'] = UnaryHelper::unaryToCarrier($filters['rnCode']);
            foreach ($filters as $key => $value) {
                if(is_array($value) && sizeof($value) == 2) {
                    if($value[1] == -1) {
                        $filters[$key][1] = 2000000000;
                    }   
                }
            }
            return $filters;
        }


        private function beginProcess($filters, $id, $db, $isScheduler) {
            putenv('/seniortmp');
            ignore_user_abort(true);
            $command = "java -Xmx6G -XX:+CMSClassUnloadingEnabled -jar " . ($isScheduler? "public/" : "") . "java/seniorproject/target/seniorproject-1.0-SNAPSHOT.jar ". $id . ' ' . $db . ' 1';
            foreach ($filters as $key => $value) {
                $len = sizeof($value);
                $command = $command . ' ' . $key . ' ';
                $back_command = $len . ' ';
                if(is_array($value)) {
                    $command = $command . (is_numeric($value[0])? 1 : 0) . ' ';
                    foreach ($value as $k => $val) {
                        $back_command = $back_command . $val;
                        if($k < $len - 1) {
                            $back_command = $back_command . ' ';
                        }
                    }
                } else {
                    $command = $command . (is_numeric($value[0])? 1 : 0) . ' ';
                    $back_command = $back_command . $value;
                }
                $command = $command . $back_command;
            }

            $command = $command . ' 2>&1';

            exec($command, $output);
            Log::info($command);
            Log::info($output);
            $q = '';
            if($isScheduler) {
                if (preg_match('/Exit code AA0901/',end($output))) {
                    $q = "MATCH (n:PreprocessJob) WHERE ID(n) = " . $id . " SET n.status = 'Failed'"; 
                } else {
                    $q = "MATCH (n:PreprocessJob) WHERE ID(n) = " . $id . " SET n.status = 'Ready'"; 
                }
            } else {
                if (preg_match('/Exit code AA0901/', end($output))) {
                    $q = "MATCH (n:BatchJob) WHERE ID(n) = " . $id . " SET n.status = 'Failed'";
                } else {
                    $q = "MATCH (n:BatchJob) WHERE ID(n) = " . $id . " SET n.status = 'Ready'";
                }
            }
            $this->connector->sendCypherQuery($q);
            return ;
        }



        private function deleteSource($type, $nid) {
                // Reject query whenever $this->connector has not been initialized
            $this->checkConnection();

                // Prepare Query Statement
            $q = "";

            if($type == 'preprocess') {
                $q = "MATCH (n:PreprocessSetting) WHERE ID(n) = " . $nid . " DELETE n";
            } elseif ($type == 'batch') {
                $q = "MATCH (n:BatchJob) WHERE ID(n) = " . $nid . " DELETE n";
            } else {
                return false;
            }

                // Delete Existing Source based on ID given
            $result = $this->connector->sendCypherQuery($q)->getResult()->getTableFormat();

            return true;
        }

        private function deleteLabel($type, $nid) {
                // Reject query whenever $this->connector has not been initialized
            $this->checkConnection();

                // Prepare Query Statement
            $q = "MATCH (n:Processed" . $nid . ")-[r:Call]->(m:Processed" . $nid . ") DELETE n,m,r";

                // Delete Existing Processed Data based on ID given
            $result = $this->connector->sendCypherQuery($q)->getResult()->getTableFormat();

            return true;
        }

        private function deleteCommunities($type, $nid) {
                // Reject query whenever $this->connector has not been initialized
            $this->checkConnection();

                // Prepare Query Statement
            $q = "MATCH (n:ProcessedCom" . $nid . ")-[r:Call]->(m:ProcessedCom" . $nid . ") DELETE n,m,r";

                // Delete Existing Processed Data based on ID given
            $result = $this->connector->sendCypherQuery($q)->getResult()->getTableFormat();

            return true;
        }

        private function toCurrentYearMonth($date) {
                // Get Current Month and Year
            $now = Carbon\Carbon::now();
                // $curr_year = $now->year;
                // $curr_month = $now->month;
            $curr_month = 9;
            $curr_year = 2015;
                // Replace Year Month of the given
            $date[0] = $curr_year . $this->setZeroPrefix($curr_month) . substr($date[0], 6);
            $date[1] = $curr_year . $this->setZeroPrefix($curr_month) . substr($date[1], 6);

            return $date;

        }

        private function setZeroPrefix($n) {
            return $n < 10 ? '0' . $n : $n;
        }

        private function checkConnection() {
            if(is_null($this->connector)) {
                throw new \Exception("You have not connect to Database yet.");
            }
        }

        public function lockWrite($db_name) {
            $q = 'MATCH (n:UploadLocker) RETURN n';
            if(sizeof($this->connector->sendCypherQuery($q)->getResult()->getTableFormat()) == 0) {
                $q = 'CREATE (n:UploadLocker {name: "' . $db_name . '", status : 0}) RETURN n' ;
                $this->connector->sendCypherQuery($q);
            }

            $q = 'MATCH (n:UploadLocker {status: 0}) SET n.status = 1, n.name = "' . $db_name . '" RETURN n';
            return sizeof($this->connector->sendCypherQuery($q)->getResult()->getTableFormat()) == 1;
        }

        public function unlockWrite() {
            $q = 'MATCH (n:UploadLocker) RETURN n';
            if(sizeof($this->connector->sendCypherQuery($q)->getResult()->getTableFormat()) == 0) {
                $q = 'CREATE (n:UploadLocker {name: "", status : 0}) RETURN n' ;
                $this->connector->sendCypherQuery($q);
                return true;
            }

            $q = 'MATCH (n:UploadLocker {status: 1}) SET n.status = 0 RETURN n';
            return sizeof($this->connector->sendCypherQuery($q)->getResult()->getTableFormat()) == 1;   
        }

    // ------------------------------------------------------------------------------------------------------------

}

?>