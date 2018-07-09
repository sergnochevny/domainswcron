<?php

/**
 */
class StepThree extends Steps
{
    use tStepThree;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->whois = Whoisdata::getInstance();
        $this->parser = ParserStepThree::getInstance();
    }


    /**
     * @return array
     */
    protected function getDataFromDB()
    {
        $result_array = [];
        if (!is_null($this->db)) {
            try {
                $this->db->beginTransaction();
                if ($this->db->inTransaction()) {
                    $st = $this->db->prepare(Cfg::${get_called_class()}["getdata_select_domain"]);
                    if ($st->execute([$this->find_str])) {
                        $ip_assoc = $st->fetchAll(PDO::FETCH_ASSOC);
                        if (count($ip_assoc)>0) {
                            $ip_id = $ip_assoc[0]["id"];
                            if (!is_null($ip_id) && $ip_id) {

                                $st = $this->db->query(Cfg::${get_called_class()}["getdata_select_fields"]);
                                $fields_assoc = $st->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($fields_assoc as $fields_assoc_row) {
                                    $fields_array["field_" . $fields_assoc_row["id"]] = $fields_assoc_row["name"];
                                }

                                $st = $this->db->prepare(Cfg::${get_called_class()}["get_data_select_whois"]);
                                if ($st->execute([$ip_id])) {
                                    $data_assoc = $st->fetchAll(PDO::FETCH_ASSOC);
                                    if (count($data_assoc)>0) {
                                        foreach ($data_assoc[0] as $field => $field_data) {
                                            if (!is_null($field_data) && is_string($field_data)) {
                                                if (in_array($field, array_keys($fields_array))) {
                                                    $result_array[$fields_array[$field]] = $field_data;
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    throw new Exception($st->errorInfo()[2]);
                                }
                            }
                        }
                    } else {
                        throw new Exception($st->errorInfo()[2]);
                    }
                }
                $this->db->commit();
            } catch (Exception $e) {
//                echo $e->getMessage();
                $this->db->rollBack();
            }
        }
        return $result_array;
    }
}