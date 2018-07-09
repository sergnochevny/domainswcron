<?php

/**
 */
final class Autoload
{

    private static $_last_loaded;

    /**
     * @param $className
     * @return bool
     */
    public static function _autoload($className)
    {
        $base_dir = "";
        $class_name_parts = explode('_', $className);
        self::$_last_loaded = implode(DIRECTORY_SEPARATOR, $class_name_parts);
        $class_file = $base_dir
            . str_replace('\\', '/', self::$_last_loaded)
            . '.php';
        if (file_exists($class_file)) {
            require_once($class_file);
        } else return false;
    }

}

spl_autoload_register(array('Autoload', '_autoload'));
