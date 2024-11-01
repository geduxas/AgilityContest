<?php

/**
 * Created by PhpStorm.
 * User: jantonio
 * Date: 16/11/16
 * Time: 10:58
 */
class Open_RSCE_3 extends Competitions {
    function __construct() {
        parent::__construct("Prueba Open (3 alturas)");
        $this->federationID=0;
        $this->competitionID=8;
        $this->moduleVersion="1.0.0";
        $this->moduleRevision="20170523_2045";
        $this->federationLogoAllowed=false; // RSCE rules: only allowed in authorized events
    }

    function getRoundHeights($mangaid) { return 3; }

    function useLongNames() { return true; }
}