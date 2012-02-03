<?php

error_reporting( E_ALL | E_STRICT );

require_once dirname(__DIR__).'/vendor/zf2/library/Zend/Loader/StandardAutoloader.php';

$loader = new Zend\Loader\StandardAutoloader();
$loader->registerNamespace('Diggin\Http\Charset', 
  dirname(__DIR__).'/vendor/Diggin_Http_Charset/src/Diggin/Http/Charset');
$loader->registerNamespace('Diggin\Scraper', 
  dirname(__DIR__).'/vendor/Diggin_Scraper/src/Diggin/Scraper');
$loader->registerNamespace('Diggin\Scraper\Adapter\Htmlscraping', 
  dirname(__DIR__).'/vendor/Diggin_Scraper_Adapter_Htmlscraping/src/Diggin/Scraper/Adapter/Htmlscraping');
$loader->registerNamespace('Diggin\Service\Wedata',
  dirname(__DIR__).'/vendor/Diggin_Service_Wedata/src/Diggin/Service/Wedata');
$loader->registerNamespace('Miffie', dirname(__DIR__).'/src/Miffie');
$loader->register();

