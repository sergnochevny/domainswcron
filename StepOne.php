<?php

/**
 */

class StepOne extends Steps
{
    use tStepOne;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->crwlr = Crawler::getInstance();
        $this->parser = ParserStepOne::getInstance();
    }


    /**
     * @return array
     */
    protected function getDataFromDB()
    {
        $result_array = [];
        if (!is_null($this->db)) {
            $this->db->beginTransaction();
            try {
                if ($this->db->inTransaction()) {
                    $search = $this->find_str;
                    $st = $this->db->prepare(Cfg::${get_called_class()}["getdata_select_all"]);
                    if ($st->execute(['%' . $search . '%'])) {
                        while ($search_assoc_row = $st->fetch(PDO::FETCH_ASSOC)) {
                            if (is_string($search_assoc_row["gap"]) && is_string($search_assoc_row["search"])) {
                                $result_array[$search_assoc_row["gap"]] = strip_tags($search_assoc_row["search"]);
                            }
                        }
                    } else {
                        throw new Exception($st->errorInfo()[2]);
                    }
                    $this->db->commit();
                }
            } catch (Exception $e) {
//                echo $e->getMessage();
                if ($this->db->inTransaction()) $this->db->rollBack();
            }
        }
        return $result_array;
    }

    /**
     * @param array $data
     * @param null $id
     */
    protected function saveDataToDB(array &$data, $id = null)
    {
        if (is_array($data) && count($data)) {
            if (!is_null($this->db)) {
                $this->db->beginTransaction();
                try {
                    if ($this->db->inTransaction()) {
                        $search = strip_tags(array_values($data)[0]);
                        $last_reg_val = '';
                        $search_id = 0;
                        foreach ($data as $ipgap_value => $reg_val) {
                            if ($last_reg_val !== strip_tags($reg_val)) {
                                $last_reg_val = strip_tags($reg_val);
                                $st = $this->db->prepare(Cfg::${get_called_class()}["savedata_select_search"]);
                                if ($st->execute([$last_reg_val])) {
                                    $search_assoc = $st->fetchAll(PDO::FETCH_ASSOC);
                                    if (count($search_assoc)) {
                                        $search_id = $search_assoc[0]["id"];
                                    } else {
                                        $st = $this->db->prepare(Cfg::${get_called_class()}["savedata_insert_search"]);
                                        if ($st->execute([$last_reg_val])) {
                                            $search_id = $this->db->lastInsertId();
                                        } else throw new Exception($st->errorInfo()[2]);
                                    }
                                } else throw new Exception($st->errorInfo()[2]);
                            }
                            if ($search_id) {
                                $st = $this->db->prepare(Cfg::${get_called_class()}["savedata_select_gap"]);
                                $id = 0;
                                if ($st->execute([strip_tags($ipgap_value)])) {
                                    $res_id_array = $st->fetchAll();
                                    if (count($res_id_array)) {
                                        $id = $res_id_array[0]["id"];
                                    }
                                } else {
                                    throw new Exception($st->errorInfo()[2]);
                                }
                                if (!is_null($id) && $id) {
                                    $st = $this->db->prepare(Cfg::${get_called_class()}["savedata_update_gap"]);
                                    if ($st->execute([strip_tags($ipgap_value), $search_id, $id])) {
                                        $this->db->lastInsertId();
                                        // delete whois & ip reference data???
                                    } else {
                                        throw new Exception($st->errorInfo()[2]);
                                    }
                                } else {
                                    $st = $this->db->prepare(Cfg::${get_called_class()}["savedata_insert_gap"]);
                                    if (!$st->execute([strip_tags($ipgap_value), $search_id]))
                                        throw new Exception($st->errorInfo()[2]);
                                }
                            }
                        }
                        $this->db->commit();
                    }
                } catch (Exception $e) {
//                    echo $e->getMessage();
                    if ($this->db->inTransaction()) $this->db->rollBack();
                }
            }
        }
    }
}