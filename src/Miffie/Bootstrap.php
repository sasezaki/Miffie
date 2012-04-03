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
                    )
                ),
            ),
            'instance' => array(
                'alias' => array(
                    'cache' => 'Zend\Cache\Storage\Adapter',
                    'scraper' => 'Diggin\Scraper\Scraper',
                    'wedata-storage' => 'Diggin\Service\Wedata\Storage\Cache'
                ),
                'cache' => array(
                    'parameters' => array('cfg' => array(
                        'adapter' => 'Memory'
                    ))
                )
            )
        ));

        $diConfig->configure($di);

        $config = new DiConfiguration($this->config->di);
        $config->configure($di);
        $runner->setLocator($di);
    }
}
