<?php

class Console {
    public static function Write($str) {
        if ( constant("IS_WINDOWS") ) {
            $str = iconv('utf-8', 'cp1251', $str);
        }
        echo $str;
    }

    public static function WriteLine($str) {
        self::Write($str . PHP_EOL);
    }
}