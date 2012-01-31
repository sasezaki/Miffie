<?php
namespace Miffie;

use Miffie\GetoptExt,
    Miffie\Spider;

class CLIRunner
{
    public static function run()
    {
        try {
            $getopt = static::getOpt();
            $options = $getopt->getOptionVars();
            $remain = $getopt->getRemainingArgs();
            if (count($remain) === 0) {
                throw new \InvalidArgumentException('URL is not set');
            }

            $urls = ($remain[0] == '-') ? new NoRewindIterator(new SplFileObject(STDIN)) : $remain;
            foreach ($urls as $url) {
                $runner = new Spider($options);
                $runner->run($url);
            }
        } catch (\Exception $e){
            echo $e->getMessage(), PHP_EOL;
            echo $getopt->getUsageMessage();
        }
    }

    public static function setup()
    {
        $spider = new Spider(array());
        $spider->setupAutoPagerize();
    }

    public static function getOpt()
    {
        return new GetoptExt(
        array(
         'xpath|x=s' => 'expression xpath or css selector',
          'type|v=s' => 'val type',
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
        'helper|l=s' => 'helper'
      )
        );

    }
}

