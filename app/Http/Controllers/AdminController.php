<?php


namespace App\Http\Controllers;
use Request;
use Neoxygen\NeoClient\ClientBuilder;
use Carbon;
use \App\Http\Helpers\DateHelper as DateHelper;
use \App\Http\Helpers\ExecHelper as ExecHelper;
use \App\Http\Helpers\UnaryHelper as UnaryHelper;
use Log;

use \App\Http\Classes\Neo4JConnector as Neo4JConnector;
class AdminController extends Controller{

    public function index() {
        set_time_limit(50000);
        // Instantiate Neo4JConnector 
        $neo = new Neo4JConnector('default', 'http', 'localhost', 7474, 'neo4j', 'aiscu');
        
        // Query All Data needed
        $database = $neo->getAvailableDatabase();
        $preprocess_settings = $neo->getReadablePreprocessSettings();
        $preprocess_jobs = $neo->getReadablePreprocessJobs();
        $batch_jobs = $neo->getReadableBatchJobs();
        
        return view('admin.adminpanel', [
                    'database' => $database,
                    'preprocess_settings' => $preprocess_settings, 
                    'preprocess_jobs' => $preprocess_jobs, 
                    'batch_jobs' => $batch_jobs
                    ]);
   }

   public function processSetup() {
        set_time_limit(50000);
        // Get all Input send via AJAX to $rec
        $rec = Request::all();

        // Inputs divided into four Categories - Filters, Type, Description and Other Informations
        $filters = $rec['filter'];
        $type = $rec['type'];
        $desc = $rec['description'];
        $db = $rec['database'];


        // Instantiate Neo4JConnector
        $neo = new Neo4JConnector('default', 'http', 'localhost', 7474, 'neo4j', 'aiscu');

        if($type == 'batch') {
            // Setup Batch Process, Save Batch Process Information to Neo4j and Formatting data for Displaying
            return $neo->setUpBatchProcess($filters, $desc, $db);
        } elseif ($type == 'preprocess') {
            // Save Preprocess Setting and Formatting data for Displaying
            // Note that Preprocess Settings will be retrieved when Scheduler is triggered!
            return $neo->setUpPreprocess($filters, $desc, $db);
        } else {
            // This condition should not be reachable.
            throw new \Exception("Invalid Process Setup");
        }
   }

   public function startProcess() {
        set_time_limit(50000);
        // Get all Input send via AJAX to $rec
        $rec = Request::all();

        // Inputs  - Filters
        $filters = $rec['filter'];
        $nid = $rec['nid'];
        $database = $rec['database'];
        // Instantiate Neo4JConnector 
        $neo = new Neo4JConnector('default', 'http', 'localhost', 7474, 'neo4j', 'aiscu');

        // Start Batch Processing
        $neo->startBatchProcess($filters, $nid, $database);

        return "Success";
   }

   public function deleteData() {
        set_time_limit(50000);
        // Get all Input send via AJAX to $rec
        $rec = Request::all();

        // Inputs  - Filters
        $type = $rec['type'];
        $nid = $rec['nid'];

        // Instantiate Neo4JConnector 
        $neo = new Neo4JConnector('default', 'http', 'localhost', 7474, 'neo4j', 'aiscu');

        // Start Batch Processing
        $neo->deleteData($type, $nid);

        return "Success";

   }

   public function exportCSV() {
        set_time_limit(50000);
        // Get all Input send via AJAX to $rec
        $rec = Request::all();
        $pid = $rec['pid'];

        // Instantiate Neo4JConnector 
        $neo = new Neo4JConnector('default', 'http', 'localhost', 7474, 'neo4j', 'aiscu');

        // Prepare data to export
        $results = $neo->queryNodesForCSV($pid);

        return response()->json($results);
   }

   public function checkJobStatus() {
        set_time_limit(50000);
        $neo = new Neo4JConnector('default', 'http', 'localhost', 7474, 'neo4j', 'aiscu');
        $results = $neo->checkJobStatus();
        return response()->json($results);
   }
}


?>
