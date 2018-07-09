<?php

/**
 */
class DBProvider extends BaseAbstractClass
{
    private $helper;
    private $_db = null;

    /**
     *
     */
    private function openDb(){
        $provider_str = Cfg::${get_called_class()}["provider"];
        $params_str = implode(';',
            array_map(
                function ($value, $key) {
                    if (is_string($value) && !empty($value)) {
                        return $key . '=' . $value;
                    }
                    return false;
                },
                Cfg::${get_called_class()}[Cfg::${get_called_class()}["provider"] . "_params"],
                array_keys(Cfg::${get_called_class()}[Cfg::${get_called_class()}["provider"] . "_params"])
            )
        );
        $params_str = str_replace(';;', ';', $params_str);
        if (!empty($provider_str{0}) && !empty($params_str{0})) {
            $this->_db = new PDO($provider_str . ':' . $params_str,
                Cfg::${get_called_class()}[Cfg::${get_called_class()}["provider"] . "_params"][Cfg::${get_called_class()}[Cfg::${get_called_class()}["provider"] . "_params"]["dbname"] . "_params"]["user"],
                Cfg::${get_called_class()}[Cfg::${get_called_class()}["provider"] . "_params"][Cfg::${get_called_class()}[Cfg::${get_called_class()}["provider"] . "_params"]["dbname"] . "_params"]["passwd"],
                Cfg::${get_called_class()}[Cfg::${get_called_class()}["provider"] . "_params"][Cfg::${get_called_class()}[Cfg::${get_called_class()}["provider"] . "_params"]["dbname"] . "_params"]["connection_attr"]
            );

            if ($this->_db &&
                in_array("attributes", array_keys(Cfg::${get_called_class()}[Cfg::${get_called_class()}["provider"] . "_params"][Cfg::${get_called_class()}[Cfg::${get_called_class()}["provider"] . "_params"]["dbname"] . "_params"])) &&
                is_array(Cfg::${get_called_class()}[Cfg::${get_called_class()}["provider"] . "_params"][Cfg::${get_called_class()}[Cfg::${get_called_class()}["provider"] . "_params"]["dbname"] . "_params"]["attributes"])
            ) {
                foreach (Cfg::${get_called_class()}[Cfg::${get_called_class()}["provider"] . "_params"][Cfg::${get_called_class()}[Cfg::${get_called_class()}["provider"] . "_params"]["dbname"] . "_params"]["attributes"] as $key => $value) {
                    $this->_db->query("set " . $key . "=" . $value);
                }
            }
            if ($this->_db &&
                in_array("attributes", array_keys(Cfg::${get_called_class()}[Cfg::${get_called_class()}["provider"] . "_params"][Cfg::${get_called_class()}[Cfg::${get_called_class()}["provider"] . "_params"]["dbname"] . "_params"])) &&
                is_array(Cfg::${get_called_class()}[Cfg::${get_called_class()}["provider"] . "_params"][Cfg::${get_called_class()}[Cfg::${get_called_class()}["provider"] . "_params"]["dbname"] . "_params"]["set_attr"])
            ) {
                foreach (Cfg::${get_called_class()}[Cfg::${get_called_class()}["provider"] . "_params"][Cfg::${get_called_class()}[Cfg::${get_called_class()}["provider"] . "_params"]["dbname"] . "_params"]["set_attr"] as $key => $value) {
                    $this->_db->setAttribute($key, $value);
                }
            }
        }
    }
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->_db = null;
    }

    /**
     * @param $value
     */
    private function setHlpr($value)
    {
        if (in_array($value, Cfg::${get_called_class()}["allow_helpers"])) $this->helper = $value;
    }

    /**
     * @return null
     */
    public function getDb()
    {
        if (is_null($this->_db)) $this->openDb();
        return $this->_db;
    }

    /**
     *
     */
    public function closeDb(){
        $this->_db = null;
    }
}

