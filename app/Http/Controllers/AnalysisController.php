<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use File;
use Neoxygen\NeoClient\ClientBuilder;
use Carbon;
class AnalysisController extends Controller{

    public function getIndex() {
       return view('analysis.analysis');
   }


   //    putenv("TMPDIR=/tmp");
   //    $tmp = sys_get_temp_dir();
   //    echo $tmp;
   //    exit();
   //      exec("java -jar java/seniorproject/target/seniorproject-1.0-SNAPSHOT.jar 0 1000 0.00 23.59", $output);
   //     return view('analysis.analysis');
   // }

   // public function runmaven() {
       
   // }

   public function main($id) {
      return view('analysis.analysis', [
                    'data_id' => $id
                    ]);
   }

    public function processData(Request $request) {
        $recieve = $request->all();
        putenv("TMPDIR=/tmp");
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
    public function getCDR($id) {
    //public function getCDR($id) {

        $start = Carbon\Carbon::now()->timestamp;
        putenv("TMPDIR=/tmp");
        set_time_limit(0);
        $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
            ->setAutoFormatResponse(true)
            ->build();
        

        
        $q = 'MATCH (n:Processed' . $id . ') RETURN n,ID(n) as n_id';
        $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
        $querytime = Carbon\Carbon::now()->timestamp;
        $node_list = array();
        $edge_list = array();
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
                // 'NoOfCall' => $result['n']['NoOfCall'],
                // 'NoOfReceive' => $result['n']['NoOfReceive']
            ];
            $user_info = [
              'label' => $result['n']['Number'],
              'x' => 10*cos(2 * $key * M_PI/$node_count),
              'y' => 10*sin(2 * $key * M_PI/$node_count),
              'id' => $result['n_id'],
              'attributes' => [],
              'color' => $result['n']['Color'],
              'size' => 1
            ];
            array_push($node_list, $user_info);
        }

        $call_list = array();
        $q = 'MATCH (n:Processed' . $id . ')-[r:Call]->(m:Processed' . $id . ') RETURN distinct n.Number as n_num, m.Number as m_num';
        $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
        foreach($results as $result){
          $call_info = [
            'source' => $result['n_num'],
            'target' => $result['m_num']
          ];
          array_push($call_list, $call_info);
        }

        $edge_id = 9945;
        $edge_list = array();
        foreach($call_list as $call){
          $q = "MATCH (n:Processed" . $id . ")-[r:Call]->(m:Processed" . $id . ") WHERE n.Number = '".$call['source']."' AND m.Number = '".$call['target']."' RETURN ID(n) as n_id, ID(m) as m_id,collect(r) as collect_r";
          $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
          $duration = 0;
          $weight = 0;
          $noDayTime = 0;
          $noNightTime = 0;
          foreach ($results[0]['collect_r'] as $value) {
            $duration += $value['Duration'];
            $weight += 1;
            if($value['StartTime'] >= 5 && $value['StartTime'] <= 17){
              $noDayTime += 1;
            } else {
              $noNightTime += 1;
            }
          }
          $edge_prop = [
            'duration' => $duration,
            'weight' => $weight,
            'noDayTime' => $noDayTime,
            'noNightTime' => $noNightTime
          ];
          $edge_info = [
            'target' => $results[0]['m_id'],
            'color' => '',
            'label' => '',
            'source' => $results[0]['n_id'],
            'attributes' => $edge_prop,
            'id' => $edge_id,
            'size' => 1
          ];
          $edge_id += 1;
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

        $q = 'MATCH (n:User) RETURN distinct n.CommunityID';
        $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
        $communities_num = count($results);

        $communities_list = array();
        for ($x = 0; $x < $communities_num; $x++) {
          $communities_list[$x] = array();
        }

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
              'Promotion' => $result['n']['Promotion'],
              'NoOfCall' => $result['n']['NoOfCall'],
              'NoOfReceive' => $result['n']['NoOfReceive']
            ];

          array_push($communities_list[$result['n']['CommunityID']], $user_info);
        }

        for ($x = 0; $x < count($communities_list); $x++) {
          usort($communities_list[$x], function($a,$b){
            if ($a['Closeness Centrality']==$b['Closeness Centrality']) return 0;
            return ($a['Closeness Centrality']>$b['Closeness Centrality'])?-1:1;
          });
        }

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

    $q = 'MATCH (n:User_Com) RETURN n, ID(n) as n_id';
        $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
        $node_list = array();
        $node_count = sizeof($results);
        foreach($results as $key => $result) {
            $community_stat = [
                'Betweenness Centrality' => $result['n']['Betweenness'],
                'Modularity Class' => $result['n']['CommunityID'],
                'Eccentricity' => $result['n']['Eccentricity'],
                'Closeness Centrality' => $result['n']['Closeness'],
                'Member' => $result['n']['Member']
            ];
            $community_info = [
              'label' => "Community".$result['n']['CommunityID'],
              'x' => 10*cos(2 * M_PI/$community_num),
              'y' => 10*sin(2 * M_PI/$community_num),
              'id' => $result['n_id'],
              'attributes' => $community_stat,
              'color' => $result['n']['Color'],
              'size' => 1
            ];
            array_push($community_list, $community_info);
        }

        $call_list = array();
        $q = 'MATCH (n:User_Com)-[r:Call]->(m:User_Com) RETURN distinct n.CommunityID as n_num, m.CommunityID as m_num';
        $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
        foreach($results as $result){
          $call_info = [
            'source' => $result['n_num'],
            'target' => $result['m_num']
          ];
          array_push($call_list, $call_info);
        }

        $edge_id = 9945;
        $edge_list = array();
        foreach($call_list as $call){
          $q = "MATCH (n:User_Com)-[r:Call]->(m:User_Com) WHERE n.CommunityID = ".$call['source']." AND m.CommunityID = ".$call['target']." RETURN ID(n) as n_id, ID(m) as m_id,collect(r) as collect_r";
          $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
          $duration = 0;
          $weight = 0;
          $noDayTime = 0;
          $noNightTime = 0;
          foreach ($results[0]['collect_r'] as $value) {
            $duration += $value['Duration'];
            $weight += 1;
            if($value['StartTime'] >= 5 && $value['StartTime'] <= 17){
              $noDayTime += 1;
            } else {
              $noNightTime += 1;
            }
          }
          $edge_prop = [
            'duration' => $duration,
            'weight' => $weight,
            'noDayTime' => $noDayTime,
            'noNightTime' => $noNightTime
          ];
          $edge_info = [
            'target' => $results[0]['m_id'],
            'color' => '',
            'label' => '',
            'source' => $results[0]['n_id'],
            'attributes' => $edge_prop,
            'id' => $edge_id,
            'size' => 1
          ];
          $edge_id += 1;
          array_push($edge_list, $edge_info);
         }

    // $edge_list = array();
    // $q = 'MATCH (n:User_Com)-[r:Call]->(m:User_Com) RETURN ID(n) as n_id, r, ID(r) as r_id, ID(m) as m_id';
    //     $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
    //     $edge_list = array();
    //     foreach ($results as $result) {
    //         $edge_prop = [
    //             'duration' => $result['r']['Duration'],
    //             'startDate' => $result['r']['StartDate'],
    //             'startTime' => $result['r']['StartTime'],
    //             'callDay' => $result['r']['CallDay']
    //         ];
    //         $edge_info = [
    //           'target' => $result['m_id'],
    //           'color' => '',
    //           'label' => '',
    //           'source' => $result['n_id'],
    //           'attributes' => $edge_prop,
    //           'id' => $result['r_id'],
    //           'size' => 1
    //         ];
    //         array_push($edge_list, $edge_info);
    //     }
    return response()->json(['nodes' => $community_list, 'edges' => $edge_list]); 
  }

  public function getCarrier(){
    $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
            ->setAutoFormatResponse(true)
            ->build();
    $q = 'MATCH (n:User) RETURN count(n)';
    $all_num = $client->sendCypherQuery($q)->getResult()->get('count(n)');

    $q = 'MATCH (n:User) -[r:Call]-> (m:User) RETURN count(r)';
    $all_call = $client->sendCypherQuery($q)->getResult()->get('count(r)');

    $q = 'MATCH (n:User{RnCode:"AIS"}) RETURN count(n)';
    $ais_num = $client->sendCypherQuery($q)->getResult()->get('count(n)');

    $q = 'MATCH (n:User{RnCode:"TRUE"}) RETURN count(n)';
    $true_num = $client->sendCypherQuery($q)->getResult()->get('count(n)');

    $q = 'MATCH (n:User{RnCode:"DTAC"}) RETURN count(n)';
    $dtac_num = $client->sendCypherQuery($q)->getResult()->get('count(n)');

    return response()->json(['all' => $all_num,'ais' => $ais_num,'true' => $true_num,'dtac' => $dtac_num,'calls' => $all_call]);
  }

  public function getNodeInSelectedCommunity(){
    $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
            ->setAutoFormatResponse(true)
            ->build();

        $selectedCommunity  = Input::get('senddata');
        $communities_list = array();
        $edge_list = array();

        $q = 'MATCH (n:User) WHERE n.CommunityID = '.$selectedCommunity.' RETURN count(n)';
        $community_num = $client->sendCypherQuery($q)->getResult()->get('count(n)');

        $r = 'MATCH (n:User) WHERE n.CommunityID = '.$selectedCommunity.' RETURN n, ID(n) as n_id';
        $results = $client->sendCypherQuery($r)->getResult()->getTableFormat();
        foreach($results as $key => $result) {
            $user_stat = [
                'Betweenness Centrality' => $result['n']['Betweenness'],
                'Modularity Class' => $result['n']['CommunityID'],
                'Eccentricity' => $result['n']['Eccentricity'],
                'Closeness Centrality' => $result['n']['Closeness'],
                'Age' => $result['n']['Age'],
                'Gender' => $result['n']['Gender'],
                'RnCode' => $result['n']['RnCode'],
                'Promotion' => $result['n']['Promotion'],
                'NoOfCall' => $result['n']['NoOfCall'],
                'NoOfReceive' => $result['n']['NoOfReceive']
            ];
            $user_info = [
              'label' => $result['n']['Number'],
              'x' => 10*cos(2 * $key * M_PI / $community_num),
              'y' => 10*sin(2 * $key * M_PI / $community_num),
              'id' => $result['n_id'],
              'attributes' => $user_stat,
              'color' => $result['n']['Color'],
              'size' => 1
            ];
          array_push($communities_list, $user_info);
        }
        $end = Carbon\Carbon::now()->timestamp;
        $t1 = $querytime - $start;
        $t2 = $end - $querytime;
        $time = [
          '1' => $t1,
          '2' => $t2
        ];

        $call_list = array();
        $q = 'MATCH (n:User)-[r:Call]->(m:User) WHERE n.CommunityID = '.$selectedCommunity.' AND m.CommunityID = '.$selectedCommunity.' RETURN distinct n.Number as n_num, m.Number as m_num';
        $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
        foreach($results as $result){
          $call_info = [
            'source' => $result['n_num'],
            'target' => $result['m_num']
          ];
          array_push($call_list, $call_info);
        }

        $edge_id = 9945;
        $edge_list = array();
        foreach($call_list as $call){
          $q = "MATCH (n:User)-[r:Call]->(m:User) WHERE n.CommunityID = ".$selectedCommunity." AND m.CommunityID = ".$selectedCommunity." AND n.Number = '".$call['source']."' AND m.Number = '".$call['target']."' RETURN ID(n) as n_id, ID(m) as m_id,collect(r) as collect_r";
          $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
          $duration = 0;
          $weight = 0;
          $noDayTime = 0;
          $noNightTime = 0;
          foreach ($results[0]['collect_r'] as $value) {
            $duration += $value['Duration'];
            $weight += 1;
            if($value['StartTime'] >= 5 && $value['StartTime'] <= 17){
              $noDayTime += 1;
            } else {
              $noNightTime += 1;
            }
          }
          $edge_prop = [
            'duration' => $duration,
            'weight' => $weight,
            'noDayTime' => $noDayTime,
            'noNightTime' => $noNightTime
          ];
          $edge_info = [
            'target' => $results[0]['m_id'],
            'color' => '',
            'label' => '',
            'source' => $results[0]['n_id'],
            'attributes' => $edge_prop,
            'id' => $edge_id,
            'size' => 1
          ];
          $edge_id += 1;
          array_push($edge_list, $edge_info);
         }

        // $q = 'MATCH (n:User)-[r:Call]->(m:User) WHERE n.CommunityID = '.$selectedCommunity.' AND m.CommunityID = '.$selectedCommunity.' RETURN ID(n) as n_id, r, ID(r) as r_id, ID(m) as m_id';
        // $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
        // $edge_list = array();
        // foreach ($results as $result) {
        //     $edge_prop = [
        //         'duration' => $result['r']['Duration'],
        //         'startDate' => $result['r']['StartDate'],
        //         'startTime' => $result['r']['StartTime'],
        //         'callDay' => $result['r']['CallDay']
        //     ];
        //     $edge_info = [
        //       'target' => $result['m_id'],
        //       'color' => '',
        //       'label' => '',
        //       'source' => $result['n_id'],
        //       'attributes' => $edge_prop,
        //       'id' => $result['r_id'],
        //       'size' => 1
        //     ];
        //     array_push($edge_list, $edge_info);
        // }        

        return response()->json(["nodes" => $communities_list,"edges" => $edge_list]);
        //return  response()->json(['nodes' => $node_list, 'edges' => $edge_list, 'time' => $time]);
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