<?php
namespace MiffieTest;
use Miffie\Spider;

class SpiderTest extends \PHPUnit_Framework_TestCase
{
    public function testHttpClientSetting()
    {
        $spider = 
            new Spider(array(
                'referer' => 'http://test',
                'agent' => 'dummyagent',
                'basic' => 'foo/password'
            ));
        $client = $spider->getHttpClient();

        $this->assertEquals('http://test', $client->getHeader('referer'));

        //$this->assertEquals('dummmyagent', $client->getHeader('useragent'));
        ///var_dump($client->getRequest()->headers()->toString());
    }
}

