<?php 

namespace App\Http\Helpers;

use Neoxygen\NeoClient\ClientBuilder;
use Carbon;

class ExecHelper {
    public static function beginProcess($filters, $id){
        $command = "java -jar java/seniorproject/target/seniorproject-1.0-SNAPSHOT.jar ". $id;
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
}

?>