<?php

/**
 */

require_once('Autoload.php');

class AutocompleteAjax extends BaseAbstractClass
{
    protected $find_str = '';

    /**
     * @param $value
     */
    protected function setFind($value)
    {
        if (is_string($value) && !empty($value{0})) {
            $this->find_str = $value;
        }
    }

    /**
     * @param $array
     * @return bool|string
     */
    private function array_to_json($array)
    {
        if (!is_array($array)) return false;
        $associative = count(array_diff(array_keys($array), array_keys(array_keys($array))));
        if ($associative>0) {
            $construct = [];
            foreach ($array as $key => $value) {
                if (is_numeric($key)) $key = "key_$key";
                $key = "\"" . addslashes($key) . "\"";
                if (is_array($value)) {
                    $value = array_to_json($value);
                } else if (!is_numeric($value) || is_string($value)) {
                    $value = "\"" . addslashes($value) . "\"";
                }
                $construct[] = "$key: $value";
            }
            $result = "{ " . implode(", ", $construct) . " }";
        } else {
            $construct = [];
            foreach ($array as $value) {
                if (is_array($value)) {
                    $value = $this->array_to_json($value);
                } else if (!is_numeric($value) || is_string($value)) {
                    $value = "'" . addslashes($value) . "'";
                }
                $construct[] = $value;
            }
            $result = "[ " . implode(", ", $construct) . " ]";
        }
        return $result;
    }

    /**
     * @return array
     * @throws
     */
    protected function getFindInDB()
    {
        $db_provider = DBProvider::getInstance();
        $db = $db_provider->db;
        $result_array = [];
        if (!is_null($db)) {
            $db->beginTransaction();
            try {
                if ($db->inTransaction()) {
                    $search = $this->find_str;
                    $st = $db->prepare(Cfg::${get_called_class()}["sql_select"]
                        . Cfg::${get_called_class()}["autocomplete_limit"]);
                    if ($st->execute(['%' . $search . '%'])) {
                        while ($search_assoc_row = $st->fetch(PDO::FETCH_ASSOC)) {
                            if (is_string($search_assoc_row["id"]) && is_string($search_assoc_row["search"])) {
                                $result_array[] = [
                                    "id" => $search_assoc_row["id"],
                                    "label" => strip_tags($search_assoc_row["search"]),
                                    "value" => strip_tags($search_assoc_row["search"])
                                ];
                            }
                        }
                    } else {
                        throw Exception($st->errorInfo()[2]);
                    }
                    $db->commit();
                }
            } catch (Exception $e) {
//                echo $e->getMessage();
                if ($db->inTransaction()) $db->rollBack();
            }
        }
        $db_provider->closeDb();
        return $result_array;
    }

    /**
     * @return bool|string
     */
    public function getData()
    {
        $result_array = [];
        if (is_string($this->find_str) && !empty($this->find_str{0})) {
            $result_array = $this->findInDB;
            if (count($result_array)>0) {
//                return $this->array_to_json($result_array);
                return json_encode($result_array);
            }
        }
        return false;
    }
}

$find_provider = AutocompleteAjax::getInstance();
$find = $_GET['term'];
if (is_string($find) && !empty($find{0})) {

    $find_provider->find = $find;
    if (ob_get_length()) ob_clean();

    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Content-Type: text/json; charset=windows-1251;');
    echo $find_provider->data;
}


