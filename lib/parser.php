<?php
require_once "phpQuery/phpQuery.php";

class Parser {

    public static $availableExtensions = array(
        "html", "fb2", "txt", "rtf"
    );

    public static function parsePages($html) {
        $document = phpQuery::newDocument($html);
        $aPage = $document->find("a.page");

        $links = array();

        foreach($aPage as $pageUrl) {
            $urlContent = file_get_contents(pq($pageUrl)->attr("href"));
            $document = phpQuery::newDocument($urlContent);
            $a = $document->find("a.book_t");
            $links[] = $a;
        }

        return $links;
    }

    public static function getLinks($url, $ext = null) {
        $urlContent = file_get_contents($url);
        $document = phpQuery::newDocument($urlContent);
        
        // Первая страница
        $firstPage = $document->find("a.book_t");
        // Парсим остальные страницы
        $aFromPages = self::parsePages($urlContent);
        array_unshift($aFromPages, $firstPage);
        
        $links = array();        
        foreach($aFromPages as $pageNum => $a) {
            Console::WriteLine("Parse page #" . ++$pageNum);
            Console::WriteLine("Founded " . $a->count() . " books");

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
                
                if ( ! is_null($ext) ) {
                    if ( ! in_array($ext, Parser::$availableExtensions) ) {
                        throw new Exception("Format " . $ext . " is not available");
                    }
                }

                $ext or $ext = "fb2";
                
                foreach($bookLinks as $bookLink) {
                    switch(pq($bookLink)->text()) {
                        case "." . $ext:
                            $links[pq($bookLink)->attr('href')] = pq($link)->attr('title');
                            break;
                    }
                }
            }
        }
        return $links;
    }
}