<?php


namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Neoxygen\NeoClient\ClientBuilder;

class AdminController extends Controller{

    public function index() {
        $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
            ->setAutoFormatResponse(true)
            ->build();
        

        $q = 'MATCH (n:table) RETURN n';
        $results_table = $client->sendCypherQuery($q)->getResult()->getTableFormat();

        $q = 'MATCH (n:preprocess) RETURN n';
        $results_preprocess = $client->sendCypherQuery($q)->getResult()->getTableFormat();
        
       return view('admin.adminpanel', ['table' => $results_table, 'preprocess' => $results_preprocess]);
   }
}
?>
