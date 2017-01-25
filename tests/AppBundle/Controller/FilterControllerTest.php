<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class FilterControllerTest extends WebTestCase
{
    protected $client;

    protected function setUp()
    {
        $this->client = $this->createAuthorizedClient();
    }

    protected function createAuthorizedClient()
    {
        $client = static::createClient();
        $container = static::$kernel->getContainer();
        $session = $container->get('session');
        $person = self::$kernel->getContainer()->get('doctrine')->getRepository('AppBundle:User')->findOneByUsername('user_1');

        $token = new UsernamePasswordToken($person, null, 'main', $person->getRoles());
        $session->set('_security_main', serialize($token));
        $session->save();

        $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));

        return $client;
    }

    public function testIndex()
    {
        $crawler = $this->client->request('GET', '/filter');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());


        $this->assertGreaterThan(
            0,
            $crawler->filter('title:contains("Filtered URLs")')->count()
        );

        $this->assertGreaterThan(0, $crawler->filter('#appbundle_url_url')->count());
        $this->assertGreaterThan(0, $crawler->filter("*[id='add-url']")->count());
    }

    public function testAdd()
    {
        $crawler = $this->client->request('GET', '/filter/add');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());


        $this->assertGreaterThan(
            0,
            $crawler->filter('title:contains("Add an URL")')->count()
        );

        $this->assertGreaterThan(0, $crawler->filter('#appbundle_url_url')->count());
    }
}
