<?php

namespace Miffie;
use Miffie\Runner,
    Zend\Config\Config,
    Zend\Di\Configuration as DiConfiguration,
    Zend\Di\Di;

class Bootstrap
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function bootstrap(Runner $runner)
    {
        $this->setupLocator($runner);
    }

    protected function setupLocator(Runner $runner)
    {
        $di = new Di;
        $di->instanceManager()->addTypePreference('Zend\Di\Locator', $di);
    
        $diConfig = new DiConfiguration(array(
            'definition' => array(
                'class' => array(
                    'Zend\Cache\Storage\Adapter' => array(
                        'instantiator' => array(
                            'Zend\Cache\StorageFactory',
                            'factory'
                        ),
                    ),
                    'Zend\Cache\StorageFactory' => array(
                        'methods' => array(
                            'factory' => array('cfg' => array('required' => true, 'type' => false))
                        )
                    ),
                    'Diggin\Service\Wedata\Api\ZF2Client' => array(
                        'methods' => array(
                            'setHttpClient' => array('client' => array('required' => true, 'type' => 'Zend\Http\Client'))
                        )
                    ),
                    'Diggin\Service\Wedata\Storage\Cache' => array(
                        'methods' => array(
                            'setSearchItemDataIgnore' => array(
                                'callback' => array('required' => true, 'type' => 'Closure')
                            )
                        )
                    ),
                ),
            ),
            'instance' => array(
                'alias' => array(
                    'client' => 'Zend\Http\Client',
                    'cache' => 'Zend\Cache\Storage\Adapter',
                    //'queue'
                    //'logger'
                    'scraper' => 'Diggin\Scraper\Scraper',
                    'wedata-api' => 'Diggin\Service\Wedata\Api\ZF2Client',
                    'wedata-storage' => 'Diggin\Service\Wedata\Storage\Cache',
                ),
                'wedata-api' => array(
                    'injections' => array('client')
                ),
                'cache' => array(
                    'parameters' => array('cfg' => array(
                        'adapter' => 'Memory'
                    ))
                ),
                'wedata-storage' => array(
                    'parameters' => array(
                        'cacheStorage' => 'cache'
                    ),
                    //'injections' => array(function(){})
                ),
            )
        ));

        $diConfig->configure($di);

        $config = new DiConfiguration($this->config->di);
        $config->configure($di);
        $runner->setLocator($di);
    }
}
