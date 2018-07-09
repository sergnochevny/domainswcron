<?php

/**
 */
abstract class Steps extends BaseAbstractClass
{
    use tSteps;
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->db_provider = DBProvider::getInstance();
    }

    /**
     * @param array $data
     * @param null $id
     * @return mixed
     */
    abstract protected function saveDataToDB(array &$data, $id = null);

    /**
     * @return mixed
     */
    public function getData()
    {
        $this->db = $this->db_provider->db;
        $result_array = $this->getDataFromDB();
        if (!(is_array($result_array) && count($result_array)>0)) {
            $result_array = $this->getDataFromNet();
            if (is_array($result_array) && count($result_array)>0) {
                $this->saveDataToDB($result_array);
            }
        }
        $this->db_provider->closeDb();
        return $result_array;
    }
}