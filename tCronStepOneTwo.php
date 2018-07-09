<?php

/**
 */
trait tCronStepOneTwo
{

    /**
     * @return array
     */
    protected function getDataFromDB()
    {
        $result_array = [];
        $this->db_provider = new DBProvider;
        $this->db = $this->db_provider->db;
        if (!is_null($this->db)) {
            $this->db->beginTransaction();
            try {
                if ($this->db->inTransaction()) {
                    $search = $this->find;
                    $st = $this->db->prepare(Cfg::${get_called_class()}["getdata_select_all"]);
                    if ($st->execute([$search])) {
                        $search_assoc = $st->fetchAll(PDO::FETCH_ASSOC);
                        $result_array = $this->prepareDataFromDB($search_assoc);
                    } else {
                        throw new Exception($st->errorInfo()[2]);
                    }
                    $this->db->commit();
                }
            } catch (Exception $e) {
                echo $e->getMessage();
                if ($this->db->inTransaction()) $this->db->rollBack();
            }
            $this->db_provider->closeDb();
        }
        return $result_array;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function handleData(array $data)
    {
        $result_array = [];
        if (is_array($data) && count($data)>0) {
            foreach ($data as $search_id => $search_val) {
                $this->find = $search_val;
                $net_data = $this->getDataFromNet();
                $this->find = (string) $search_id;
                $db_data = $this->getDataFromDB();
                $process_array = $this->processingDataDB($net_data, $db_data, $search_id, $search_val);
                array_walk($process_array,
                    function($value, $key) use(&$result_array){
                        $result_array[$key] = $value;
                    }
                );
            }
        }
        return $result_array;
    }

}