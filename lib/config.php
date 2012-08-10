<?php

class Config {
    
    public $config;

    private static $_instance = null;

    public static function instance() {
        if ( is_null(self::$_instance) ) {
            self::$_instance = new Config();
        }

        return self::$_instance;
    }

    public function setConfig($config = array()) {
        self::$_instance->config = $config;
        return $this;
    }

    public function getConfig() {
        return self::$_instance->config;
    }

    public function get($key) {
        return $this->config[$key];
    }

    public function set($key, $value) {
        $this->config[$key] = $value;
        return $this;
    }
}