<?php

/**
 */
class CronStepTwo extends CronSteps
{
    use tStepTwo, tCronSteps;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->crwlr = new Crawler();
        $this->parser = new ParserStepTwo();
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function prepareDataFromNet($data){
        return $data;
    }

    /**
     * @param $search_assoc
     * @return array
     */
    protected function prepareDataFromDB($search_assoc)
    {
        $result_array = [];
        if (count($search_assoc) > 0) {
            array_walk($search_assoc,
                function ($value) use (&$result_array) {
                    if (is_string($value[Cfg::${get_called_class()}["fields"][0]])
                        && is_string($value[Cfg::${get_called_class()}["fields"][1]])
                        && is_string($value[Cfg::${get_called_class()}["fields"][2]])
                    ) {
                        $result_array[$value[Cfg::${get_called_class()}["fields"][0]]] = [
                            strip_tags($value[Cfg::${get_called_class()}["fields"][1]]) =>
                                strip_tags($value[Cfg::${get_called_class()}["fields"][2]])
                        ];
                    }
                }
            );
        }
        return $result_array;
    }

    /**
     * @param $net_data
     * @param $in_db_data
     * @param $search_id
     * @param $search_val
     * @return array
     */
    protected function processingDataDB($net_data, $in_db_data, $search_id, $search_val)
    {
        $result_array = [];
        if (is_array($net_data) && count($net_data) > 0) {
            $db_data_prepare_compare = [];
            $db_data_prepare_delete = [];
            array_walk($in_db_data,
                function ($value, $key) use (&$db_data_prepare_compare, &$db_data_prepare_delete) {
                    foreach ($value as $k => $v) {
                        $db_data_prepare_compare[$k] = $v;
                        $db_data_prepare_delete[$k] = $key;
                    }
                }
            );
            $db_data = $db_data_prepare_compare;
            $this->db_provider = new DBProvider;
            $this->db = $this->db_provider->db;
            if (!is_null($this->db)) {
                try {
                    $insert_data = array_diff_assoc($net_data, $db_data);
                    $delete_data = array_diff_assoc($db_data, $net_data);
                    if (is_array($delete_data) && count($delete_data) > 0) {
                        $this->db->beginTransaction();
                        try {
                            if ($this->db->inTransaction()) {
                                $st = Cfg::${get_called_class()}["handledata_delete"];
                                foreach ($delete_data as $key => $value) {
                                    if (!$this->db->exec(sprintf($st, $db_data_prepare_delete[$key])))
                                        throw new Exception($st->errorInfo()[2]);
                                }
                            }
                            $this->db->commit();
                        } catch (Exception $e) {
//                            echo $e->getMessage();
                            if ($this->db->inTransaction()) $this->db->rollBack();
                        }
                    }
                    if (is_array($insert_data) && count($insert_data) > 0) {
                        $this->db->beginTransaction();
                        try {
                            if ($this->db->inTransaction()) {
                                $st = $this->db->prepare(Cfg::${get_called_class()}["handledata_insert"]);
                                $prms_execute = [];
                                foreach ($insert_data as $key=>$value) {
                                    $prms_execute[0] = $key;
                                    $prms_execute[1] = $value;
                                    $prms_execute[2] = $search_id;
                                    if (!$st->execute($prms_execute))
                                        throw new Exception($st->errorInfo()[2]);
                                }
                            }
                            $this->db->commit();
                        } catch (Exception $e) {
//                            echo $e->getMessage();
                            if ($this->db->inTransaction()) $this->db->rollBack();
                        }
                    }
                } catch (Exception $e) {
//                    echo $e->getMessage();
                }
                $this->db_provider->closeDb();
                if ((is_array($net_data) && count($net_data) > 0) ||
                    (is_array($in_db_data) && count($in_db_data) > 0)
                ) {
                    $db_data = $this->getDataFromDB();
                    $db_data = $this->prepareDataFromDB($db_data);
                } else $db_data = $in_db_data;
                $result_array = $db_data;
            }
            return $result_array;
        }
    }

}