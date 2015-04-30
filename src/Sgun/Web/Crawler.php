<?php

namespace Sgun\Web;

/**
 * Crawler of a website
 *
 * @author prakash
 */
class Crawler 
{

    protected $url;
    protected $host;
    protected $depth = 5;
    protected $error;
    protected $crawledUrls = array();

    public function setUrl($url) {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            $this->error = "Not a valid url ";
            return false;
        }
        $this->url = $url;
        $parse = parse_url($this->url);
        $this->host = $parse['host'];
        return true;
    }

    public function setDepth($depth) {
        if (is_numeric($depth) !== true) {
            $this->error = "Depth is not numeric";
            return false;
        }
        $this->depth = $depth;
        return true;
    }

    public function getError() {
        return $this->error;
    }

    protected function countTags($content, $name) {
        $dom = new \DOMDocument('1.0');
        @$dom->loadHTML($content);
        return $dom->getElementsByTagName('img')->length;
    }

    protected function processInsideUrls($content, $url, $depth) {
        $dom = new \DOMDocument('1.0');
        @$dom->loadHTML($content);
        $anchors = $dom->getElementsByTagName('a');
        foreach ($anchors as $element) {
            $href = $element->getAttribute('href');
            if (0 !== strpos($href, 'http')) {
                $path = '/' . ltrim($href, '/');
                if (extension_loaded('http')) {
                    $href = http_build_url($url, array('path' => $path));
                } else {
                    $parts = parse_url($url);
                    $href = $parts['scheme'] . '://';
                    if (isset($parts['user']) && isset($parts['pass'])) {
                        $href .= $parts['user'] . ':' . $parts['pass'] . '@';
                    }
                    $href .= $parts['host'];
                    if (isset($parts['port'])) {
                        $href .= ':' . $parts['port'];
                    }
                    $href .= $path;
                }
            }
            /**
              Crawl only link that belongs to the start domain
             * */
            $this->crawlPage($href, $depth - 1);
        }
    }

    protected function fetchUrlContent($url) {
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        /* Get the HTML or whatever is linked in $url. */
        $response = curl_exec($handle);
        /** response total time * */
        $time = curl_getinfo($handle, CURLINFO_TOTAL_TIME);
        /* Check for 404 (file not found). */
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);
        return array($response, $httpCode, $time);
    }

    protected function showResult($url, $depth, $httpcode, $time, $tagcount) {
        @ob_end_flush();
        $currentDepth = $this->depth - $depth;
        $count = count($this->crawledUrls);
        echo "N::$count, img tag::$tagcount,    CODE::$httpcode, "
        . "    TIME::$time, "
        . "   DEPTH::$currentDepth, URL::$url \n";
        ob_start();
        flush();
    }

    protected function isValid($url, $depth) {
        if (strpos($url, $this->host) === false) {
            return false;
        }
        if ($depth === 0) {
            return false;
        }
        if (isset($this->crawledUrls[$url])) {
            return false;
        }
        return true;
    }

    /**
     * Start the crawling
     * @return boolean
     */
    protected function crawlPage($url, $depth) {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            $this->error = "Url not set.";
            return false;
        }
        if (is_numeric($depth) !== true) {
            $this->error = "Depth not set.";
            return false;
        }
        if ($this->isValid($url, $depth) === false) {
            $this->error = "Depth reached or cannot crawl further.";
            return false;
        }
        $this->crawledUrls[$url] = true;
        /*         * * get crawled content ** */
        list($content, $httpcode, $time) = $this->fetchUrlContent($url);
        $tagcount = $this->countTags($content, 'img');
        $this->showResult($url, $depth, $httpcode, $time, $tagcount);
        /*         * * process subsequent pages ** */
        $this->processInsideUrls($content, $url, $depth);
        return true;
    }

    public function run() {
        return $this->crawlPage($this->url, $this->depth);
    }

}
