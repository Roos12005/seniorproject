<?php 

namespace App\Http\Helpers;

class DateHelper {
    public static function test(){
        
        return 1;
    }

    public static function getStartEndDate($fom, $week) {
        $result = array();
        switch($week){
            case 0: 
                $result['startDate'] = $fom . '01';
                $result['endDate'] = $fom . '31';
                break;
            case 1:
                $result['startDate'] = date('Ymd', strtotime("first day of this month", $fom));
                $result['endDate'] = date('Ymd', strtotime("first Saturday of this month", $fom));
                break;
            case 2:
                $fd = date( "w", strtotime("first day of this month", $fom));
                if($fd == 0) {
                    $result['startDate'] = date('Ymd', strtotime("second Sunday of this month", $fom));
                    $result['endDate'] = date('Ymd', strtotime("second Saturday of this month", $fom));
                } else {
                    $result['startDate'] = date('Ymd', strtotime("first Sunday of this month", $fom));
                    $result['endDate'] = date('Ymd', strtotime("second Saturday of this month", $fom));
                }
                break;
            case 3:
                $fd = date( "w", strtotime("first day of this month", $fom));
                if($fd == 0) {
                    $result['startDate'] = date('Ymd', strtotime("third Sunday of this month", $fom));
                    $result['endDate'] = date('Ymd', strtotime("third Saturday of this month", $fom));
                } else {
                    $result['startDate'] = date('Ymd', strtotime("second Sunday of this month", $fom));
                    $result['endDate'] = date('Ymd', strtotime("third Saturday of this month", $fom));
                }
                break;
            case 4:
                $fd = date( "w", strtotime("first day of this month", $fom));
                if($fd == 0) {
                    $result['startDate'] = date('Ymd', strtotime("fourth Sunday of this month", $fom));
                    $result['endDate'] = date('Ymd', strtotime("fourth Saturday of this month", $fom));
                } else {
                    $result['startDate'] = date('Ymd', strtotime("third Sunday of this month", $fom));
                    $result['endDate'] = date('Ymd', strtotime("fourth Saturday of this month", $fom));
                }
                break;
            case 5:
                $fd = date( "w", strtotime("first day of this month", $fom));
                if($fd == 0) {
                    $result['startDate'] = date('Ymd', strtotime("fifth Sunday of this month", $fom));
                    $result['endDate'] = date('Ymd', strtotime("last day of this month", $fom));
                } else {
                    $result['startDate'] = date('Ymd', strtotime("fourth Sunday of this month", $fom));
                    $result['endDate'] = date('Ymd', strtotime("last day of this month", $fom));
                }
                break;
        }
        return $result;
    }

    public static function formatDate($date) {
        return substr($date,0,4) . '/' . substr($date, 4, 2) . '/' . substr($date, 6, 2);
    }

    
}

?>