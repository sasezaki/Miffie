<?php
namespace Miffie;

use Miffie\GetoptExt,
    Miffie\Spider,
    Zend\Di\Locator;

class Runner
{
    protected $locator;

    public function setLocator(Locator $locator)
    {
        $this->locator = $locator;
    }

    public function getLocator()
    {
        return $this->locator;   
    }

    public function run()
    {

        try {
            $getopt = static::getOpt();
            $options = $getopt->getOptionVars();
            $remain = $getopt->getRemainingArgs();
            if (count($remain) === 0) {
                throw new \InvalidArgumentException('URL is not set');
            } elseif ($remain[0] === '-'){
                if ($pipe = stream_get_contents(STDIN)) {
                    $urls = array_filter(explode(PHP_EOL, $pipe));
                }
            } else {
                $urls = $remain;
            }

            $runner = new Spider($options);

            foreach ($urls as $url) {
                $runner->run($url);
            }
        } catch (\InvalidArgumentException $iae){
            echo $iae->getMessage(), PHP_EOL;
            echo $getopt->getUsageMessage();
            echo '', PHP_EOL;
            echo '// example..', PHP_EOL;
            echo '$php miffie.phar -x img -v @src http://example.com/', PHP_EOL;
        }
    }

    public static function setupAutoPagerize()
    {
        $spider = new Spider(array());
        $spider->setupAutoPagerize();
    }

    public static function testSearchAutoPagerize($url)
    {
        $spider = new Spider(array());
        $storage = $spider->getWedataStorage();
        var_dump($storage->searchItemData('AutoPagerize', 'url', $url));
    }

    public static function getOpt()
    {
        return new GetoptExt(
        array(
         'xpath|x=s' => 'expression xpath or css selector',
          'type|v=s' => 'val type (text, asxml, @href, @src) default:text',
       'referer|e=s' => 'referer',
//     'cookieJar|c=s' => 'cookie',
         'agent|a=s' => 'useragent',
      'nextlink|n=s' => 'nextlink',
         'depth|d=i' => 'depth "if not set nextlink, using wedata"',
         'basic|b=s' => 'basic auth "user/pass"',
         'cache|h' => 'cache with Zend\Cache',
       'noCache|r'   => 'no-cache-force',
          'wait|w=s' => 'sleep() :default 1',
        'filter|f=s' => 'filter for Diggin\Scraper',
           'out|o=i' => 'timeout',
       'baseurl|p=s' => 'baseurl path',
        'helper|l=s' => 'helper'
      )
        );

    }
}

