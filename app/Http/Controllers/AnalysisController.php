<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use File;
use Neoxygen\NeoClient\ClientBuilder;

class AnalysisController extends Controller{

    public function getIndex() {
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


    public function getCommunities() {
        $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
            ->setAutoFormatResponse(true)
            ->build();
        
        $q = 'MATCH (n:User) RETURN distinct n.CommunityID';
        $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
        $communities_list = array();
        foreach($results as $key => $result) {
            $community_info = [
              'CommunityID' => $result['n.CommunityID']
            ];

            array_push($communities_list, $community_info);
        }
        sort($communities_list);
        return  response()->json($communities_list);
    } 

    public function getNodeCommunity() {
        $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
            ->setAutoFormatResponse(true)
            ->build();

        $selectedCommunity  = Input::get('senddata');

        $query = "WHERE ";

        foreach($selectedCommunity as $community) {
            $query = $query ." n.CommunityID = ".(string)$community." OR ";
        }

        $query = substr($query,0,strlen($query)-4);

        // $q = 'MATCH (n:User) RETURN distinct n.CommunityID';
        // $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
        // $communities_num = count($results);

        $communities_list = array();
        // for ($x = 0; $x < $communities_num; $x++) {
        //   $communities_list[$x] = array();
        // }

        $r = 'MATCH (n:User) '.(string)$query.' RETURN n, n.CommunityID';
        $results = $client->sendCypherQuery($r)->getResult()->getTableFormat();
        foreach($results as $key => $result) {
            $user_info = [
              'label' => $result['n']['Number'],
              'Betweenness Centrality' => $result['n']['Betweenness'],
              'Modularity Class' => $result['n']['CommunityID'],
              'Eccentricity' => $result['n']['Eccentricity'],
              'Closeness Centrality' => $result['n']['Closeness'],
              'Age' => $result['n']['Age'],
              'Gender' => $result['n']['Gender'],
              'RnCode' => $result['n']['RnCode'],
              'Promotion' => $result['n']['Promotion']
            ];

            //array_push($communities_list[$result['n']['CommunityID']], $user_info);
          array_push($communities_list, $user_info);
        }

        // for ($x = 0; $x < count($communities_list); $x++) {
        //   usort($communities_list[$x], function($a,$b){
        //     if ($a['Closeness Centrality']==$b['Closeness Centrality']) return 0;
        //     return ($a['Closeness Centrality']>$b['Closeness Centrality'])?-1:1;
        //   });
        // }

        usort($communities_list, function($a,$b){
          if ($a['Closeness Centrality']==$b['Closeness Centrality']) return 0;
          return ($a['Closeness Centrality']>$b['Closeness Centrality'])?-1:1;
        });

        return response()->json($communities_list);
    } 

  public function getCommunityOfCommunity() {
    $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
            ->setAutoFormatResponse(true)
            ->build();

    $q = 'MATCH (n:User) RETURN count(distinct n.CommunityID)';
    $community_num = $client->sendCypherQuery($q)->getResult()->get('count(distinct n.CommunityID)');

    $community_list = array();

    for($community = 0; $community < $community_num; $community++) {
      $q = 'MATCH (n:User{CommunityID:'.$community.'}) RETURN count(n),n.Color';
      $result = $client->sendCypherQuery($q)->getResult()->getTableFormat();
      
      $community_stat = [
        'Member' => $result[0]['count(n)'],
        'Modularity Class' => $community
      ];

      $community_info = [
        'id' => $community,
        'label' => "Community: ".$community,
        'attributes' => $community_stat,
        'x' => 10*cos(2 * $community * M_PI/$community_num),
        'y' => 10*sin(2 * $community * M_PI/$community_num),
        'color' => $result[0]['n.Color'],
        'size' => 1
      ];
      array_push($community_list, $community_info);
    }

    $edge_list = array();

    $q = 'Match(n:User)-[r:Call]->(m:User) Return n.CommunityID,m.CommunityID';
    $edges = $client->sendCypherQuery($q)->getResult()->getTableFormat();
    $edge_id = 5421;
    foreach ($edges as $edge) {
      $edge_info = [
        'id' => $edge_id,
        'source' => $edge['n.CommunityID'],
        'target' => $edge['m.CommunityID'],
        'color' => '',
        'size' => 1
      ];
      array_push($edge_list, $edge_info);
      $edge_id++;
    }

    return response()->json(['nodes' => $community_list, 'edges' => $edge_list]); 
  }

  public function getCarrier(){
    $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
            ->setAutoFormatResponse(true)
            ->build();
    $q = 'MATCH (n:User) RETURN count(n)';
    $all_num = $client->sendCypherQuery($q)->getResult()->get('count(n)');

    $q = 'MATCH (n:User{RnCode:"AIS"}) RETURN count(n)';
    $ais_num = $client->sendCypherQuery($q)->getResult()->get('count(n)');

    $q = 'MATCH (n:User{RnCode:"TRUE"}) RETURN count(n)';
    $true_num = $client->sendCypherQuery($q)->getResult()->get('count(n)');

    $q = 'MATCH (n:User{RnCode:"DTAC"}) RETURN count(n)';
    $dtac_num = $client->sendCypherQuery($q)->getResult()->get('count(n)');

    return response()->json(['all' => $all_num,'ais' => $ais_num,'true' => $true_num,'dtac' => $dtac_num]);
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