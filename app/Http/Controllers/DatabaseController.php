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

use \App\Http\Classes\Neo4JConnector as Neo4JConnector;
use \App\Http\Classes\Neo4JValidator as Neo4JValidator;

class DatabaseController extends Controller{
    
    public function index() {
        $neo = new Neo4JConnector('default', 'http', 'localhost', 7474, 'neo4j', 'aiscu');
        
        // Query All Data needed
        $database = $neo->getAvailableDatabase();

        return view('admin.database',['database' => $database]);
    }

    public function uploadCDR() {
        return Plupload::receive('file', function ($file){
            $filename = str_replace(" ","_",$file->getClientOriginalName()) . '_cdr';
            $file->move(storage_path() . '/tmp_db_store/', $filename);

            return 'ready';
        });
    }

    public function uploadProfile() {
        Plupload::receive('file', function ($file){
            $filename = str_replace(" ","_",$file->getClientOriginalName()) . '_profile';
            $file->move(storage_path() . '/tmp_db_store/', $filename);
            return 'ready';
        });
    }

    public function writeToDatabase() {
        ignore_user_abort(true);

        $db_name = Request::all()['name'];
        $db_name = str_replace(" ","_",$db_name);
        $neo = new Neo4JConnector('default', 'http', 'localhost', 7474, 'neo4j', 'aiscu');
        $validator = new Neo4JValidator($neo->getConnector());
        while($av = $validator->isWriteLocked()) {
            if(!$av) {
                sleep(rand(1,10));
                continue;
            }
        }
        try {
            $isGranted = $neo->grantLock($db_name);
            if($isGranted) {
                $command = "java -Xmx6G -XX:+CMSClassUnloadingEnabled -jar java/data-importer/target/data-importer-1.0-SNAPSHOT.jar " . $db_name . ' 2>&1';
                $output = shell_exec($command);
                Log::info($command);
                Log::info($output);
                if (preg_match('/Exception/',$output)) {
                    throw new \Exception("Error occured while writing input data to database. Please check logging file.");
                }
                $neo->execQuery('CREATE (n:Database {name: "' . $db_name . '"})');
            } else {
                throw new \Exception("Locking database is denied.");
            }
        } catch (Exception $e) {
            throw new \Exception($e);
        } finally {
            $isReleased = $neo->releaseLock();

            $filename1 = $db_name . '_cdr';
            $filename2 = $db_name . '_profile';

            unlink(storage_path() . '/tmp_db_store/' . $filename1);
            unlink(storage_path() . '/tmp_db_store/' . $filename2);
            if(!$isReleased) {
                throw new \Exception("Database can't be unlocked. Manually unlocking is needed.");
            }
        }    
        return 'success';
    }

    public function deleteDatabase() {
        $neo = new Neo4JConnector('default', 'http', 'localhost', 7474, 'neo4j', 'aiscu');
        $db_id = Request::all()['db_id'];
        $database = $neo->deleteDatabase($db_id);
        return "success";
    }

    public function renameDatabase() {
        $neo = new Neo4JConnector('default', 'http', 'localhost', 7474, 'neo4j', 'aiscu');
        $rec = Request::all();
        $db_id = $rec['db_id'];
        $db_name = $rec['new_name'];
        $database = $neo->renameDatabase($db_id, $db_name);
        return "success";
    }
    
}
?>