<?php

/**
 */
class BaseAbstractClass
{

    private static $instance;

    private $exists_vars_array = [];
    private $exists_method_array = [];

    /**
     * @return mixed
     */
    public static function getInstance()
    {
        $class = get_called_class();
        if (!self::$instance instanceof $class) {
            self::$instance = new $class();
        }
        return self::$instance;
    }

    /**
     *
     */
    public function __construct()
    {
        $this->exists_vars_array = get_object_vars($this);
        $this->exists_method_array = get_class_methods($this);
    }

    /**
     * @param $name
     * @return bool|mixed
     */
    public function __get($name)
    {
        $name_get_func = 'get' . ucfirst($name);
        if (array_key_exists($name, $this->exists_vars_array))
            return $this->{$name};
        if (method_exists($this, $name_get_func))
            return call_user_func([$this, $name_get_func]);
        return false;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $name_set_func = 'set' . ucfirst($name);
        if (method_exists($this, $name_set_func)) {
            call_user_func_array([$this, $name_set_func], [$value]);
        }
    }

}