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

  public function main($id) {
      $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
            ->setAutoFormatResponse(true)
            ->build();
      $q = 'Match (n:BatchJob) Where ID(n) = '.$id.' Return n';
      $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
      $week = substr($results[0]['n']['startDate'],6);
      $database = '-';
      //$startDate = substr($results[0]['n']['startDate'],0,-1).($week == "0"||"1"?'01':($week == "2"?'08':($week == "3"?'15':($week == "4"?'22':'29')))).' - '.substr($results[0]['n']['startDate'],0,-1).($week == "0"?'30':($week == "1"?'07':($week == "2"?'14':($week == "3"?'21':($week == "4"?'28':'30')))));
      $startDate = 'Year '.substr($results[0]['n']['startDate'],0,-3).' Month '.substr($results[0]['n']['startDate'],4,2)."  ".($week == "0"?'All month':($week == "1"?'Week 1':($week == "2"?'Week 2':($week == "3"?'Week 3':($week == "4"?'Week 4':'Week 5')))));
      $callDay =   (substr($results[0]['n']['callDay'],0,1)=="1"?"Sunday , ":"").(substr($results[0]['n']['callDay'],1,1)=="1"?"Monday , ":"").(substr($results[0]['n']['callDay'],2,1)=="1"?"Tuesday , ":"").(substr($results[0]['n']['callDay'],3,1)=="1"?"Wednesday , ":"").(substr($results[0]['n']['callDay'],4,1)=="1"?"Thursday , ":"").(substr($results[0]['n']['callDay'],5,1)=="1"?"Friday , ":"").(substr($results[0]['n']['callDay'],6,1)=="1"?"Saturday":"");
      $carrier =   (substr($results[0]['n']['rnCode'],0,1)=="1"?"AIS , ":"").(substr($results[0]['n']['rnCode'],1,1)=="1"?"TRUE , ":"").(substr($results[0]['n']['rnCode'],2,1)=="1"?"DTAC , ":"").(substr($results[0]['n']['rnCode'],3,1)=="1"?"JAS , ":"").(substr($results[0]['n']['callDay'],4,1)=="1"?"Others":"");
      $mode =   (substr($results[0]['n']['mode'],0,1)=="1"?"Centrality , ":"").(substr($results[0]['n']['mode'],1,1)=="1"?"Customer Profiling , ":"").(substr($results[0]['n']['mode'],2,1)=="1"?"Community , ":"").(substr($results[0]['n']['mode'],3,1)=="1"?"Community Profile ":"");
      $duration = $results[0]['n']['durationMin']." - ".($results[0]['n']['durationMax']=="-1"?'100':$results[0]['n']['durationMax']);
      $period = number_format($results[0]['n']['startTime'], 2, '.', '')." - ".($results[0]['n']['endTime']=="-1"?'24.00':number_format($results[0]['n']['endTime'], 2, '.', ''));
      $noOfOutgoing = '-';
      $noOfIncoming = '-';
      return view('analysis.analysis', [
                      'data_id' => $id
                    ])->with([
                      'database' => $database,
                      'startDate' => $startDate,
                      'callDay' => $callDay,
                      'carrier' => $carrier,
                      'calculation' => $mode,
                      'duration' => $duration,
                      'period' => $period,
                      'noOfOutgoing' => $noOfOutgoing,
                      'noOfIncoming' => $noOfIncoming
                    ]);
  }

  //Get all CDR
  public function getCDR($id) {
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
                'Promotion' => $result['n']['Promotion'],
                'NoOfOutgoing' => $result['n']['NoOfOutgoing'],
                'NoOfIncoming' => $result['n']['NoOfIncoming']
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

  //Get number of Community for export data
  public function getCommunities($id) {
        $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
            ->setAutoFormatResponse(true)
            ->build();
        
        $q = 'MATCH (n:Processed' . $id . ') RETURN distinct n.CommunityID';
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

  //Get nodes in selected community for export data
  public function getNodeCommunity($id) {
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

        $q = 'MATCH (n:Processed' . $id . ') RETURN distinct n.CommunityID';
        $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
        $communities_num = count($results);

        $communities_list = array();
        for ($x = 0; $x < $communities_num; $x++) {
          $communities_list[$x] = array();
        }

        $r = 'MATCH (n:Processed' . $id . ') '.(string)$query.' RETURN n, n.CommunityID';
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
              'NoOfOutgoing' => $result['n']['NoOfOutgoing'],
              'NoOfIncoming' => $result['n']['NoOfIncoming']
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

  //Get nodes community of community
  public function getCommunityOfCommunity($id) {
    $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
            ->setAutoFormatResponse(true)
            ->build();

    $q = 'MATCH (n:Processed' . $id . ') RETURN count(distinct n.CommunityID)';
    $community_num = $client->sendCypherQuery($q)->getResult()->get('count(distinct n.CommunityID)');

    $community_list = array();

    $q = 'MATCH (n:ProcessedCom' . $id . ') RETURN n, ID(n) as n_id';
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
        $q = 'MATCH (n:ProcessedCom' . $id . ')-[r:Call]->(m:ProcessedCom' . $id . ') RETURN distinct n.CommunityID as n_num, m.CommunityID as m_num';
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
          $q = "MATCH (n:ProcessedCom" . $id . ")-[r:Call]->(m:ProcessedCom" . $id . ") WHERE n.CommunityID = ".$call['source']." AND m.CommunityID = ".$call['target']." RETURN ID(n) as n_id, ID(m) as m_id,collect(r) as collect_r";
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
    return response()->json(['nodes' => $community_list, 'edges' => $edge_list]); 
  }

  //Get numbers of nodes in each carrier
  public function getCarrier($id){
    $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
            ->setAutoFormatResponse(true)
            ->build();
    $q = 'MATCH (n:Processed' . $id . ') RETURN count(n)';
    $all_num = $client->sendCypherQuery($q)->getResult()->get('count(n)');

    $q = 'MATCH (n:Processed' . $id . ') -[r:Call]-> (m:User) RETURN count(r)';
    $all_call = $client->sendCypherQuery($q)->getResult()->get('count(r)');

    $q = 'MATCH (n:Processed' . $id . '{RnCode:"AIS"}) RETURN count(n)';
    $ais_num = $client->sendCypherQuery($q)->getResult()->get('count(n)');

    $q = 'MATCH (n:Processed' . $id . '{RnCode:"TRUE"}) RETURN count(n)';
    $true_num = $client->sendCypherQuery($q)->getResult()->get('count(n)');

    $q = 'MATCH (n:Processed' . $id . '{RnCode:"DTAC"}) RETURN count(n)';
    $dtac_num = $client->sendCypherQuery($q)->getResult()->get('count(n)');

    return response()->json(['all' => $all_num,'ais' => $ais_num,'true' => $true_num,'dtac' => $dtac_num,'calls' => $all_call]);
  }

  //Get nodes in selected community for double click listener
  public function getNodeInSelectedCommunity($id){
    $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
            ->setAutoFormatResponse(true)
            ->build();

        $selectedCommunity  = Input::get('senddata');
        $communities_list = array();
        $edge_list = array();

        $q = 'MATCH (n:Processed' . $id . ') WHERE n.CommunityID = '.$selectedCommunity.' RETURN count(n)';
        $community_num = $client->sendCypherQuery($q)->getResult()->get('count(n)');

        $r = 'MATCH (n:Processed' . $id . ') WHERE n.CommunityID = '.$selectedCommunity.' RETURN n, ID(n) as n_id';
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
                'NoOfOutgoing' => $result['n']['NoOfOutgoing'],
                'NoOfIncoming' => $result['n']['NoOfIncoming']
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

        $call_list = array();
        $q = 'MATCH (n:Processed' . $id . ')-[r:Call]->(m:Processed' . $id . ') WHERE n.CommunityID = '.$selectedCommunity.' AND m.CommunityID = '.$selectedCommunity.' RETURN distinct n.Number as n_num, m.Number as m_num';
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
          $q = "MATCH (n:Processed" . $id . ")-[r:Call]->(m:Processed" . $id . ") WHERE n.CommunityID = ".$selectedCommunity." AND m.CommunityID = ".$selectedCommunity." AND n.Number = '".$call['source']."' AND m.Number = '".$call['target']."' RETURN ID(n) as n_id, ID(m) as m_id,collect(r) as collect_r";
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

        return response()->json(["nodes" => $communities_list,"edges" => $edge_list]);
    } 
}
?>