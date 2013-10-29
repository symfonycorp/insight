<?php

/*
 * This file is part of the SensioLabsInsight package.
 *
 * (c) SensioLabs <contact@sensiolabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Insight\Sdk;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Guzzle\Common\Collection;
use Guzzle\Common\Event;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Message\Request;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Psr\Log\LoggerInterface;
use SensioLabs\Insight\Sdk\Exception\ApiClientException;
use SensioLabs\Insight\Sdk\Exception\ApiServerException;
use SensioLabs\Insight\Sdk\Model\Project;

class Api
{
    const ENDPOINT = 'https://insight.sensiolabs.com';

    private $client;
    private $serializer;
    private $parser;
    private $logger;

    public function __construct($options = array(), Client $client = null, Parser $parser = null, LoggerInterface $logger = null)
    {
        $this->client = $client ?: new Client();
        $this->parser = $parser ?: new Parser();

        $defaultOptions = array(
            'base_url' => static::ENDPOINT,
            'cache' => false,
            'debug' => false,
        );
        $requiredOptions = array('api_token', 'base_url', 'user_uuid');
        $options = Collection::fromConfig($options, $defaultOptions, $requiredOptions);
        $this->client->setConfig($options);

        $this->client->setBaseUrl($options->get('base_url'));
        $this->client->setDefaultHeaders(array(
            'accept' => 'application/vnd.com.sensiolabs.insight+xml',
        ));

        $this->client->getEventDispatcher()->addListener('client.create_request', function(Event $event) {
            $url = $event['request']->getUrl(true);
            $url->getQuery()->set('apiToken', $event['client']->getConfig()->get('api_token'));
            $url->getQuery()->set('userUuid', $event['client']->getConfig()->get('user_uuid'));
            $event['request']->setUrl($url);
        });

        $serializerBuilder = SerializerBuilder::create()
            ->addMetadataDir(__DIR__.'/Model')
            ->setDebug($options->get('debug'))
        ;
        if ($cache = $options->get('cache')) {
            $serializerBuilder = $serializerBuilder->setCacheDir($cache);
        }
        $this->serializer = $serializerBuilder->build();

        AnnotationRegistry::registerLoader('class_exists');

        $this->logger = $logger;
    }

    public function getProjects($page = 1)
    {
        $request = $this->client->createRequest('GET', '/api/projects');
        $url = $request->getUrl(true);
        $url->getQuery()->set('page', (int) $page);
        $request->setUrl($url);

        return $this->serializer->deserialize(
            (string) $this->send($request)->getBody(),
            'SensioLabs\Insight\Sdk\Model\Projects',
            'xml'
        );
    }

    public function getProject($uuid)
    {
        $request = $this->client->createRequest('GET', sprintf('/api/projects/%s', $uuid));

        return $this->serializer->deserialize(
            (string) $this->send($request)->getBody(),
            'SensioLabs\Insight\Sdk\Model\Project',
            'xml'
        );
    }

    public function updateProject(Project $project)
    {
        $request = $this->client->createRequest('PUT', sprintf('/api/projects/%s', $project->getUuid()), null, array('insight_project' => $project->toArray()));

        return $this->serializer->deserialize(
            (string) $this->send($request)->getBody(),
            'SensioLabs\Insight\Sdk\Model\Project',
            'xml'
        );
    }

    public function createProject(Project $project)
    {
        $request = $this->client->createRequest('POST', '/api/projects', null, array('insight_project' => $project->toArray()));

        return $this->serializer->deserialize(
            (string) $this->send($request)->getBody(),
            'SensioLabs\Insight\Sdk\Model\Project',
            'xml'
        );
    }

    public function getAnalyses($projectUuid)
    {
        $request = $this->client->createRequest('GET', sprintf('/api/projects/%s/analyses', $projectUuid));

        return $this->serializer->deserialize(
            (string) $this->send($request)->getBody(),
            'SensioLabs\Insight\Sdk\Model\Analyses',
            'xml'
        );
    }

    public function getAnalysis($projectUuid, $analysesNumber)
    {
        $request = $this->client->createRequest('GET', sprintf('/api/projects/%s/analyses/%s', $projectUuid, $analysesNumber));

        return $this->serializer->deserialize(
            (string) $this->send($request)->getBody(),
            'SensioLabs\Insight\Sdk\Model\Analysis',
            'xml'
        );
    }

    public function getAnalysisStatus($projectUuid, $analysesNumber)
    {
        $request = $this->client->createRequest('GET', sprintf('/api/projects/%s/analyses/%s/status', $projectUuid, $analysesNumber));

        return $this->serializer->deserialize(
            (string) $this->send($request)->getBody(),
            'SensioLabs\Insight\Sdk\Model\Analysis',
            'xml'
        );
    }

    public function triggerAnalyse($projectUuid)
    {
        $request = $this->client->createRequest('POST', sprintf('/api/projects/%s/analyses', $projectUuid));

        return $this->serializer->deserialize(
            (string) $this->send($request)->getBody(),
            'SensioLabs\Insight\Sdk\Model\Analysis',
            'xml'
        );
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    public function getSerializer()
    {
        return $this->serializer;
    }

    private function send(Request $request)
    {
        try {
            $this->logger and $this->logger->debug(sprintf('%s "%s"', $request->getMethod(), $request->getUrl()));
            $this->logger and $this->logger->debug(sprintf("Request:\n%s", (string) $request));
            $response = $request->send();
            $this->logger and $this->logger->debug(sprintf("Response:\n%s", (string) $response));

            return $response;
        } catch (ClientErrorResponseException $e) {
            $this->logException($e);

            $this->processClientError($e);
        } catch (BadResponseException $e) {
            $this->logException($e);

            throw new ApiServerException('Something went wrong with upstream', 0, $e);
        }
    }

    private function processClientError(ClientErrorResponseException $e)
    {
        $statusCode = $e->getResponse()->getStatusCode();
        $reasonPhrase = $e->getResponse()->getReasonPhrase();

        $error = null;
        $message = sprintf('Your request in not valid (status code: "%d", reason phrase: "%s").', $statusCode, $reasonPhrase);

        if (400 == $statusCode) {
            $error = $this->parser->parseError((string) $e->getResponse()->getBody());
            $message .= 'See $error attached to the exception';
        }

        throw new ApiClientException($message, $error, 0, $e);
    }

    private function logException(\Exception $e)
    {
        $message = sprintf("Exception: Class: \"%s\", Message: \"%s\", Response:\n%s",
            get_class($e),
            $e->getMessage(),
            (string) $e->getResponse()
        );
        $this->logger and $this->logger->error($message, array('exception' => $e));
    }
}
