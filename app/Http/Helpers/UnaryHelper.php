<?php 

namespace App\Http\Helpers;

class UnaryHelper {
    public static function unaryToDays($u) {
        $arrChar = str_split($u);
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $result = array();
        foreach($arrChar as $key => $c) {
            if($c == "1") {
                array_push($result, $days[$key]);
            }
        }
        return $result;
    }

    public static function unaryToDaysRegex($u) {
        $arrChar = str_split($u);
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $result = "";
        foreach($arrChar as $key => $c) {
            if($c == "1") {
                $result = $result . '|' . $days[$key];
            }
        }
        return substr($result,1);
    }

    public static function unaryToDaysReadable($u) {
        $arrChar = str_split($u);
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $result = "";
        foreach($arrChar as $key => $c) {
            if($c == "1") {
                $result = $result . ', ' . $days[$key];
            }
        }
        return substr($result,2);
    }

    public static function unaryToCarrier($u) {
        $arrChar = str_split($u);
        $days = ['AIS', 'TRUE', 'DTAC', 'JAS', 'Others'];
        $result = array();
        foreach($arrChar as $key => $c) {
            if($c == "1") {
                array_push($result, $days[$key]);
            }
        }
        return $result;
    }

    public static function unaryToCarrierRegex($u) {
        $arrChar = str_split($u);
        $days = ['AIS', 'TRUE', 'DTAC', 'JAS', 'Others'];
        $result = "";
        foreach($arrChar as $key => $c) {
            if($c == "1") {
                $result = $result . '|' . $days[$key];
            }
        }
        return substr($result,1);
    }

    public static function unaryToCarrierReadable($u) {
        $arrChar = str_split($u);
        $days = ['AIS', 'TRUE', 'DTAC', 'JAS', 'Others'];
        $result = "";
        foreach($arrChar as $key => $c) {
            if($c == "1") {
                $result = $result . ', ' . $days[$key];
            }
        }
        return substr($result,2);
    }

    public static function unaryToMode($u) {
        $arrChar = str_split($u);
        $result = array();
        $result['centrality'] = $arrChar[0];
        $result['community'] = $arrChar[1];
        $result['cust_profiling'] = $arrChar[2];
        $result['com_profiling'] = $arrChar[3];
        return $result;
    }

    public static function arrToRegex($arr) {
        $result = '';
        foreach($arr as $key => $c) {
            $result = $result . '|' . $c;
        }   
        return substr($result,1);
    }

    public static function arrToReadable($arr) {
        $result = '';
        foreach($arr as $key => $c) {
            $result = $result . ', ' . $c;
        }   
        return substr($result,2);
    }

    public static function arrToUnary($arr) {
        $result = '';
        foreach($arr as $key => $c) {
            $result = $result . $c;
        }   
        return $result;
    }

    public static function rangeToReadable($arr, $t) {
        if($t == 'time') {
            if($arr[1] == 2000000000) {
                return 'After ' . number_format($arr[0], 2, '.', '');
            } else {
                return number_format($arr[0], 2, '.', '') . ' - ' . number_format($arr[1], 2, '.', '');
            }
        } else {
            if($arr[1] == 2000000000) {
                return 'More than ' . $arr[0];
            } else {
                return $arr[0] . ' - ' . $arr[1];
            }
        }
    }

    
}

?>