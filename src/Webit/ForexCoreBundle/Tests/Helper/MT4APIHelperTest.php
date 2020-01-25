<?php

namespace Webit\ForexCoreBundle\Tests\Helper;

use \Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Webit\ForexCoreBundle\Helper\MT4API;

class MT4APIHelperTest extends WebTestCase {

    public function setUp() {
        static::$kernel = static::createKernel();
        static::$kernel->boot();        
    }

    public function testExtractLoginFromResponse() {        
        $manager = self::$kernel->getContainer()->get('Doctrine')->getManager();
        $helper = new MT4API($manager, rand(1,1000));

        $ret = $helper->extractLoginFromResponse('<ERR>0</ERR><RESULT>login=91948|</RESULT><EOL>');
        $this->assertEquals($ret, 91948);

        $ret = $helper->extractLoginFromResponse('<ERR>1</ERR><RESULT></RESULT><EOL>');
        $this->assertEquals($ret, 0);
    }

}
