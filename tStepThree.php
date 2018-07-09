<?php

/**
 */
trait tStepThree
{

    protected $whois = null;
    protected $parser = null;

    /**
     * @param $value
     * @return string
     */
    private function map_md5($value)
    {
        if (in_array($value, array_keys(Cfg::${get_called_class()}["whois_ident_fields"])))
            $value = Cfg::${get_called_class()}["whois_ident_fields"][$value];
        return md5(trim($value));
    }

    /**
     * @param $in_key
     * @param array $keys_array
     * @param $ret_key
     * @return bool
     */
    private function array_similar_key_exists($in_key, array $keys_array, &$ret_key)
    {
        $similar = false;
        $key_hash = array_map([$this, 'map_md5'], explode(' ', strtolower($in_key)));
        $keys_array_hashes = [];
        foreach ($keys_array as $key => $row)
            $keys_array_hashes[$row] = array_map([$this, 'map_md5'], explode(' ', strtolower($row)));
        foreach ($keys_array_hashes as $ret_key => $hash_array) {
            $similar = true;
            foreach ($key_hash as $hash) {
                $similar = $similar && in_array($hash, $hash_array);
            }
            if ($similar) break;
            else {
                if ((count($hash_array) > 1) && (count($key_hash) > count($hash_array))) {
                    $similar = true;
                    foreach ($hash_array as $hash_array_row) {
                        $similar = $similar && in_array($hash_array_row, $key_hash);
                    }
                    if ($similar) break;
                }
            }
        }
        return $similar;
    }

    /**
     * @return array
     */
    protected function getDataFromNet()
    {
        $result_array = [];
        $this->whois->searchDomain = $this->find_str;
        $result_whois_str = (string)$this->whois->data;
        if (is_string($result_whois_str) && !empty($result_whois_str{0})) {
            $result_array = $this->parser->getParseResult($result_whois_str);
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
            try {
                $this->db->beginTransaction();
                if ($this->db->inTransaction()) {
                    if ((!is_null($id) && $id)) {
                        $ip_id = $id;
                    }else{
                        $st = $this->db->prepare(Cfg::${get_called_class()}["savedata_select_domain"]);
                        if ($st->execute([$this->find_str])) {
                            $ip_assoc = $st->fetchAll(PDO::FETCH_ASSOC);
                            if (count($ip_assoc)>0) {
                                $ip_id = $ip_assoc[0]["id"];
                            }
                        } else {
                            throw new Exception($st->errorInfo()[2]);
                        }
                    }

                    if (!is_null($ip_id) && $ip_id) {
                        $st = $this->db->prepare(Cfg::${get_called_class()}["savedata_select_whois"]);
                        $id = 0;
                        if ($st->execute([$ip_id])) {
                            $res_id_array = $st->fetchAll();
                            if (count($res_id_array)>0) $id = $res_id_array[0]["id"];
                        } else {
                            throw new Exception($st->errorInfo()[2]);
                        }
                        $st = $this->db->query(Cfg::${get_called_class()}["savedata_select_fields"]);
                        $fields_assoc = $st->fetchAll(PDO::FETCH_ASSOC);
                        $sql_array = [];
                        foreach ($fields_assoc as $fields_assoc_row) {
                            if ($this->array_similar_key_exists($fields_assoc_row["name"], array_keys($data), $ret_key)) {
                                if (is_array($data[$ret_key])) {
                                    $sql_array["`field_" . $fields_assoc_row["id"] . "`"] =
                                        strip_tags(join(', ', $data[$ret_key]));
                                } else $sql_array["`field_" . $fields_assoc_row["id"] . "`"] =
                                    strip_tags($data[$ret_key]);
                            }
                        }
                        if (count($sql_array)>0) {
                            if (!is_null($id) && $id) {
                                $sql_update_str = sprintf(Cfg::${get_called_class()}["savedata_update_whois"],
                                    join('=?, ', array_keys($sql_array)));
                                $params_array = array_values($sql_array);
                                $params_array[] = $id;
                                $st = $this->db->prepare($sql_update_str);
                                if (!$st->execute($params_array)) {
                                    throw new Exception($st->errorInfo()[2]);
                                }
                            } else {
                                $sql_insert_str = sprintf(Cfg::${get_called_class()}["savedata_insert_whois"],
                                    join(', ', array_keys($sql_array)),
                                    join(', ', array_fill(0, count($sql_array) + 1, '?')));
                                $params_array = array_values($sql_array);
                                $params_array[] = $ip_id;
                                $st = $this->db->prepare($sql_insert_str);
                                if (!$st->execute($params_array)) {
                                    throw new Exception($st->errorInfo()[2]);
                                }
                            }
                        } else $data = [];
                    }
                }
                $this->db->commit();
            } catch (Exception $e) {
//                echo $e->getMessage();
                $this->db->rollBack();
            }
        }
    }

}