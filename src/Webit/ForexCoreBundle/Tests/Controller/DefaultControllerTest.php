<?php

namespace Webit\ForexCoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testMessage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/en/message/success/test-success-message');

        $this->assertTrue($crawler->filter('.data-message-green')->count() > 0);
    }
}
