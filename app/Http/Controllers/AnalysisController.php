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

class AnalysisController extends Controller{

    public function getIndex() {
        exec("java -jar java/seniorproject/target/seniorproject-1.0-SNAPSHOT.jar 0 1000 0.00 23.59", $output);
       return view('analysis.analysis');
   }

    public function processData(Request $request) {
        $recieve = $request->all();
        
        $command = "java -jar java/seniorproject/target/seniorproject-1.0-SNAPSHOT.jar 0";
        foreach ($recieve as $key => $value) {
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

        exec($command);
        // exec('java -jar java/seniorproject/target/seniorproject-1.0-SNAPSHOT.jar');

        return $command;
    }

    //Get all CDR
    public function getCDR() {
        $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
            ->setAutoFormatResponse(true)
            ->build();
        

        $q = 'MATCH (n:User) RETURN n, ID(n) as n_id';
        $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
        $node_list = array();
        $node_count = sizeof($results);
        foreach($results as $key => $result) {
            $user_stat = [
                'Betweenness Centrality' => $result['n']['Betweenness'],
                'Modularity Class' => $result['n']['CommunityID'],
                'Eccentricity' => $result['n']['Eccentricity'],
                'Closeness Centrality' => $result['n']['Closeness'],
                'Age' => $result['n']['Age'],
                'Gender' => $result['n']['Gender'],
                'RnCode' => $result['n']['RnCode'],
                'Promotion' => $result['n']['Promotion']
            ];
            $user_info = [
              'label' => $result['n']['Number'],
              'x' => 10*cos(2 * $key * M_PI/$node_count),
              'y' => 10*sin(2 * $key * M_PI/$node_count),
              'id' => $result['n_id'],
              'attributes' => $user_stat,
              'color' => $result['n']['Color'],
              'size' => 1
            ];
            array_push($node_list, $user_info);
        }


        $q = 'MATCH (n:User)-[r:Call]->(m:User) RETURN ID(n) as n_id, r, ID(r) as r_id, ID(m) as m_id';
        $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
        $edge_list = array();
        foreach ($results as $result) {
            $edge_prop = [
                'duration' => $result['r']['Duration'],
                'startDate' => $result['r']['StartDate'],
                'startTime' => $result['r']['StartTime'],
                'callDay' => $result['r']['CallDay']
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

        return  response()->json(['nodes' => $node_list, 'edges' => $edge_list]);
    } 

    // TODO : This function will be removed when my experiment is done!
  public function test() {
       exec("java -jar java/seniorproject/target/seniorproject-1.0-SNAPSHOT.jar", $output);
       return var_dump($output);
   }

   public function testgraph() {
    return view('experiment.testgraph');
    }
}
?>