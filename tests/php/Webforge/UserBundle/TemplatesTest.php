<?php

namespace Webforge\UserBundle;

use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TemplatesTest extends \Webforge\Testing\WebTestCase
{

    use \Webforge\Testing\TestTrait;

    protected function setUp()
    {
        $this->client = self::makeClient($authentication = false);

        $this->loadAliceFixtures(
            array(
                'users'
            ),
            $this->client
        );

        $this->client->followRedirects();
    }

    public function testResettingRequestForm()
    {
        $crawler = $this->sendHtmlRequest($this->client, 'GET', '/resetting/request');

        $form = $this->htmlCount($crawler, 'form', 1);

        $label = $this->htmlCount($form, 'label[for=username]', 1);
        $this->assertContains('Benutzername oder E-Mail-Adresse', $label->text());

        $submit = $this->htmlCount($form, '[type="submit"]', 1);
    }

    public function testCheckEmailTemplate()
    {
        $crawler = $this->sendHtmlRequest($this->client, 'GET', '/resetting/request');

        $this->assertContains('Neues Passwort anfordern', $crawler->text());

        $form = $this->htmlCount($crawler, 'form', 1);

        $crawler = $this->client->submit($form->form(), array('username' => 'petra.platzhalter@ps-webforge.net'));

        $this->assertContains('Neues Passwort angefordert', $crawler->text());

        $alert = $this->htmlCount($crawler, '.alert-success', 1);

        $this->assertContains('Wir haben dir gerade eine E-Mail geschickt', $alert->text());
    }

    protected function htmlCount($crawler, $selector, $count)
    {
        $filtered = $crawler->filter($selector);
        $this->assertEquals($count, $filtered->count(), 'filter('.$selector.') ->count() does not match');
        return $filtered;
    }
}
