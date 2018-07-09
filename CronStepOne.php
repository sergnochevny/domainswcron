<?php

/**
 */
class CronStepOne extends CronSteps
{
    use tStepOne, tCronSteps;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->crwlr = new Crawler();
        $this->parser = new ParserStepOne();
    }

    protected function prepareDataFromNet($data)
    {
        $search = md5($this->find);
        return array_filter($data,
            function ($value) use ($search) {
                return md5($value) === $search;
            }
        );
    }


    protected function prepareDataFromDB($search_assoc)
    {
        $result_array = [];
        if (count($search_assoc) > 0) {
            array_walk($search_assoc,
                function ($value) use (&$result_array) {
                    if (is_string($value[Cfg::${get_called_class()}["fields"][0]])
                        && is_string($value[Cfg::${get_called_class()}["fields"][1]])
                    ) {
                        $result_array[$value[Cfg::${get_called_class()}["fields"][0]]] =
                            strip_tags($value[Cfg::${get_called_class()}["fields"][1]]);
                    }
                }
            );
        }
        return $result_array;
    }

    protected function processingDataDB($net_data, $db_data, $search_id, $search_val)
    {
        $result_array = [];
        if (is_array($net_data) && count($net_data) > 0) {
            $net_data = array_keys($net_data);
            $this->db_provider = new DBProvider();
            $this->db = $this->db_provider->db;
            if (!is_null($this->db)) {
                try {
                    $insert_data = array_diff($net_data, $db_data);
                    $delete_data = array_diff($db_data, $net_data);
                    if (is_array($delete_data) && count($delete_data) > 0) {
                        $this->db->beginTransaction();
                        try {
                            if ($this->db->inTransaction()) {
                                $st = Cfg::${get_called_class()}["handledata_delete"];
                                foreach ($delete_data as $key => $value) {
                                    if (!$this->db->exec(sprintf($st, $key)))
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
                                foreach ($insert_data as $value) {
                                    if (!$st->execute([strip_tags($value), $search_id]))
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
                    (is_array($db_data) && count($db_data) > 0)
                ) {
                    $db_data = $this->getDataFromDB();
                    $db_data = $this->prepareDataFromDB($db_data);
                }
                array_walk($db_data,
                    function ($value, $key) use (&$result_array) {
                        $result_array[$key] = $value;
                    }
                );
            }
            return $result_array;
        }
    }

}