<?php

/**
 */
class ParserStepOne extends AbstractParser
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
    public function getParseResult($page)
    {

        $this->result_array = [];
        if (in_array($this->helper, Cfg::${get_called_class()}["allow_helpers"])) {
            switch ($this->helper) {
                case Cfg::${get_called_class()}["allow_helpers"][0]:
                    $page_dom = new DOMDocument();
                    $page_dom->loadHTML($page);
                    $div_table = $page_dom->getElementById(Cfg::${get_called_class()}["selectors"][$this->helper]["1"]);
                    if (!empty($div_table)) {
                        $table_content = $div_table->getElementsByTagName(Cfg::${get_called_class()}["selectors"][$this->helper]["2"]);
                        if (count($table_content) && ($table_content->length === 1)) {
                            foreach ($table_content->item(0)->getElementsByTagName(Cfg::${get_called_class()}["selectors"][$this->helper]["3"]) as $row) {
                                $td = $row->getElementsByTagName(Cfg::${get_called_class()}["selectors"][$this->helper]["4"]);
                                if (count($td) && ($td->length === 2)) {
                                    $ip_gap = trim(strip_tags($td->item(0)->textContent));
                                    if (preg_match(Cfg::${get_called_class()}["preg_match"], $ip_gap)) {
                                        $desc = trim(strip_tags($td->item(1)->textContent));
                                        $this->result_array[$ip_gap] = $desc;
                                    }
                                }
                            }
                        }
                    }
                    break;

                case Cfg::${get_called_class()}["allow_helpers"][1]:
                    require_once('simple_html_dom.php');
                    $page_dom = str_get_html($page);
                    if (!empty($page_dom)) {
                        $div_table = $page_dom->find(Cfg::${get_called_class()}["selectors"][$this->helper]["1"]);
                        if (count($div_table)) {
                            $table_content = $div_table[0]->find(Cfg::${get_called_class()}["selectors"][$this->helper]["2"]);
                            if (count($table_content)) {
                                foreach ($table_content[0]->find(Cfg::${get_called_class()}["selectors"][$this->helper]["3"]) as $row) {
                                    $td = $row->find(Cfg::${get_called_class()}["selectors"][$this->helper]["4"]);
                                    if (count($td)) {
                                        $ip_gap = trim(strip_tags($td[0]->plaintext));
                                        if (preg_match(Cfg::${get_called_class()}["preg_match"], $ip_gap)) {
                                            $desc = trim(strip_tags($td[1]->plaintext));
                                            $this->result_array[$ip_gap] = $desc;
                                        }
                                    }
                                }
                            }
                        }
                        $page_dom->clear();
                        unset($page_dom);
                    }
                    break;
            }
        }

        return $this->result_array;
    }
}