<?php
require_once "phpQuery/phpQuery.php";

class Parser {

    public static function parsePages($html) {
        $document = phpQuery::newDocument($urlContent);
        $aPage = $document->find("pagination > a.page");

        $links = array();

        foreach($aPage as $pageUrl) {
            $urlContent = file_get_contents($url);
            $document = phpQuery::newDocument($urlContent);
            $a = $document->find("a.book_t");
            $links[] = $a;
        }

        return $links;
    }

    public static function getLinks($url) {
        $urlContent = file_get_contents($url);
        $document = phpQuery::newDocument($urlContent);
        $a = $document->find("a.book_t");
        
        // TODO: распарсить страницы
        $pages = self::parsePages($urlContent);
        
        $links = array();
        foreach($a as $el) {
            $link = pq($el);
            
            // gzipped page
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $link->attr('href'));
            curl_setopt ($curl, CURLOPT_USERAGENT, 'Mozilla/5.0');
            curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($curl, CURLOPT_HEADER, 1);
            curl_setopt ($curl, CURLINFO_HEADER_OUT, 1);
            $result = curl_exec($curl);
            
            if (strstr($result,"Content-Encoding: gzip")) {
                $result = preg_replace("/(.*)Content\-Encoding: gzip\s+/isU","",$result);
                $result = gzinflate(substr($result, 13));
            }
            
            $bookDocument = phpQuery::newDocument($result);
            $bookLinks = $bookDocument->find(".book_formats a");
            
            foreach($bookLinks as $bookLink) {
                switch(pq($bookLink)->text()) {
                    case ".fb2":
                        $links[pq($bookLink)->attr('href')] = pq($link)->attr('title');
                        break;
                }
            }
        }
        
        return $links;
    }
}