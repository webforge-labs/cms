<?php

namespace Webforge\Testing;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Json\JsonDecoder;

class WebTestCase extends SymfonyWebTestCase
{

    protected $credentials = array(
        'petra' => array(
            'username' => 'petra.platzhalter@ps-webforge.net',
            'password' => 'secret'
        )
    );

    protected function loadAliceFixtures(Array $names, $client, $con = 'default', $purge = true)
    {
        $dir = $GLOBALS['env']['root']->sub('tests/files/alice/');

        $files = array();
        foreach ($names as $name) {
            $files[] = $dir->getFile($name.'.yml');
        }

        $objectManager = $client->getContainer()->get(sprintf('doctrine.orm.%s_entity_manager', $con));
        $aliceManager = $client->getContainer()->get('webforge_symfony_alice_manager');

        $aliceManager->loadFixtures($files, $objectManager,
            $output = new \Symfony\Component\Console\Output\BufferedOutput(), $purge);
    }

    protected function sendJsonRequest(Client $client, $method, $url, $json = null)
    {
        return $client->request(
            $method,
            $url,
            array(),
            array(),
            array(
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'application/json'
            ),
            !is_string($json) ? json_encode($json, JSON_PRETTY_PRINT) : $json
        );
    }

    protected function sendHtmlRequest(Client $client, $method, $url, $body = null)
    {
        return $client->request(
            $method,
            $url,
            array(),
            array(),
            array(
                'HTTP_ACCEPT' => 'text/html'
            ),
            $body
        );
    }

    protected function assertJsonResponse($statusCode, $client)
    {
        $this->assertStatusCode($statusCode, $client);

        $response = $client->getResponse();
        $content = (string)$response->getContent();

        if (empty($content)) {
            return null;
        }

        try {
            return new ObjectAsserter($this->parseJSON($content), $this);
        } catch (\Webmozart\Json\DecodingFailedException $e) {
            $this->fail('could not convert to json-response: '.$content);
        }
    }

    /**
     * @param $string
     * @return mixed
     * @throws \Webmozart\Json\ValidationFailedException
     */
    public function parseJSON($string)
    {
        $decoder = new JsonDecoder();
        return $decoder->decode($string);
    }


    public static function makeClient($credentials)
    {
        $server = [];
        if ($credentials) {
            $server['PHP_AUTH_USER'] = $credentials['username'];
            $server['PHP_AUTH_PW'] = $credentials['password'];
        }

        $client = self::createClient(array('environment' => 'test'), $server);
        $kernel = $client->getKernel();

        // the test client emulates the apache server, and "overwrites" the routing request host (which we have configured for cli) with HTTP_HOST
        // maybe they fixed this in symfony 4.0?
        $context = $kernel->getContainer()->get('router')->getContext();
        $server['HTTP_HOST'] = $context->getHost();

        $client->setServerParameters($server);

        return $client;
    }

    /**
     * Asserts that the HTTP response code of the last request performed by
     * $client matches the expected code. If not, raises an error with more
     * information.
     *
     * @param $expectedStatusCode
     * @param Client $client
     */
    public function assertStatusCode($expectedStatusCode, Client $client)
    {
        $helpfulErrorMessage = null;

        /**
         * @var \Symfony\Component\HttpFoundation\Response $response
         */
        $response = $client->getResponse();

        if ($expectedStatusCode !== $response->getStatusCode()) {
            /*
            // Get a more useful error message, if available
            if ($exception = $client->getContainer()->get('liip_functional_test.exception_listener')->getLastException()) {
                $helpfulErrorMessage = $exception->getMessage();
            } elseif (count($validationErrors = $client->getContainer()->get('liip_functional_test.validator')->getLastErrors())) {
                $helpfulErrorMessage = "Unexpected validation errors:\n";

                foreach ($validationErrors as $error) {
                    $helpfulErrorMessage .= sprintf("+ %s: %s\n", $error->getPropertyPath(), $error->getMessage());
                }
            } else {

            */
            $helpfulErrorMessage = sprintf(
                "\n** Tested Request: %s\n\n** Tested Response:\n%s",
                $client->getRequest(),
                substr($client->getResponse(), 0, 800)
            );

            //file_put_contents(getcwd().'/last-exception.html', $client->getResponse());
        }

        self::assertEquals($expectedStatusCode, $response->getStatusCode(), $helpfulErrorMessage);
    }
}
