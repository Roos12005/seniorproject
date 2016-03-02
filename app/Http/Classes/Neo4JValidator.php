<?php 

namespace App\Http\Classes;

use Neoxygen\NeoClient\ClientBuilder;
use Carbon;
use Log;

class Neo4JValidator {

    // ------------------------------------------------ Class Attributes -----------------------------------------
    
    public $connector = NULL;

    // -----------------------------------------------------------------------------------------------------------


    // ------------------------------------------------ Constructor ----------------------------------------------

    public function __construct($connector) {
        $this->connector = $connector;
    } 

    // ------------------------------------------------------------------------------------------------------------

    public function foo() {
        $q = 'MATCH (n:UploadLocker {status: 1}) SET n.status = 0 RETURN n';
        return $this->connector->sendCypherQuery($q)->getResult()->getTableFormat();
    }

    public function isWriteLocked() {
        // 0 - unlock
        // 1 - locked
        $q = 'MATCH (n:UploadLocker {status: 1}) RETURN n';
        $result = $this->connector->sendCypherQuery($q)->getResult()->getTableFormat();
        return !empty($result);
    }

    public function isLabelDuplicated() {

    }

    public function isLabelNameValid($name) {

    }

}

?>