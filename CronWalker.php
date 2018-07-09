<?php

/**
 */
class CronWalker extends BaseAbstractClass
{

    private $limit = 0;
    private $start = 0;

    /**
     *
     */
    function __construct()
    {
        parent::__construct();
        $this->limit = Cfg::${get_called_class()}["select_limit"];
    }

    /**
     * @return array
     */
    protected function handleData()
    {
        $result_array = [];
        $db_provider = new DBProvider;
        $db = $db_provider->db;
        if (!is_null($db)) {
            $db->beginTransaction();
            try {
                if ($db->inTransaction()) {
                    if (Cfg::${get_called_class()}["token_dt_diff"]) {
                        $select_sql = sprintf(Cfg::${get_called_class()}["handledata_select_by_data"], Cfg::${get_called_class()}["dt_diff"], $this->start, $this->limit);
                    } else {
                        $select_sql = sprintf(Cfg::${get_called_class()}["handledata_select_all"], $this->start, $this->limit);
                    }
                    $st = $db->prepare($select_sql);
                    if (!$st->execute()) throw new Exception($st->errorInfo()[2]);
                    $data_assoc = $st->fetchAll(PDO::FETCH_ASSOC);
                    $db->commit();
                    if (count($data_assoc) > 0) {
                        array_walk($data_assoc,
                            function ($value) use (&$result_array) {
                                $result_array[$value["id"]] = $value["search"];
                            }
                        );
                        $this->start = $this->limit + 1;
                    }
                }
            } catch (Exception $e) {
//                echo $e->getMessage();
                if ($db->inTransaction()) $db->rollBack();
            }
            $db_provider->closeDb();
        }
        return $result_array;
    }

    /**
     * @param array $data
     * @return mixed
     */
    protected function saveData(array $data)
    {
        $db_provider = new DBProvider;
        $db = $db_provider->db;
        if (!is_null($db)) {
            $db->beginTransaction();
            try {
                if ($db->inTransaction()) {
                    foreach ($data as $key => $value) {
                        $st = $db->prepare(Cfg::${get_called_class()}["savedata_update_search"]);
                        if (!$st->execute([$key])) {
                            throw new Exception($st->errorInfo()[2]);
                        }
                        $db->commit();
                    }
                }
            } catch (Exception $e) {
//                echo $e->getMessage();
                if ($db->inTransaction()) $db->rollBack();
            }
            $db_provider->closeDb();
        }
    }

    /**
     *
     */
    public function Walk()
    {
        if (Cfg::${get_called_class()}["next_step_by_sockets_processing"]) {
            if (array_key_exists("REQUEST_METHOD", $_SERVER) && ($_SERVER["REQUEST_METHOD"] == 'GET')) {
                $socket = new SocketCronRequest();
                $prms = $socket->ParsedParams;
                if (is_array($prms) && count($prms)) {
                    $next_walker = new $prms[0];
                    $next_walker->Walk($prms[1]);
                }
            } else {
//                while( true ) {
                $result_array = $this->handleData();
                if (!is_null($result_array) && count($result_array) > 0) {
                    $socket = new SocketCronRequest();
                    $socket->StartSocketsRequest($result_array);
                    $socket->WaitSocketsRequestData($result_array, Cfg::${get_called_class()}["next_walker_class_name"]);
                    if (Cfg::${get_called_class()}["token_dt_diff"]) {
                       $this->saveData($result_array);
                    }
                }
//                    else {
                //                       echo('close');
//                        break;
//                    }
//                }
            }
        } else {
            while (true) {
                $result_array = $this->handleData();
                if (!is_null($result_array) && count($result_array) > 0) {
                    $next_walker = new Cfg::${get_called_class()}["next_walker_class_name"];
                    $next_walker->Walk($result_array);
                } else break;
            }
        }

    }

}

