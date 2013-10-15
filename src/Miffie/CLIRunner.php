<?php
namespace Miffie;

use Miffie\GetoptExt,
    Miffie\Spider;

class CLIRunner
{
    public static function run($argv)
    {
        // check backend commands
        if (isset($argv[1]) && strpos($argv[1], '---') === 0) {
            if ($argv[1] == '---autopagerize-setup') {
                static::setupAutoPagerize();
            } else if ($argv[1] == '---autopagerize-search') {
                static::testSearchAutoPagerize($argv[2]);
            }
            exit(0);
        }

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

            foreach ($urls as $url) {
                $runner = new Spider($options);
                try {
                    $runner->run($url);
                } catch(\Exception $e) {
                    if (isset($options) && isset($options['silent']) && $options['silent']) {
                        continue;
                    } else {
                        throw $e;
                    }
                }
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
        'silent|s'   => 'silent if runner raise Exception',
         'cache|h'   => 'cache with Zend\Cache',
       'noCache|r'   => 'no-cache-force',
          'wait|w=s' => 'sleep() :default 1',
        'filter|f=s' => 'filter for Diggin\Scraper',
           'out|o=i' => 'timeout',
       'baseurl|p=s' => 'baseurl path',
        'helper|l=s' => 'helper method'
            )
        );

    }
}

