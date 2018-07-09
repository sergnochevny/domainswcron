<?php
/**
 */
require_once('Autoload.php');

if ($prms = Threads::getParams()){
    if (is_array($prms) && count($prms)>0){
        $data = $prms[1];
        $class = $prms[0];
        $cron_step = new $class;
        $cron_step->Walk($data);

    }
}
exit;