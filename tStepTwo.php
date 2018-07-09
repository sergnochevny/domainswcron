<?php

/**
 */
trait tStepTwo
{
    protected $crwlr = null;
    protected $parser = null;


    /**
     * @param $value
     */
    protected function setCrawlerHelper($value)
    {
        if (!is_null($this->crwlr)) $this->crwlr->hlpr = $value;
    }

    /**
     * @param $value
     */
    public function setFind($value)
    {
        $value = str_replace("/", "-", $value);
        parent::setFind($value);
    }


    /**
     * @return mixed
     */
    public function getFind()
    {
        return str_replace("-", "/", parent::getFind());
    }

    /**
     * @return array
     */
    protected function getDataFromNet()
    {
        $result_array = [];
        $token_net_error = false;
        $iteration = 0;
        if (!is_null($this->crwlr)) {
            $this->crwlr->hlpr = 'http_r';
            $this->crwlr->url = ["request" => 'https://www.robtex.com/route/' . $this->find_str . '.html', "params" => []];
            do{
                if ($token_net_error ) sleep(360);
                $result_array = $this->parser->getParseResult($this->crwlr->data, $this->find_str, $token_net_error);
            }while($token_net_error && (Cfg::${get_called_class()}["net_iteration"] > $iteration++));
        }
        return $result_array;
    }
}