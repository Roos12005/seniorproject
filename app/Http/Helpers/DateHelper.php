<?php 

namespace App\Http\Helpers;
use DateTime;
use Exception;

class DateHelper {
    public static function test(){
        
        return 1;
    }

    public static function formatDateWithDelim($date, $delim) {
        return substr($date,0,4) . $delim . substr($date, 4, 2) . $delim . substr($date, 6, 2);
    }

    public static function getStartEndDate($fom, $week) {
        $result = array();
        switch($week) {
            case 1:
                $fom = $fom . '01';
                break;
            case 2:
                $fom = $fom . '08';
                break;
            case 3:
                $fom = $fom . '15';
                break;
            case 4:
                $fom = $fom . '22';
                break;
            case 5:
                $fom = $fom . '29';
                break;
            case 0:
                $result['startDate'] = substr($fom, 0, 4) . substr($fom, 4,2) . '01';
                $result['endDate'] = str_replace('-', '', date("Y-m-t", strtotime($result['startDate'])));
                return $result;
        }

        $date = new DateTime(DateHelper::formatDate($fom));
        $week = $date->format("W");
        $year = substr($fom, 0, 4);
        $tmp = DateHelper::getStartAndEndDate($week, $year, substr($fom, 4, 2));
        $result['startDate'] = str_replace('-', '', $tmp[0]);
        $result['endDate'] = str_replace('-', '', $tmp[1]);
        
        // switch($week){
        //     case 0: 
        //         $result['startDate'] = $fom . '01';
        //         $result['endDate'] = $fom . '31';
        //         break;
        //     case 1:
        //         $result['startDate'] = date('Ymd', strtotime("first day of this month", $fom));
        //         $result['endDate'] = date('Ymd', strtotime("first Saturday of this month", $fom));
        //         break;
        //     case 2:
        //         $fd = date( "w", strtotime("first day of this month", $fom));
        //         if($fd == 0) {
        //             $result['startDate'] = date('Ymd', strtotime("second Sunday of this month", $fom));
        //             $result['endDate'] = date('Ymd', strtotime("second Saturday of this month", $fom));
        //         } else {
        //             $result['startDate'] = date('Ymd', strtotime("first Sunday of this month", $fom));
        //             $result['endDate'] = date('Ymd', strtotime("second Saturday of this month", $fom));
        //         }
        //         break;
        //     case 3:
        //         $fd = date( "w", strtotime("first day of this month", $fom));
        //         if($fd == 0) {
        //             $result['startDate'] = date('Ymd', strtotime("third Sunday of this month", $fom));
        //             $result['endDate'] = date('Ymd', strtotime("third Saturday of this month", $fom));
        //         } else {
        //             $result['startDate'] = date('Ymd', strtotime("second Sunday of this month", $fom));
        //             $result['endDate'] = date('Ymd', strtotime("third Saturday of this month", $fom));
        //         }
        //         break;
        //     case 4:
        //         $fd = date( "w", strtotime("first day of this month", $fom));
        //         if($fd == 0) {
        //             $result['startDate'] = date('Ymd', strtotime("fourth Sunday of this month", $fom));
        //             $result['endDate'] = date('Ymd', strtotime("fourth Saturday of this month", $fom));
        //         } else {
        //             $result['startDate'] = date('Ymd', strtotime("third Sunday of this month", $fom));
        //             $result['endDate'] = date('Ymd', strtotime("fourth Saturday of this month", $fom));
        //         }
        //         break;
        //     case 5:
        //         $fd = date( "w", strtotime("first day of this month", $fom));
        //         if($fd == 0) {
        //             $result['startDate'] = date('Ymd', strtotime("fifth Sunday of this month", $fom));
        //             $result['endDate'] = date('Ymd', strtotime("last day of this month", $fom));
        //         } else {
        //             $result['startDate'] = date('Ymd', strtotime("fourth Sunday of this month", $fom));
        //             $result['endDate'] = date('Ymd', strtotime("last day of this month", $fom));
        //         }
        //         break;
        // }
        return $result;
    }

    public static function formatDate($date) {
        return substr($date,0,4) . '/' . substr($date, 4, 2) . '/' . substr($date, 6, 2);
        // return str_replace('-', '/', $date);
    }

    public static function getStartAndEndDate($week, $year, $month){
      $dto = new DateTime();
      $dto->setISODate($year, $week);
      $ret[0] = $dto->format('Y-m-d');
      $dto->modify('+6 days');
      $ret[1] = $dto->format('Y-m-d');

      if(substr($ret[0],5,2) != $month) {
        $ret[0] = $year . '-' .$month . '01';
      }

      if(substr($ret[1],5,2) != $month) {
        $ret[1] = $dto->modify('-6 days')->format('Y-m-t');
      }
      return $ret;
    }

    
}

?>