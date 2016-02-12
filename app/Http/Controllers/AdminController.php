<?php


namespace App\Http\Controllers;
use Request;
use Neoxygen\NeoClient\ClientBuilder;
use Carbon;
use \App\Http\Helpers\DateHelper as DateHelper;
use \App\Http\Helpers\ExecHelper as ExecHelper;
use \App\Http\Helpers\UnaryHelper as UnaryHelper;

use \App\Http\Classes\Neo4JConnector as Neo4JConnector;
class AdminController extends Controller{

    public function index() {
        // Instantiate Neo4JConnector 
        $neo = new Neo4JConnector('default', 'http', 'localhost', 7474, 'neo4j', 'aiscu');
        
        // Query All Data needed
        $preprocess_settings = $neo->getReadablePreprocessSettings();
        $preprocess_jobs = $neo->getReadablePreprocessJobs();
        $batch_jobs = $neo->getReadableBatchJobs();

        return view('admin.adminpanel', [
                    'preprocess_settings' => $preprocess_settings, 
                    'preprocess_jobs' => $preprocess_jobs, 
                    'batch_jobs' => $batch_jobs
                    ]);
   }

   public function getEstimation() {
        // Get all Input send via AJAX to $rec
        $rec = Request::all();

        // Inputs divided into three Categories - Filters, Type and Mode
        $filters = $rec['filter'];
        $type = $rec['type'];
        $mode = UnaryHelper::unaryToMode($rec['mode']);

        // Instantiate Neo4JConnector
        $neo = new Neo4JConnector('default', 'http', 'localhost', 7474, 'neo4j', 'aiscu');

        // Estimate both exec time and resources used
        $estimation = $neo->estimateResource($type, $filters, $mode);

        return $estimation;
        
   }

   public function processSetup() {

        // Get all Input send via AJAX to $rec
        $rec = Request::all();

        // Inputs divided into five Categories - Filters, Type, Mode, Description and Other Informations
        $filters = $rec['filter'];
        $type = $rec['type'];
        $mode = $rec['mode'];
        $desc = $rec['description'];
        $others = $rec['others'];

        // Instantiate Neo4JConnector
        $neo = new Neo4JConnector('default', 'http', 'localhost', 7474, 'neo4j', 'aiscu');

        if($type == 'batch') {
            // Setup Batch Process, Save Batch Process Information to Neo4j and Formatting data for Displaying
            return $neo->setUpBatchProcess($filters, $mode, $desc, $others);
        } elseif ($type == 'preprocess') {
            // Save Preprocess Setting and Formatting data for Displaying
            // Note that Preprocess Settings will be retrieved when Scheduler is triggered!
            return $neo->setUpPreprocess($filters, $mode, $desc);
        } else {
            // This condition should not be reachable.
            throw new Exception("Invalid Process Setup");
        }
   }

   public function startProcess() {
        // Get all Input send via AJAX to $rec
        $rec = Request::all();

        // Inputs  - Filters
        $filters = $rec['filter'];
        $nid = $rec['nid'];

        // Instantiate Neo4JConnector 
        $neo = new Neo4JConnector('default', 'http', 'localhost', 7474, 'neo4j', 'aiscu');

        // Start Batch Processing
        $neo->startBatchProcess($filters, $nid);

        return "Success";
   }
}


?>
