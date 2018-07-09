<?php

/**
 */
class StepTwo extends Steps
{
    use tStepTwo;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->crwlr = Crawler::getInstance();
        $this->parser = ParserStepTwo::getInstance();
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
                    $search = str_replace("-", "/", $this->find_str);
                    $st = $this->db->prepare(Cfg::${get_called_class()}["getdata_select_domain"]);
                    if ($st->execute([$search])) {
                        while ($domain_assoc_row = $st->fetch(PDO::FETCH_ASSOC)) {
                            if (is_string($domain_assoc_row["domain"]) && is_string($domain_assoc_row["ip"]))
                                $result_array[$domain_assoc_row["domain"]] = $domain_assoc_row["ip"];
                        }
                    } else throw new Exception($st->errorInfo()[2]);
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
        if (is_array($data) && count($data)>0) {
            if (!is_null($this->db)) {
                $this->db->beginTransaction();
                try {
                    if ($this->db->inTransaction()) {
                        $search = str_replace("-", "/", $this->find_str);
                        $st = $this->db->prepare(Cfg::${get_called_class()}["savedata_select_gap"]);
                        if ($st->execute([$search])) {
                            $gap_assoc = $st->fetchAll(PDO::FETCH_ASSOC);
                            if (count($gap_assoc)>0) {
                                $ipgap_id = $gap_assoc[0]["id"];
                                if ($ipgap_id) {
                                    foreach ($data as $domain_value => $ip_value) {
                                        $st = $this->db->prepare(Cfg::${get_called_class()}["savedata_select_domain"]);
                                        $id = 0;
                                        if ($st->execute([$domain_value])) {
                                            $res_id_array = $st->fetchAll();
                                            if (count($res_id_array)>0) $id = $res_id_array[0]["id"];
                                        } else throw new Exception($st->errorInfo()[2]);
                                        if (!is_null($id) && $id) {
                                            $st = $this->db->prepare(Cfg::${get_called_class()}["savedata_update_ip"]);
                                            if ($st->execute([$ip_value, $ipgap_id, $id])) {
                                                $this->db->lastInsertId();
                                            } else throw new Exception($st->errorInfo()[2]);
                                        } else {
                                            $st = $this->db->prepare(Cfg::${get_called_class()}["savedata_insert_ip"]);
                                            if (!$st->execute([$domain_value, $ip_value, $ipgap_id])) throw new Exception($st->errorInfo()[2]);
                                        }
                                    }
                                }
                            }
                        } else throw new Exception($st->errorInfo()[2]);
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