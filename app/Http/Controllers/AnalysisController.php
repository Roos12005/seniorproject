<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\App;
use App\Models\Call_Detail_Records;
use App\Models\Users;
use File;
use Neoxygen\NeoClient\ClientBuilder;

class AnalysisController extends Controller{

    public function getIndex() {
       return view('analysis.analysis');
   }
   public function test() {
       // $cdr = DB::table('call_detail_records')->where('b_no','2')->pluck('mobile_no');
       $name = Users::all()->pluck('number');
       return $name;
   }

    //Get all CDR
    public function getCDR() {
        $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
            ->setAutoFormatResponse(true)
            ->build();
        $q = 'MATCH (n:User)-[r:Call]->(m:User) RETURN n, r, m';
        $result = $client->sendCypherQuery($q)->getResult();
        print_r($result->getTableFormat());


        $users = Users::all();
        $cdr_list = array();
        foreach($users as $user) {
          $user_stat = [
            'Betweenness Centrality' => $user->Betweenness,
            'Modularity Class' => $user->CommunityID,
            'Eccentricity' => $user->Eccentricity,
            'Closeness Centrality' => $user->Closeness
          ];
            $user_info = [
              'label' => $user->Number,
              'x' => 0,
              'y' => 0,
              'id' => $user->id,
              'sttributes' => $user_stat,
              'color' => $user->Color,
              'size' => 0
            ];
            array_push($cdr_list, $user_info);
        }

        return  response()->json(['node' => $cdr_list]);
    } 

    // TODO : This function will be removed when my experiment is done!
   public function testgraph() {
    return view('experiment.testgraph');
    }
}
?>