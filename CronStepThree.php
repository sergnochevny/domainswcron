<?php

/**
 */
class CronStepThree extends CronSteps
{
    use tStepThree;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->whois = new Whoisdata();
        $this->parser = new ParserStepThree();
    }

    /**
     * @param $data
     * @param $id
     */
    protected function processingDataDB($data, $id){
        $this->db_provider = new DBProvider();
        $this->db = $this->db_provider->db;
        $this->saveDataToDB($data, $id);
        $this->db_provider->closeDb();
    }

    /**
     * @param array $data
     * @return array
     */
    protected function handleData(array $data)
    {
        if (is_array($data) && count($data)>0) {
            foreach ($data as $search_id => $search_val) {
                $this->find = array_keys($search_val)[0];
                $net_data = $this->getDataFromNet();
                $this->processingDataDB($net_data, $search_id);

                $f = fopen('tmp/' . get_called_class().'_'.$search_id, 'w+');
                fclose($f);

            }
        }
    }

    /**
     * @return mixed
     */
    protected function getDataFromDB()
    {
        // TODO: Implement getDataFromDB() method.
    }
}