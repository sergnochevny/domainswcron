<?php

/**
 */
trait tSteps
{
    protected $db_provider = null;
    protected $db = null;
    protected $find_str = '';

    /**
     * @param $value
     */
    public function setFind($value)
    {
        if (is_string($value) && !empty($value{0})) {
            $this->find_str = strip_tags($value);
        }
    }


    public function getFind()
    {
            return $this->find_str;
    }

    /**
     * @return mixed
     */
    abstract protected function getDataFromNet();

    /**
     * @return mixed
     */
    abstract protected function getDataFromDB();

}