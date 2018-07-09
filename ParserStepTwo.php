<?php

/**
 */
class ParserStepTwo extends AbstractParser
{
    private $result_array = [];

    /**
     */
    public function __construct()
    {
        parent::__construct();
        $this->helper = Cfg::${get_called_class()}["default_helper"];
    }


    /**
     * @param $page
     * @return array
     */
    public function getParseResult($page, $tmp_name = null, &$token_net_error = null)
    {
        $token_net_error = true;
        $this->result_array = [];
        if (in_array($this->helper, Cfg::${get_called_class()}["allow_helpers"])) {
            $last_desc = '';
            switch ($this->helper) {
                case Cfg::${get_called_class()}["allow_helpers"][0]:
                    try {
                        $page_dom = new DOMDocument();
                        $page_dom->loadHTML($page);
                        foreach ($page_dom->getElementsByTagName(Cfg::${get_called_class()}["selectors"][$this->helper]["1"]) as $table_content) {
                            if (strlen($table_content->getAttribute(Cfg::${get_called_class()}["selectors"][$this->helper]["2"])) > 0) {
                                $token_net_error = false;
                                if ($table_content->getElementsByTagName(Cfg::${get_called_class()}["selectors"][$this->helper]["3"])->length > 0) {
                                    foreach ($table_content->getElementsByTagName(
                                        Cfg::${get_called_class()}["selectors"][$this->helper]["3"])[0]->getElementsByTagName(
                                        Cfg::${get_called_class()}["selectors"][$this->helper]["4"]) as $row
                                    ) {
                                        $td = $row->getElementsByTagName(Cfg::${get_called_class()}["selectors"][$this->helper]["5"]);
                                        if ($count_filds = $td->length) {
                                            $ip = trim(strip_tags($td[0]->textContent));
                                            if (($count_filds > 3) &&
                                                ($last_desc !== strtoupper(trim(strip_tags($td[2]->textContent))))
                                            ) {
                                                $last_desc = strtoupper(trim(strip_tags($td[2]->textContent)));
                                                if (preg_match(Cfg::${get_called_class()}["preg_matches"]["1"], $ip) &&
                                                    preg_match(Cfg::${get_called_class()}["preg_matches"]["2"], $last_desc)
                                                ) $this->result_array[$last_desc] = $ip;
                                            }
                                        }
                                    }
                                }
                                break;
                            }
                        }
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                    break;

                case Cfg::${get_called_class()}["allow_helpers"][1]:
                    try {
                        require_once('simple_html_dom.php');
                        $page_dom = str_get_html($page);
                        if (!empty($page_dom)) {
                            if ($page_dom->find(Cfg::${get_called_class()}["selectors"][$this->helper]["0"])) $token_net_error = false;
                            foreach ($page_dom->find(Cfg::${get_called_class()}["selectors"][$this->helper]["1"]) as $row) {
                                $td = $row->find(Cfg::${get_called_class()}["selectors"][$this->helper]["2"]);
                                if ($count_filds = count($td)) {
                                    $ip = strtoupper(trim(strip_tags($td[0]->plaintext)));
                                    if (($count_filds > 3) &&
                                        ($last_desc !== strtoupper(trim(strip_tags($td[2]->plaintext))))
                                    ) {
                                        $last_desc = strtoupper(trim(strip_tags($td[2]->plaintext)));
                                        if (preg_match(Cfg::${get_called_class()}["preg_matches"]["1"], $ip) &&
                                            preg_match(Cfg::${get_called_class()}["preg_matches"]["2"], $last_desc)
                                        ) $this->result_array[$last_desc] = $ip;
                                    }
                                }
                            }
                            $page_dom->clear();
                            unset($page_dom);
                        }
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }

                    break;
            }
        }
        if ($token_net_error) {
            $f = fopen('tmp/' . $tmp_name, 'w+');
            fwrite($f, $page);
            fclose($f);
        }

        return $this->result_array;
    }
}