<?php

/**
 */
abstract class CronSteps extends BaseAbstractClass
{
    use tSteps;

    /**
     * @param array $data
     * @return mixed
     */
    abstract protected function handleData(array $data);

    /**
     * @param array $data
     */
    public function Walk(array $data)
    {
        $result_array = $this->handleData($data);
        if (!is_null($result_array) && count($result_array) > 0) {
            if (array_key_exists("next_walker_class_name", Cfg::${get_called_class()}) &&
                class_exists(Cfg::${get_called_class()}["next_walker_class_name"])
            ) {
                if (Cfg::${get_called_class()}["next_step_by_sockets_processing"]){
                    $socket = new SocketCronRequest();
//
//                    $result_array = [8974=>"75.126.64.0/20"];
//
                    $socket->StartSocketsRequest($result_array);
                    $socket->WaitSocketsRequestData($result_array, Cfg::${get_called_class()}["next_walker_class_name"]);
                    echo 'exit';
                } else {
                    $next_walker = new Cfg::${get_called_class()}["next_walker_class_name"];
                    $next_walker->Walk($result_array);
                }
            }
        }
    }

}