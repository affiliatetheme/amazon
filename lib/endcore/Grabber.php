<?php
/**
 * Project      affiliatetheme-amazon
 * @author      Giacomo Barbalinardo <info@ready24it.eu>
 * @copyright   2020
 */

namespace Endcore;


class Grabber
{
    protected $url;
    protected $html;
    protected $hrefs;
    protected $asins;

    public function __construct($url)
    {
        $this->url = $url;
        $plainHtml = '';

        if (ini_get('allow_url_fopen')) {

            $plainHtml = file_get_contents($this->url);

        } elseif (function_exists("curl_init")) {
            $ch_init = curl_init();
            $user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; "."Windows NT 5.0)";
            $ch_init = curl_init();
            curl_setopt($ch_init, CURLOPT_USERAGENT, $user_agent);
            curl_setopt( $ch_init, CURLOPT_HTTPGET, 1 );
            curl_setopt( $ch_init, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $ch_init, CURLOPT_FOLLOWLOCATION , 1 );
            curl_setopt( $ch_init, CURLOPT_FOLLOWLOCATION , 1 );
            curl_setopt( $ch_init, CURLOPT_URL, $this->url);
            curl_setopt ($ch_init, CURLOPT_COOKIEJAR, 'cookie.txt');
            $plainHtml = curl_exec($ch_init);
            curl_close($ch_init);
        } else {
            $hfile = fopen($this->url, "r");
            if($hfile)
            {
                while(!feof($hfile))
                {
                    $plainHtml .= fgets($hfile,1024);
                }
            }
        }

        $this->html = $plainHtml;
        $this->setHrefs();
        $this->setAsins();
    }

    public function getPlainHtml(){
        return $this->html;
    }

    public function setHrefs()
    {
        $entries = array();

        $dom = new \DOMDocument;
        @$dom->loadHTML($this->html);

        $xpath = new \DOMXPath($dom);
        $nodes = $xpath->query('//a/@href');

        foreach($nodes as $href) {
            $url = trim($href->nodeValue);
            if(strpos($url, '/dp/') && strpos($url, '&sr=1-1-spons') === false) {
                $entries[] = $url;
            }
        }

        $this->hrefs = $entries;
    }

    public function extractAsin($url){
        $re = "/\\/dp\\/([A-Za-z0-9]*)\\//";
        if (preg_match($re, $url, $matches)) {
            $match = explode('?', $matches[1]);
            return $match[0];
        } else {
            $url_arr = explode('dp/', $url);
            $match = explode('?',end($url_arr));
            return $match[0];
        }

        return '';
    }

    public function setAsins()
    {
        $asins = array();

        foreach ($this->hrefs as $asinUrl) {
            $asins[] = $this->extractAsin($asinUrl);
        }

        $this->asins = $asins;
    }

    public function getAsins(){
        return array_unique($this->asins);
    }
}
