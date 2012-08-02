<?php

class Downloader {
    public static function download($url, $pathTo) {
        file_put_contents($pathTo, file_get_contents($url));
    }
}