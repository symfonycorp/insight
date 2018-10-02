<?php

/*
 * This file is part of the SymfonyInsight package.
 *
 * (c) Symfony <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCorp\Insight\Sdk;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Guzzle\Common\Collection;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Message\RequestInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Psr\Log\LoggerInterface;
use SymfonyCorp\Insight\Sdk\Exception\ApiClientException;
use SymfonyCorp\Insight\Sdk\Exception\ApiServerException;
use SymfonyCorp\Insight\Sdk\Model\Analyses;
use SymfonyCorp\Insight\Sdk\Model\Analysis;
use SymfonyCorp\Insight\Sdk\Model\Project;
use SymfonyCorp\Insight\Sdk\Model\Projects;

class Api
{
    const ENDPOINT = 'https://insight.symfony.com';

    private $client;
    private $serializer;
    private $parser;
    private $logger;

    public function __construct(array $options = array(), Client $client = null, Parser $parser = null, LoggerInterface $logger = null)
    {
        $sslAuthority = defined('PHP_WINDOWS_VERSION_BUILD') ? true : 'system'; // The system certs cannot be found by curl on Windows.
        $this->client = $client ?: new Client('', array(Client::SSL_CERT_AUTHORITY => $sslAuthority));
        $this->parser = $parser ?: new Parser();

        $defaultOptions = array(
            'base_url' => static::ENDPOINT,
            'cache' => false,
            'debug' => false,
        );
        $requiredOptions = array('api_token', 'base_url', 'user_uuid');
        $options = Collection::fromConfig($options, $defaultOptions, $requiredOptions);
        $this->client->getConfig()->merge($options);

        $this->client->setBaseUrl($options->get('base_url'));
        $this->client->setDefaultHeaders(array(
            'accept' => 'application/vnd.com.sensiolabs.insight+xml',
        ));
        $this->client->setDefaultOption('auth', array($options['user_uuid'], $options['api_token'], 'Basic'));

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

    /**
     * @param int $page
     *
     * @return Projects
     */
    public function getProjects($page = 1)
    {
        $request = $this->client->createRequest('GET', '/api/projects');
        $url = $request->getUrl(true);
        $url->getQuery()->set('page', (int) $page);
        $request->setUrl($url);

        return $this->serializer->deserialize(
            (string) $this->send($request)->getBody(),
            'SymfonyCorp\Insight\Sdk\Model\Projects',
            'xml'
        );
    }

    /**
     * @param string $uuid
     *
     * @return Project
     */
    public function getProject($uuid)
    {
        $request = $this->client->createRequest('GET', sprintf('/api/projects/%s', $uuid));

        return $this->serializer->deserialize(
            (string) $this->send($request)->getBody(),
            'SymfonyCorp\Insight\Sdk\Model\Project',
            'xml'
        );
    }

    /**
     * @param Project $project
     *
     * @return Project
     */
    public function updateProject(Project $project)
    {
        $request = $this->client->createRequest('PUT', sprintf('/api/projects/%s', $project->getUuid()), null, array('insight_project' => $project->toArray()));

        return $this->serializer->deserialize(
            (string) $this->send($request)->getBody(),
            'SymfonyCorp\Insight\Sdk\Model\Project',
            'xml'
        );
    }

    /**
     * @param Project $project
     *
     * @return Project
     */
    public function createProject(Project $project)
    {
        $request = $this->client->createRequest('POST', '/api/projects', null, array('insight_project' => $project->toArray()));

        return $this->serializer->deserialize(
            (string) $this->send($request)->getBody(),
            'SymfonyCorp\Insight\Sdk\Model\Project',
            'xml'
        );
    }

    /**
     * @param string $projectUuid
     *
     * @return Analyses
     */
    public function getAnalyses($projectUuid)
    {
        $request = $this->client->createRequest('GET', sprintf('/api/projects/%s/analyses', $projectUuid));

        return $this->serializer->deserialize(
            (string) $this->send($request)->getBody(),
            'SymfonyCorp\Insight\Sdk\Model\Analyses',
            'xml'
        );
    }

    /**
     * @param string $projectUuid
     * @param int    $analysesNumber
     *
     * @return Analysis
     */
    public function getAnalysis($projectUuid, $analysesNumber)
    {
        $request = $this->client->createRequest('GET', sprintf('/api/projects/%s/analyses/%s', $projectUuid, $analysesNumber));

        return $this->serializer->deserialize(
            (string) $this->send($request)->getBody(),
            'SymfonyCorp\Insight\Sdk\Model\Analysis',
            'xml'
        );
    }

    /**
     * @param string $projectUuid
     * @param int    $analysesNumber
     *
     * @return Analysis an incomplete Analysis object
     */
    public function getAnalysisStatus($projectUuid, $analysesNumber)
    {
        $request = $this->client->createRequest('GET', sprintf('/api/projects/%s/analyses/%s/status', $projectUuid, $analysesNumber));

        return $this->serializer->deserialize(
            (string) $this->send($request)->getBody(),
            'SymfonyCorp\Insight\Sdk\Model\Analysis',
            'xml'
        );
    }

    /**
     * @param string      $projectUuid
     * @param string|null $reference   A git reference. It can be a commit sha, a tag name or a branch name
     * @param string|null $branch      Current analysis branch, used by SymfonyInsight to distinguish between the main branch and PRs
     *
     * @return Analysis
     */
    public function analyze($projectUuid, $reference = null, $branch = null)
    {
        $request = $this->client->createRequest(
            'POST',
            sprintf('/api/projects/%s/analyses', $projectUuid),
            array(),
            $branch ? array('reference' => $reference, 'branch' => $branch) : array('reference' => $reference)
        );

        return $this->serializer->deserialize(
            (string) $this->send($request)->getBody(),
            'SymfonyCorp\Insight\Sdk\Model\Analysis',
            'xml'
        );
    }

    /**
     * Use this method to call a specific API resource.
     */
    public function call($method = 'GET', $uri = null, $headers = null, $body = null, array $options = array(), $classToUnserialize = null)
    {
        $request = $this->client->createRequest($method, $uri, $headers, $body, $options);

        if ($classToUnserialize) {
            return $this->serializer->deserialize(
                (string) $this->send($request)->getBody(),
                $classToUnserialize,
                'xml'
            );
        }

        return $this->send($request);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @return Serializer
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    private function send(RequestInterface $request)
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

    private function logException(BadResponseException $e)
    {
        $message = sprintf("Exception: Class: \"%s\", Message: \"%s\", Response:\n%s",
            get_class($e),
            $e->getMessage(),
            (string) $e->getResponse()
        );
        $this->logger and $this->logger->error($message, array('exception' => $e));
    }
}
