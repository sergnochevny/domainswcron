<?php

/**
 */
trait tCronStepOne_Two
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
                    $search = $this->find_str;
                    $st = $this->db->prepare(Cfg::${get_called_class()}["getdata_select_all"]);
                    if ($st->execute([$search])) {
                        $search_assoc_row = $st->fetchAll(PDO::FETCH_ASSOC);
                        array_walk($search_assoc_row,
                            function ($value) use ($result_array) {
                                if (is_string($value["id"]) && is_string($value["fvalue"])) {
                                    $result_array[$value["id"]] =
                                        htmlentities(htmlspecialchars(strip_tags($value["fvalue"])));
                                }
                            }
                        );
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
        if (is_array($data) && count($data)) {
            foreach ($data as $search_id => $search_val) {
                $this->find_str = $search_val;
                $net_data = array_keys($this->getDataFromNet());
                $db_data = $this->getDataFromDB();
                $this->db_provider = new DBProvider;
                $this->db = $this->db_provider->db;
                if (!is_null($this->db)) {
                    try {
                        $insert_data = array_diff($net_data, $db_data);
                        $delete_data = array_diff($db_data, $net_data);
                        if (is_array($delete_data) && !empty($delete_data)) {
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
                                echo $e->getMessage();
                                if ($this->db->inTransaction()) $this->db->rollBack();
                            }
                        }
                        if (is_array($insert_data) && !empty($insert_data)) {
                            $this->db->beginTransaction();
                            try {
                                if ($this->db->inTransaction()) {
                                    $st = $this->db->prepare(Cfg::${get_called_class()}["handledata_insert"]);
                                    foreach ($insert_data as $value) {
                                        if (!$st->execute([htmlentities(htmlspecialchars(strip_tags($value))), $search_id]))
                                            throw new Exception($st->errorInfo()[2]);
                                    }
                                }
                                $this->db->commit();
                            } catch (Exception $e) {
                                echo $e->getMessage();
                                if ($this->db->inTransaction()) $this->db->rollBack();
                            }
                        }
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                    $this->db_provider->closeDb();
                    if (!is_null($delete_data) || !is_null($insert_data)) {
                        $db_data = $this->getDataFromDB();
                    }
                    $result_array = $db_data;
                }
            }
        }
        return $result_array;
    }

}