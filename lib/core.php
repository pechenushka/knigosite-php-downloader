<?php

require_once "downloader.php";
require_once "parser.php";
require_once "console.php";

class Core {
    private $appConfig = array();

    private $availableOptions = array(
        '' => 'help',
        'a::' => 'author::',
        't::' => 'title::',
        'd::' => 'dir::',
        'e::' => 'extension::'
    );
    
    private $currentOptions = array();
    
    private $shortToLongOptions = array(
        'a' => 'author',
        't' => 'title',
        'd' => 'dir',
        'e' => 'extension'
    );
    
    public $pathTo = null;

    private $_isWindows = true;
    
    public function __construct($appConfig = array()) {
        $this->appConfig = $appConfig;
        $this->_isWindows = constant("IS_WINDOWS");
    }
    
    public function getOptions($argv) {
        $options = getopt(implode('', array_keys($this->availableOptions)), $this->availableOptions);
        $this->currentOptions = $options;
        
        $this->pathTo = ($this->isOptionExists('d') or $this->isOptionExists('dir'))
             ? $this->getOptionValue('d')
             : "./";
         
        if ( ! is_dir($this->pathTo) ) {
            @mkdir($this->pathTo, 0777, true);
        }
        
        return $options;
    }
    
    public function isOptionExists($option) {
        return array_key_exists($option, $this->currentOptions);
    }
    
    public function getOptionValue($option) {
        return ( isset($this->currentOptions[$option]) )
               ? $this->currentOptions[$option]
               : $this->currentOptions[$this->shortToLongOptions[$option]];
    }
    
    public function download($url, $fileTitle) {
        $pathInfo = pathinfo($url);
        $filepath = $this->pathTo . '/' . $fileTitle . "." . $pathInfo['extension'];
        
        //Console::WriteLine("Скачиваю $url в $filepath");
        Console::WriteLine("Start downloading $url in $filepath");

        Downloader::download($url, $filepath);
        
        if ( $pathInfo['extension'] == 'zip' ) {
            $dirToExtract = $this->pathTo . '/' . $fileTitle;

            //Console::WriteLine("Распаковываю $filepath в $dirToExtract");
            Console::WriteLine("Unzip $filepath in $dirToExtract");

            $this->unzip($filepath, $dirToExtract);
            
            $archivesDir = substr($dirToExtract, 0, stripos($dirToExtract, "/")) . "/archives/";
            
            if ( ! is_dir($archivesDir) ) {
                mkdir($archivesDir, 0777);
            }
            rename($filepath, $archivesDir . $fileTitle . "." . $pathInfo['extension']);
        }
    }
    
    public function downloadBooks($urlToParse) {
        /*
         * Парсим ссылки на книги
         */
        try {
            $links = Parser::getLinks(
                $urlToParse,
                ($this->isOptionExists("e") or $this->isOptionExists("extension"))
                    ? $this->getOptionValue("e")
                    : null
            );
        } catch (Exception $e) {
            Console::WriteLine($e->getMessage());
            exit(1);
        }
        
        Console::WriteLine("Books to be downloaded: " . count($links));

        foreach($links as $url => $title) {
            if ( $this->isWindows() ) {
                $title = iconv('utf-8', 'cp1251', $title);
            }
            $this->download($url, $title);
        }
    }
    
    public function downloadAuthorBooks($author) {
        if ( $this->isWindows() ) {
            $author = iconv('cp1251', 'utf-8', $author);
        }
        $urlToParse = sprintf($this->appConfig['author_search_url'], $this->prepareArgsToString($author));
        $this->downloadBooks($urlToParse);
    }
    
    public function downloadFromTitle($title) {
        if ( $this->isWindows() ) {
            $author = iconv('cp1251', 'utf-8', $author);
        }
        $urlToParse = sprintf($this->appConfig['search_url'], $this->prepareArgsToString($title));
        $this->downloadBooks($urlToParse);
    }
    
    private function prepareArgsToString($arg) {
        return str_replace(" ", "+", $arg);
    }

    public function isWindows() {
        return $this->_isWindows;
    }

    public function unzip($from, $to) {
        $zip = new ZipArchive();
        $zip->open($from);
        $zip->extractTo($to);
        $zip->close();
    }
}