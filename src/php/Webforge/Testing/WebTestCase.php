<?php

namespace Webforge\Testing;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;
use Symfony\Component\Debug\Exception\FlattenException;
use Webmozart\Json\JsonDecoder;

class WebTestCase extends SymfonyWebTestCase
{
    use MockeryPHPUnitIntegration;

    protected $credentials = array(
        'petra' => array(
            'username' => 'petra.platzhalter@ps-webforge.net',
            'password' => 'secret'
        )
    );

    protected function loadAliceFixtures(array $names, $client, $con = 'default', $purge = true)
    {
        $dir = $GLOBALS['env']['root']->sub('tests/files/alice/');

        $files = array();
        foreach ($names as $name) {
            $files[] = $dir->getFile($name.'.yml');
        }

        $objectManager = $client->getContainer()->get(sprintf('doctrine.orm.%s_entity_manager', $con));
        $aliceManager = $client->getContainer()->get('webforge_symfony_alice_manager');

        $aliceManager->loadFixtures(
            $files,
            $objectManager,
            $output = new \Symfony\Component\Console\Output\BufferedOutput(),
            $purge
        );
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
        $client->enableProfiler();

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

        $helpfulErrorMessage = sprintf(
            "\n** Tested Request: %s\n\n",
            $client->getRequest()
        );

        if ($expectedStatusCode !== $response->getStatusCode()) {
            if (($profile = $client->getProfile()) && $profile->hasCollector('exception') && ($collector = $profile->getCollector('exception'))->hasException()) {
                /** @var FlattenException $exception */
                $exception = $collector->getException();

                $trace = '';
                foreach ($exception->getTrace() as $step) {
                    // verbose mode:
                    //$trace .= sprintf("\n  at %s->%s(%s)\n    (%s:%d)", $step['class'], $step['function'], json_encode($step['args']), $step['file'], $step['line']);
                    // tiny mode:
                    $trace .= sprintf("\n at %s:%d", $step['file'], $step['line']);
                }

                $helpfulErrorMessage .= sprintf(
                    "** Exception %s was thrown with Message: '%s'.%s",
                    $exception->getClass(),
                    $exception->getMessage(),
                    $trace
                );
            } else {
                $helpfulErrorMessage = sprintf(
                    "** Tested Response:\n%s",
                    $client->getRequest(),
                    $client->getResponse()
                );
            }
        }

        self::assertEquals($expectedStatusCode, $response->getStatusCode(), $helpfulErrorMessage);
    }
}
