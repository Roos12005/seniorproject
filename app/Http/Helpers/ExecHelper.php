<?php 

namespace App\Http\Helpers;

use Neoxygen\NeoClient\ClientBuilder;

class ExecHelper {
    public static function beginProcess($filters){
        $command = "java -jar java/seniorproject/target/seniorproject-1.0-SNAPSHOT.jar 0";
        foreach ($filters as $key => $value) {
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
        return ;
    }

    public static function prepareData($filters) {
        $filters['callDay'] = \App\Http\Helpers\UnaryHelper::unaryToDays($filters['callDay']);
        $filters['rnCode'] = \App\Http\Helpers\UnaryHelper::unaryToCarrier($filters['rnCode']);
        foreach ($filters as $key => $value) {
            if(is_array($value) && sizeof($value) == 2) {
                if($value[1] == -1) {
                    $filters[$key][1] = 2000000000;
                }   
            }
        }
        return $filters;
    } 


    public static function estimateResource($filters) {
        $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'aiscu')
            ->setAutoFormatResponse(true)
            ->build();

        $q = 'MATCH (n:Node)-[r:Call]->(m:Node) WHERE';

        $q = $q . ' r.startDate >= ' . $filters['startDate'][0] . ' AND r.startDate <= ' . $filters['startDate'][1] . ' AND';
        $q = $q . ' r.startTime >= ' . $filters['startTime'][0] . ' AND r.startTime <= ' . $filters['startTime'][1] . ' AND';
        $q = $q . ' r.duration >= ' . $filters['duration'][0] . ' AND r.duration <= ' . $filters['duration'][1] . ' AND';
        $q = $q . ' n.rnCode =~ "' . \App\Http\Helpers\UnaryHelper::arrToRegex($filters['rnCode']) . '"' . ' AND';
        $q = $q . ' m.rnCode =~ "' . \App\Http\Helpers\UnaryHelper::arrToRegex($filters['rnCode']) . '"' . ' AND';
        $q = $q . ' r.callDay =~ "' . \App\Http\Helpers\UnaryHelper::arrToRegex($filters['callDay']) . '"';

        $q = $q . ' RETURN count(r) as edges, count(DISTINCT m) as nodes';
        $results = $client->sendCypherQuery($q)->getResult()->getTableFormat();

        return $results[0];
    }

    
}

?>