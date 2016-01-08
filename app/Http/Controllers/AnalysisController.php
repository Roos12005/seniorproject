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
        exec("java -jar java/seniorproject/target/seniorproject-1.0-SNAPSHOT.jar", $output);
       return view('analysis.analysis');
   }
   public function test() {
       // $cdr = DB::table('call_detail_records')->where('b_no','2')->pluck('mobile_no');
       exec("java -jar java/seniorproject/target/seniorproject-1.0-SNAPSHOT.jar", $output);
       return var_dump($output);
   }

    //Get all CDR
    public function getCDR() {
        $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
            ->setAutoFormatResponse(true)
            ->build();
        

        $q = 'MATCH (n:User) RETURN n, ID(n) as n_id';
        $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
        $cdr_list = array();
        foreach($results as $result) {
            $user_stat = [
                'Betweenness Centrality' => $result['n']['Betweenness'],
                'Modularity Class' => $result['n']['CommunityID'],
                'Eccentricity' => $result['n']['Eccentricity'],
                'Closeness Centrality' => $result['n']['Closeness']
            ];
            $user_info = [
              'label' => $result['n']['Number'],
              'x' => rand(0, 10),
              'y' => rand(0, 10),
              'id' => $result['n_id'],
              'attributes' => $user_stat,
              'color' => $result['n']['Color'],
              'size' => 1
            ];
            array_push($cdr_list, $user_info);
        }


        $q = 'MATCH (n:User)-[r:Call]->(m:User) RETURN ID(n) as n_id, r, ID(r) as r_id, ID(m) as m_id';
        $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
        $edge_list = array();
        foreach ($results as $result) {
            $edge_prop = [
                'duration' => $result['r']['Duration']
            ];
            $edge_info = [
              'target' => $result['m_id'],
              'color' => '',
              'label' => '',
              'source' => $result['n_id'],
              'attributes' => $edge_prop,
              'id' => $result['r_id'],
              'size' => 1
            ];
            array_push($edge_list, $edge_info);
        }

        return  response()->json(['nodes' => $cdr_list, 'edges' => $edge_list]);
    } 

    // TODO : This function will be removed when my experiment is done!
   public function testgraph() {
    return view('experiment.testgraph');
    }
}
?>