<?php 

namespace App\Http\Classes;

use Neoxygen\NeoClient\ClientBuilder;

use \App\Http\Helpers\DateHelper as DateHelper;
use \App\Http\Helpers\ExecHelper as ExecHelper;
use \App\Http\Helpers\UnaryHelper as UnaryHelper;

class Neo4JTabular {
    
    public static $priority = ['Low', 'Medium', 'High'];

    public $connector;

    public function __construct() {
        $this->connector = NULL;
    } 

    public function connectDatabase($db_name, $protocol, $host_name, $port, $username, $password) {
        $this->connector = ClientBuilder::create()
                    ->addConnection($db_name, $protocol, $host_name, $port, true, $username, $password)
                    ->setAutoFormatResponse(true)
                    ->build();
    }

    public function queryAllBatchJob() {
        // Reject query whenever $this->connector has not been initialized
        if(is_null($this->connector)) {
            throw new Exception("You have not connect to Database yet.");
        }

        // Query All Data of Batch Job
        $q = 'MATCH (n:BatchJob) RETURN n, ID(n) as jid';

        return $this->connector->sendCypherQuery($q)->getResult()->getTableFormat();
    }

    public function queryAllPreprocessJob() {
        // Reject query whenever $this->connector has not been initialized
        if(is_null($this->connector)) {
            throw new Exception("You have not connect to Database yet.");
        }

        // Query All Data of Preprocess Job
        $q = 'MATCH (n:PreprocessJob) RETURN n, ID(n) as jid';

        return $this->connector->sendCypherQuery($q)->getResult()->getTableFormat();
    }

    public function queryAllPreprocessSetting() {
        // Reject query whenever $this->connector has not been initialized
        if(is_null($this->connector)) {
            throw new Exception("You have not connect to Database yet.");
        }

        // Query All Data of Preprocess Setting
        $q = 'MATCH (n:PreprocessSetting) RETURN n, ID(n) as pid';

        return $this->connector->sendCypherQuery($q)->getResult()->getTableFormat();
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
        $result = array();
        foreach($jobs as $r) {
            
            $formatted_date = DateHelper::getStartEndDate(substr($r['n']['startDate'],0,6), substr($r['n']['startDate'],6));
            $progress = ExecHelper::calculateProgress($r['n']['startExecTime'], $r['n']['estimatedExecTime']);
            $tmp = [
                'id' => $r['jid'],
                'description' => $r['n']['description'],
                'noOfCall' => 'Not yet Support!',
                'duration' => $r['n']['durationMax'] == -1? 
                                'More than ' . $r['n']['durationMin'] : $r['n']['durationMin'] . ' - ' . $r['n']['durationMax'],
                'date' => DateHelper::formatDate($formatted_date['startDate']) . ' - ' . DateHelper::formatDate($formatted_date['endDate']),
                'customers' => number_format($r['n']['customers']),
                'size' => $r['n']['size'],
                'carrier' => UnaryHelper::unaryToCarrierReadable($r['n']['rnCode']),
                'days' => UnaryHelper::unaryToDaysReadable($r['n']['callDay']),
                'period' => $r['n']['endTime'] == -1? 
                                'After ' . number_format($r['n']['startTime'], 2, '.', '') : number_format($r['n']['startTime'], 2, '.', '') . ' - ' . number_format($r['n']['endTime'], 2, '.', ''),
                'mode' => $r['n']['mode'],
                'progress' => $progress['progress'],
                'status' => $progress['status'],
                'type' => $r['n']['type']
            ];
            array_push($result, $tmp);
        }

        return $result;
    }

    public function toReadableSetting($settings) {
        $result = array();
        foreach($settings as $r) {
            $tmp = [
                'id' => $r['pid'],
                'date' => $r['n']['startDate'],
                'priority' => Neo4JTabular::$priority[$r['n']['priority']],
                'description' => $r['n']['description'],
                'carrier' => UnaryHelper::unaryToCarrierReadable($r['n']['rnCode']),
                'days' => UnaryHelper::unaryToDaysReadable($r['n']['callDay']),
                'period' => $r['n']['endTime'] == -1? 
                                'More than ' . $r['n']['startTime'] : $r['n']['startTime'] . ' - ' . $r['n']['endTime'],
                'noOfCall' => /*$r['n']['noOfCallMax'] == -1? 
                                '> ' . $r['n']['noOfcallMin'] : $r['n']['noOfcallMin'] . ' - ' . $r['n']['noOfCallMax']*/ 1,
                'duration' => $r['n']['durationMax'] == -1? 
                                'More than ' . $r['n']['durationMin'] : $r['n']['durationMin'] . ' - ' . $r['n']['durationMax']
            ];
            array_push($result, $tmp);
        }
        return $result;
    }

}

?>