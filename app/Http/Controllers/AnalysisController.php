<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use File;
use Neoxygen\NeoClient\ClientBuilder;
use Carbon;
use Log;
use \App\Http\Classes\Neo4JConnector as Neo4JConnector;
class AnalysisController extends Controller{

  public function main($id) {
    $client = ClientBuilder::create()
    ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
    ->setAutoFormatResponse(true)
    ->build();
    $q = 'Match (n:BatchJob) Where ID(n) = '.$id.' Return n';
    $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
    $week = substr($results[0]['n']['startDate'],6);
    $startDate = 'Year '.substr($results[0]['n']['startDate'],0,-3).' Month '.substr($results[0]['n']['startDate'],4,2)."  ".($week == "0"?'All month':($week == "1"?'Week 1':($week == "2"?'Week 2':($week == "3"?'Week 3':($week == "4"?'Week 4':'Week 5')))));
    $callDay =   (substr($results[0]['n']['callDay'],0,1)=="1"?"Sunday , ":"").(substr($results[0]['n']['callDay'],1,1)=="1"?"Monday , ":"").(substr($results[0]['n']['callDay'],2,1)=="1"?"Tuesday , ":"").(substr($results[0]['n']['callDay'],3,1)=="1"?"Wednesday , ":"").(substr($results[0]['n']['callDay'],4,1)=="1"?"Thursday , ":"").(substr($results[0]['n']['callDay'],5,1)=="1"?"Friday , ":"").(substr($results[0]['n']['callDay'],6,1)=="1"?"Saturday":"");
    $carrier =   (substr($results[0]['n']['rnCode'],0,1)=="1"?"AIS , ":"").(substr($results[0]['n']['rnCode'],1,1)=="1"?"TRUE , ":"").(substr($results[0]['n']['rnCode'],2,1)=="1"?"DTAC , ":"").(substr($results[0]['n']['rnCode'],3,1)=="1"?"JAS , ":"").(substr($results[0]['n']['callDay'],4,1)=="1"?"Others":"");
    $duration = $results[0]['n']['durationMin']." - ".($results[0]['n']['durationMax']=="-1"?'100':$results[0]['n']['durationMax']);
    $period = number_format($results[0]['n']['startTime'], 2, '.', '')." - ".($results[0]['n']['endTime']=="-1"?'24.00':number_format($results[0]['n']['endTime'], 2, '.', ''));
    $noOfOutgoing = $results[0]['n']['incomingMin']." - ".($results[0]['n']['incomingMax']=="-1"?'10000':$results[0]['n']['incomingMax']);
    $noOfIncoming = $results[0]['n']['outgoingMin']." - ".($results[0]['n']['outgoingMax']=="-1"?'10000':$results[0]['n']['outgoingMax']);
    return view('analysis.analysis', ['data_id' => $id])->with([
      'startDate' => $startDate,
      'callDay' => $callDay,
      'carrier' => $carrier,
      'duration' => $duration,
      'period' => $period,
      'noOfOutgoing' => $noOfOutgoing,
      'noOfIncoming' => $noOfIncoming
      ]);
  }

  //Get all CDR
  public function getCDR($id) {
    $start = Carbon\Carbon::now()->timestamp;
    putenv("TMPDIR=/seniortmp");
    set_time_limit(0);
    $client = ClientBuilder::create()
    ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
    ->setAutoFormatResponse(true)
    ->setDefaultTimeout(200000)
    ->build();
    $q = 'MATCH (n:Processed' . $id . ') RETURN n,ID(n) as n_id';
    $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
    $querytime = Carbon\Carbon::now()->timestamp;
    $node_list = array();
    $edge_list = array();
    $node_count = sizeof($results);
    foreach($results as $key => $result) {
      $user_stat = [
        'Betweenness Centrality' => $result['n']['betweenness'],
        'Modularity Class' => $result['n']['communityID'],
        'Eccentricity' => $result['n']['eccentricity'],
        'Closeness Centrality' => $result['n']['closeness'],
        'Age' => $result['n']['age'],
        'Gender' => $result['n']['gender'],
        'Carrier' => $result['n']['carrier'],
        'Arpu' => $result['n']['arpu'],
        'Promotion' => $result['n']['promotion'],
        'NoOfOutgoing' => $result['n']['outgoing'],
        'NoOfIncoming' => $result['n']['incoming']
      ];
      $user_info = [
        'label' => $result['n']['number'],
        'x' => 10*cos(2 * $key * M_PI/$node_count),
        'y' => 10*sin(2 * $key * M_PI/$node_count),
        'id' => $result['n_id'],
        'attributes' => $user_stat,
        'color' => $result['n']['color'],
        'size' => 1
      ];
      array_push($node_list, $user_info);
    }
    $q = 'MATCH (n:Processed' . $id . ')-[r:aCall]->(m) RETURN ID(n) as n_id, r, ID(r) as r_id, ID(m) as m_id';
    $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
    $node_count = sizeof($results);
    foreach($results as $key => $result) {
      $edge_prop = [
      'duration' => $result['r']['duration'],
      'weight' => $result['r']['weight'],
      'noDayTime' => $result['r']['noDayTime'],
      'noNightTime' => $result['r']['noNightTime']
      ];
      $edge_info = [
      'target' => $result['m_id'],
      'color' => '',
      'label' => 'Daytime : '.$result['r']['noDayTime'].', Nighttime : '.$result['r']['noNightTime'].', Duration : '.$result['r']['duration'],
      'source' => $result['n_id'],
      'attributes' => $edge_prop,
      'id' => $result['r_id'],
      'size' => 1
      ];
      array_push($edge_list, $edge_info);
    }
    return  response()->json(['nodes' => $node_list, 'edges' => $edge_list]);
  } 

  //Get number of Community for export data
  public function getCommunities($id) {
    putenv("TMPDIR=/seniortmp");
    $client = ClientBuilder::create()
    ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
    ->setAutoFormatResponse(true)
    ->setDefaultTimeout(20000)
    ->build();
    
    $q = 'MATCH (n:Processed' . $id . ') RETURN distinct n.communityID';
    Log::info($q);
    $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
    $communities_list = array();
    foreach($results as $key => $result) {
      $community_info = [
      'CommunityID' => $result['n.communityID']
      ];
      array_push($communities_list, $community_info);
    }
    sort($communities_list);
    return  response()->json($communities_list);
  } 

  //Get nodes in selected community for export data
  public function getNodeCommunity($id) {
    putenv("TMPDIR=/seniortmp");
    $client = ClientBuilder::create()
    ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
    ->setAutoFormatResponse(true)
    ->build();

    $communityCondition  = Input::get('exportdata');
    $profileCondition = Input::get('exportprofile');

    $query = "WHERE ";

    if(!is_null($profileCondition)){
      $queryProfileCondition = "WHERE ";
      foreach($profileCondition as $key => $value){
        $queryProfileCondition = $queryProfileCondition ." n.".$key." IN ".(string)$value." AND ";
      }
      $queryProfileCondition = substr($queryProfileCondition,0,strlen($queryProfileCondition)-5);

      $s = 'MATCH (n:ProcessedCom' . $id . ') '.(string)$queryProfileCondition.' RETURN n.communityID';
      $results = $client->sendCypherQuery($s)->getResult()->getTableFormat();
      foreach($results as $key => $result) {
        $query = $query ." n.communityID = '".$result['n.communityID']."' OR ";
      }
    }

    if(!is_null($communityCondition)) {
      foreach($communityCondition as $community) {
        $query = $query ." n.communityID = '".(string)$community."' OR ";
      }
    }

    $query = substr($query,0,strlen($query)-4);

    $q = 'MATCH (n:Processed' . $id . ') RETURN distinct n.communityID';
    $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
    $communities_num = count($results);
    $communities_list = array();
    for ($x = 0; $x < $communities_num; $x++) {
      $communities_list[$x] = array();
    }

    $r = 'MATCH (n:Processed' . $id . ') '.(string)$query.' RETURN n, n.communityID';
    $results = $client->sendCypherQuery($r)->getResult()->getTableFormat();
    foreach($results as $key => $result) {
      $user_info = [
        'label' => $result['n']['number'],
        'Betweenness Centrality' => $result['n']['betweenness'],
        'Modularity Class' => $result['n']['communityID'],
        'Eccentricity' => $result['n']['eccentricity'],
        'Closeness Centrality' => $result['n']['closeness'],
        'Age' => $result['n']['age'],
        'Gender' => $result['n']['gender'],
        'Carrier' => $result['n']['carrier'],
        'Arpu' => $result['n']['arpu'],
        'Promotion' => $result['n']['promotion'],
        'NoOfOutgoing' => $result['n']['outgoing'],
        'NoOfIncoming' => $result['n']['incoming']
      ];
      array_push($communities_list[$result['n']['communityID']], $user_info);
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
    set_time_limit(50000);
    ini_set('memory_limit', '4096M');
    putenv("TMPDIR=/seniortmp");
    $client = ClientBuilder::create()
    ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
    ->setAutoFormatResponse(true)
    ->setDefaultTimeout(20000)
    ->build();

    $q = 'MATCH (n:Processed' . $id . ') RETURN count(distinct n.communityID)';
    $community_num = $client->sendCypherQuery($q)->getResult()->get('count(distinct n.communityID)');
    
    $community_list = array();
    $edge_list = array();
    $q = 'MATCH (n:ProcessedCom' . $id . ') RETURN n, ID(n) as n_id ORDER BY n.member';
    $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
    
    foreach($results as $key => $result) {
      $community_stat = [
      'Betweenness Centrality' => $result['n']['betweenness'],
      'Modularity Class' => $result['n']['number'],
      'Eccentricity' => $result['n']['eccentricity'],
      'Closeness Centrality' => $result['n']['closeness'],
      'Member' => $result['n']['member'],
      'Member Profile' => $result['n']['memberProfile'],
      'Ais Ratio Profile' => $result['n']['aisRatioProfile'],
      'Daytime Nighttime Profile' => $result['n']['daytimeNighttimeProfile'],
      'Weekday Weekend Profile' => $result['n']['weekdayWeekendProfile'],
      'Call Other Carrier Profile' => $result['n']['callOtherCarrierProfile'],
      'Average No Of Call Profile' => $result['n']['averageNoOfCallProfile'],
      'Average Arpu Profile' => $result['n']['averageArpuProfile'],
      'Average Duration Profile' => $result['n']['averageDurationProfile'],
      ];

      $community_info = [
      'label' => "Community".$result['n']['number'],
      'x' => 5 * cos(2 * $key * M_PI/$community_num),
      'y' => 5 * sin(2 * $key * M_PI/$community_num),
      'id' => $result['n_id'],
      'attributes' => $community_stat,
      'color' => $result['n']['color'],
      'size' => 1
      ];
      array_push($community_list, $community_info);  
    }

    $q = 'MATCH (n:ProcessedCom' . $id . ')-[r:aCall]->(m) RETURN ID(n) as n_id, r, ID(r) as r_id, ID(m) as m_id';
    $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
    
    $node_count = sizeof($results);
    foreach($results as $key => $result) {
      $edge_prop = [
      'duration' => $result['r']['duration'],
      'weight' => $result['r']['weight'],
      'noDayTime' => $result['r']['noDayTime'],
      'noNightTime' => $result['r']['noNightTime']
      ];
      $edge_info = [
      'target' => $result['m_id'],
      'color' => '',
      'label' => 'Daytime : '.$result['r']['noDayTime'].', Nighttime : '.$result['r']['noNightTime'].', Duration : '.$result['r']['duration'],
      'source' => $result['n_id'],
      'attributes' => $edge_prop,
      'id' => $result['r_id'],
      'size' => 1
      ];
      array_push($edge_list, $edge_info);
    }
    return response()->json(['nodes' => $community_list, 'edges' => $edge_list]); 
  }

  //Get numbers of nodes in each carrier
  public function getCarrier($id){
    set_time_limit(50000);
    putenv("TMPDIR=/seniortmp");
    $client = ClientBuilder::create()
    ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
    ->setAutoFormatResponse(true)
    ->setDefaultTimeout(20000)
    ->build();
    $q = 'MATCH (n:Processed' . $id . ') RETURN count(n)';
    $all_num = $client->sendCypherQuery($q)->getResult()->get('count(n)');

    $q = 'MATCH (n:Processed' . $id . ') -[r:Call]-> (m:Processed' . $id . ') RETURN count(r)';
    $all_call = $client->sendCypherQuery($q)->getResult()->get('count(r)');

    $q = 'MATCH (n:Processed' . $id . ') Where n.carrier In ["AIS","3GPre-paid","3GPost-paid","3GHybrid-Post","GSM","AWN"] RETURN count(n)';
    $ais_num = $client->sendCypherQuery($q)->getResult()->get('count(n)');

    $q = 'MATCH (n:Processed' . $id . ') Where n.carrier In ["TRUE","RFT","CATCDA"] RETURN count(n)';
    $true_num = $client->sendCypherQuery($q)->getResult()->get('count(n)');

    $q = 'MATCH (n:Processed' . $id . ') Where n.carrier In ["DTAC","DTN"] RETURN count(n)';
    $dtac_num = $client->sendCypherQuery($q)->getResult()->get('count(n)');

    $q = 'MATCH (n:Processed' . $id . ') Where n.carrier In ["TOT","TOT3G"] RETURN count(n)';
    $tot_num = $client->sendCypherQuery($q)->getResult()->get('count(n)');

    return response()->json(['all' => $all_num,'ais' => $ais_num,'true' => $true_num,'dtac' => $dtac_num,'tot' => $tot_num,'calls' => $all_call]);
  }

  //Get nodes in selected community for double click listener
  public function getNodeInSelectedCommunity($id){
    set_time_limit(50000);
    putenv("TMPDIR=/seniortmp");
    $client = ClientBuilder::create()
    ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
    ->setAutoFormatResponse(true)
    ->setDefaultTimeout(20000)
    ->build();

    $selectedCommunity  = Input::get('senddata');
    $node_list = array();
    $edge_list = array();

    $q = 'MATCH (n:Processed' . $id . ') WHERE n.communityID = "'.$selectedCommunity.'" RETURN count(n)';
    $community_num = $client->sendCypherQuery($q)->getResult()->get('count(n)');

    $r = 'MATCH (n:Processed' . $id . ') WHERE n.communityID = "'.$selectedCommunity.'" RETURN n, ID(n) as n_id';
    $results = $client->sendCypherQuery($r)->getResult()->getTableFormat();
    foreach($results as $key => $result) {
      $user_stat = [
      'Betweenness Centrality' => $result['n']['betweenness'],
      'Modularity Class' => $result['n']['communityID'],
      'Eccentricity' => $result['n']['eccentricity'],
      'Closeness Centrality' => $result['n']['closeness'],
      'Age' => $result['n']['age'],
      'Gender' => $result['n']['gender'],
      'Carrier' => $result['n']['carrier'],
      'Arpu' => $result['n']['arpu'],
      'Promotion' => $result['n']['promotion'],
      'NoOfOutgoing' => $result['n']['outgoing'],
      'NoOfIncoming' => $result['n']['incoming']
      ];
      $user_info = [
      'label' => $result['n']['number'],
      'x' => 5*$key*cos(2 * $key * M_PI / $community_num),
      'y' => 5*$key*sin(2 * $key * M_PI / $community_num),
      'id' => $result['n_id'],
      'attributes' => $user_stat,
      'color' => $result['n']['color'],
      'size' => 1
      ];
      array_push($node_list, $user_info);
    }

    $q = 'MATCH (n:Processed' . $id . ')-[r:aCall]->(m:Processed' . $id . ') WHERE n.communityID = "'.$selectedCommunity.'" AND m.communityID = "'.$selectedCommunity.'" RETURN ID(n) as n_id, r, ID(r) as r_id, ID(m) as m_id';
    $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
    foreach ($results as $result) {
      $edge_prop = [
        'duration' => $result['r']['duration'],
        'weight' => $result['r']['weight'],
        'noDayTime' => $result['r']['noDayTime'],
        'noNightTime' => $result['r']['noNightTime']
      ];
        $edge_info = [
        'target' => $result['m_id'],
        'color' => '',
        'label' => 'Daytime : '.$result['r']['noDayTime'].', Nighttime : '.$result['r']['noNightTime'].', Duration : '.$result['r']['duration'],
        'source' => $result['n_id'],
        'attributes' => $edge_prop,
        'id' => $result['r_id'],
        'size' => 1
      ];
      array_push($edge_list, $edge_info);
    } 

    return response()->json(["nodes" => $node_list,"edges" => $edge_list]);
  } 
  public function getNodeCommunityProfile($id){
    putenv("TMPDIR=/seniortmp");
    $client = ClientBuilder::create()
    ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
    ->setAutoFormatResponse(true)
    ->build();

    $selectedProfile  = Input::get('sendprofile');

    $query = "WHERE ";
    foreach($selectedProfile as $key => $value){
      $query = $query ." n.".$key." IN ".(string)$value." AND ";
    }
    $query = substr($query,0,strlen($query)-5);

    $q = 'MATCH (n:ProcessedCom' . $id . ') '.(string)$query.' RETURN n.communityID';
    $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();
    $communities_list = array();
    foreach($results as $key => $result) {
      array_push($communities_list, $result['n.communityID']);
    }
    return  response()->json($communities_list);
  }

  public function getNeighbors($id) {
    putenv("TMPDIR=/seniortmp");
    $neo = new Neo4JConnector('default', 'http', 'localhost', 7474, 'neo4j', 'aiscu');
    $selectedNode  = Input::get('node');

    return response()->json($neo->getNeighbors($id, $selectedNode));
  }

  public function findCommunity($id) {
    $neo = new Neo4JConnector('default', 'http', 'localhost', 7474, 'neo4j', 'aiscu');
    $selectedNode  = Input::get('number');
    $communityID = $neo->findCommunity($id, $selectedNode);
    return response()->json(["communityID" => $communityID]);
  }
}
?>