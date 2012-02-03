<?php
namespace Miffie;

use Zend\Http\Client,
    Zend\Http\Response,
    Zend\Cache\StorageFactory,
    Zend\Cache\Storage\Adapter as CacheStorage,
    Diggin\Scraper\Scraper,
    Diggin\Scraper\Process as ScraperProcess,
    Diggin\Service\Wedata\Api\ZF2Client as WedataApi,
    Diggin\Service\Wedata\Storage\Cache as WedataStorage;

class Spider
{
    protected $options;

    protected $httpClient;

    protected $stoptime;
    protected $depth;
    protected $filter;
    
    protected $cache;
    protected $wedataStorage;

    protected $startUrl;
    protected $debug = false;

    public function __construct($options = array())
    {
        $this->options = $options;
    }

    public function run($url)
    {
        $this->init();

        $doNext = (boolean) ($this->depth > 1);
        $depth = $this->depth;

        $filter = $this->filter;
        $client = $this->getHttpClient();

        // extract options
        $this->startUrl = $url;
        $opt = $this->options;

        for ($i = 1; $i <= $depth; $i++) {

            $client->setUri($url);
            
            if (isset($opt['cache']) && !isset($opt['noCache'])) {
                $response = $this->requestIfNotInCache();
            } else {
                $response = $client->send();
            }

            if ($doNext && !isset($opt['nextlink'])){
                //searching wedata
               $nextLink = $this->searchNextLinkFromWedata($url) ;
            } else if ($doNext && isset($opt['nextlink'])) {
               $nextLink = $opt['nextlink'];
            }
            
            $scraper = new Scraper();
            $scraper->setUrl($url);
            
            $type = isset($opt['type']) ? $opt['type'] : 'TEXT';
            $helper = isset($opt['helper'])? $opt['helper'] : null;

            $process = new ScraperProcess;
            $process->setExpression($opt['xpath']);
            $process->setName('xpath');
            $process->setArrayFlag(true);
            $process->setType($type);
            if (isset($filter)) {
                $process->setFilters(array($filter));
            }
            $scraper->process($process);

            if (isset($nextLink) && !($i == $depth)) {
                $nl = new ScraperProcess;
                $nl->setExpression($nextLink);
                $nl->setName('nextLink');
                $nl->setType('@href');
                $nl->setArrayFlag(false);
                $scraper->process($nl);
            }

            if ($this->debug) {
                echo PHP_EOL, time();
            }

            $ret =  $scraper->scrape($response);
          
            /**
            if ($helper) {
                try {
                    $helperValue = $scraper->$helper();
                    echo (is_array($helperValue)) ? $helperValue[0]: $helperValue;
                } catch (Exception $e){
                    die($e);
                }
            } else {
                echo implode("\n", $ret['xpath']);
            }*/
            $xpath = $ret['xpath'];
            $xpath = array_filter($xpath);
            echo implode("\n", $xpath);

            if (!$doNext or ($i == $depth)) {
                echo PHP_EOL;
                goto loop_exit;
            }
            
            if (!isset($ret['nextLink'])) {
                echo 'next page not found';
                exit;
            } else {
                $url = \Zend\Uri\UriFactory::factory((string) $ret['nextLink']);
                $url->normalize();
                // todo set-referer
                echo PHP_EOL;
                sleep($this->stoptime);
            }
        }
        loop_exit:
    }

    public function setupAutoPagerize()
    {
        $wedataStorage = $this->getWedataStorage();
        $wedata = new WedataApi;
        //$wedata->setHttpClient($this->getHttpClient());
        $items = $wedata->getItems('AutoPagerize', null);

        return $wedataStorage->storeItems('AutoPagerize', $items);
    }

    public function init()
    {
        $opt = $this->options;

        if(!isset($opt['xpath'])) {
            throw new \InvalidArgumentException('currently, should use xpath: -x //html/body');
        }

        if (isset($opt['wait'])) {
            $this->stoptime = $opt['wait'];
        } else {
            $this->stoptime = 1/10;
        }

        $this->depth = isset($opt['depth']) ? $opt['depth'] : 1;
        $this->filter = isset($opt['filter']) ? static::evalFilter($opt['filter']) : null;
    }
    
    protected function getHttpClient()
    {
        if (!$this->httpClient) {
            $opt = $this->options;
            $client = new Client;

            if (isset($opt['agent'])) {
                $client->setConfig(array('useragent'=> $opt['agent']));
            }

            if (isset($opt['basic'])) {
                list($basicusername, $basicpassword) = explode('/', $opt['basic']);
                if(!$basicpassword) throw new InvalidArgumentException('argument is not user/pass');
                $client->setAuth($basicusername, $basicpassword, Zend_Http_Client::AUTH_BASIC);
            }

            if (isset($opt['referer'])) {
                $referer = $opt['referer'];
                $client->setHeaders("Referer: $referer");
            }

        /**
        if ($console->cookieJar) {
            require_once 'Diggin/Http/CookieJar/Loader/Firefox3.php';
            if ($cookieJar = Diggin_Http_CookieJar_Loader_Firefox3::load($console->cookieJar, $url)) {
                $client->setCookieJar($cookieJar);
            }
        }*/

            if (isset($opt['out'])) $client->setConfig(array('timeout' => $opt['out']));

            $this->httpClient = $client;
        }

        return $this->httpClient;
    }

    /**
     * @return callable
     */
    public static function evalFilter($string)
    {
        if ((substr($string, 0, 2) === 's/') or 
        (substr($string, 0, 2) === 's#')) {
        $quote = substr($string,1,1);
        list($regex, $after) = explode($quote, substr($string, 2));
        $filter = create_function('$var', <<<FUNC
return preg_replace('/'.preg_quote("$regex", '/').'/', "$after", \$var);
FUNC
);
        }

        return $filter;
    }

    /**
     * @return Zend\Cache\Storage\Adapter
     */
    public function getCacheStorage()
    {
        if (!$this->cache) {
            $opt = $this->options;
            $cache_dir = (isset($opt['cache']) && $opt['cache'] != true) ? $opt['cache'] : $_SERVER['HOME'].'/.miffie/cache';
            $this->cache = StorageFactory::factory(array(
                'adapter' => array(
                    'name' => 'Filesystem',
                    'options' => array(
                        'cache_dir' => $cache_dir,
                        'ttl' => 86400
                    )
                ),
                'plugins' => array(
                    'serializer'
                )
            ));
        }

        return $this->cache;
    }

    /**
     * @return Diggin\Service\Wedata\Storage
     */
    public function getWedataStorage()
    {
        if (!$this->wedataStorage) {
            $this->wedataStorage = new WedataStorage($this->getCacheStorage());
            $this->wedataStorage->setSearchItemDataIgnore(function ($current, $key, $iterator) {
                //if ('^https?://.' != $item['data']['url'] && (preg_match('>'.$item['data']['url'].'>', $url) == 1)) {
                $data = $current->getData();
                return $data->url !== '^https?://.';
            });
        }
        
        return $this->wedataStorage;
    }

    public function searchNextLinkFromWedata($url)
    {
        $item = $this->getWedataStorage()->searchItemData('AutoPagerize', 'url', $url);
        if ($item === false) {
            throw new \DomainException('not found from wedata with url:'.$url);
        }

        return $item->getData()->nextLink;
    }

    //
    public function requestIfNotInCache()
    {
        $key = md5($this->getHttpClient()->getRequest());
        if (!$httpResponseString = $this->getCacheStorage()->getItem($key)) {
            $httpResponse = $this->getHttpClient()->send();     
            $this->getCacheStorage()->setItem($key, $httpResponseString = $httpResponse->toString());
        }
        
        $res = Response::fromstring($httpResponseString);

        return $res;
    }
}
