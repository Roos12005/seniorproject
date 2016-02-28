<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\App;
use App\Models\Call_Detail_Records;
use App\Models\Users;
use Illuminate\Http\Request;
use File;
use Neoxygen\NeoClient\ClientBuilder;
use Plupload;
use Log;

use \App\Http\Classes\Neo4JConnector as Neo4JConnector;
use \App\Http\Classes\Neo4JValidator as Neo4JValidator;

class DatabaseController extends Controller{
    
    public function index() {
        
        return view('admin.database');
    }

    public function uploadCDR() {
        // $neo = new Neo4JConnector('default', 'http', 'localhost', 7474, 'neo4j', 'aiscu');
        // $validator = new Neo4JValidator($neo->getConnector());
        
        // while($av = $validator->isWriteLocked()) {
        //     if(!$av) {
        //         sleep(rand(1,10));
        //         continue;
        //     }

        //     try {
        //         $isGranted = $neo->grantLock();
        //         if($isGranted) {
        //             // TODO : Trigger Java Importer
        //             $isReleased = $neo->releaseLock();
        //             if(!$isReleased) {
        //                 throw new Exception("Database can't be unlocked. Manually unlocking is needed.");
        //             }
        //         } else {
        //             continue;
        //         }
        //         break;
        //     } catch (Exception $e) {
        //         throw new Exception($e);
        //     } finally {
        //         $isReleased = $neo->releaseLock();
        //         if(!$isReleased) {
        //             throw new Exception("Database can't be unlocked. Manually unlocking is needed.");
        //         }
        //     }    
        // }
        

        return "success";
    }

    public function uploadProfile() {
        
    }
    
}
?>