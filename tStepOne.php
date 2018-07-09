<?php

/**
 */
trait tStepOne
{
    protected $crwlr = null;
    protected $parser = null;


    /**
     * @param $value
     */
    protected function setCrawlerHelper($value)
    {
        if (!is_null($this->crwlr)) {
            $this->crwlr->hlpr = $value;
        }
    }

    /**
     * @return array
     */
    protected function getDataFromNet()
    {
        $result_array = [];
        if (!is_null($this->crwlr)) {
            $this->crwlr->find = $this->find_str;
            $result_array = $this->parser->getParseResult($this->crwlr->data);
            if(count($result_array)>0){
                array_walk($result_array,
                    function(&$value){
                        $value = trim(preg_replace('/[\\s]{2,}/', ' ', preg_replace('#[^a-zA-Z0-9\\s_-`"]*#si', '', $value)));
                    }
                );
            }
        }
        return $result_array;
    }

}