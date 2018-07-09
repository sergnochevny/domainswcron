<?php

/**
 */
abstract class AbstractParser extends BaseAbstractClass
{
    protected $helper;

    /**
     * @param $value
     */
    private function setHlpr($value)
    {
        if (in_array($value, Cfg::${get_called_class()}["allow_helpers"])) $this->helper = $value;
    }

    /**
     * @param $page
     * @return mixed
     */
    abstract public function getParseResult($page);
}