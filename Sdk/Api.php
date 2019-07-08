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

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\ScopingHttpClient;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Psr\Log\LoggerInterface;
use SensioLabs\Insight\Sdk\Exception\ApiClientException;
use SensioLabs\Insight\Sdk\Exception\ApiServerException;
use SensioLabs\Insight\Sdk\Model\Analyses;
use SensioLabs\Insight\Sdk\Model\Analysis;
use SensioLabs\Insight\Sdk\Model\Project;
use SensioLabs\Insight\Sdk\Model\Projects;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Api
{
    const ENDPOINT = 'https://insight.sensiolabs.com';

    private $httpClient;
    private $serializer;
    private $parser;
    private $logger;

    public function __construct(array $options = array(), HttpClientInterface $httpClient = null, Parser $parser = null, LoggerInterface $logger = null)
    {
        $this->httpClient = $httpClient ?: new HttpClient();
        $this->parser = $parser ?: new Parser();

        $defaultOptions = array(
            'base_url' => static::ENDPOINT,
            'cache' => false,
            'debug' => false,
        );
        $required = array('api_token', 'base_url', 'user_uuid');
        $options = array_merge($defaultOptions, $options);
        if ($missing = array_diff($required, array_keys($options))) {
            throw new \Exception('Config is missing the following keys: ' . implode(', ', $missing));
        }

        $this->httpClient = new ScopingHttpClient(
            $httpClient,
            [
                '.+' => [
                    'base_uri' => $options['base_url'],
                    'auth_basic' =>  array($options['user_uuid'], $options['api_token']),
                    'headers' => array('accept' => 'application/vnd.com.sensiolabs.insight+xml')
                ],
            ],
            '.+'
        );

        $serializerBuilder = SerializerBuilder::create()
            ->addMetadataDir(__DIR__.'/Model')
            ->setDebug($options['debug'])
        ;
        if ($cache = $options['cache']) {
            $serializerBuilder = $serializerBuilder->setCacheDir($cache);
        }
        $this->serializer = $serializerBuilder->build();

        $this->logger = $logger;
    }

    /**
     * @param int $page
     *
     * @return Projects
     */
    public function getProjects($page = 1)
    {
        return $this->serializer->deserialize(
            $this->send('GET', '/api/projects?page='.$page),
            'SensioLabs\Insight\Sdk\Model\Projects',
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
        return $this->serializer->deserialize(
            $this->send('GET', sprintf('/api/projects/%s', $uuid)),
            'SensioLabs\Insight\Sdk\Model\Project',
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
        return $this->serializer->deserialize(
            $this->send('PUT', sprintf('/api/projects/%s', $project->getUuid()), array('insight_project' => $project->toArray())),
            'SensioLabs\Insight\Sdk\Model\Project',
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
        return $this->serializer->deserialize(
            $this->send('POST', '/api/projects', array('insight_project' => $project->toArray())),
            'SensioLabs\Insight\Sdk\Model\Project',
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
        return $this->serializer->deserialize(
            $this->send('GET', sprintf('/api/projects/%s/analyses', $projectUuid)),
            'SensioLabs\Insight\Sdk\Model\Analyses',
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
        return $this->serializer->deserialize(
            $this->send('GET', sprintf('/api/projects/%s/analyses/%s', $projectUuid, $analysesNumber),null),
            'SensioLabs\Insight\Sdk\Model\Analysis',
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
        return $this->serializer->deserialize(
            $this->send('GET', sprintf('/api/projects/%s/analyses/%s/status', $projectUuid, $analysesNumber)),
            'SensioLabs\Insight\Sdk\Model\Analysis',
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
        return $this->serializer->deserialize(
            $this->send(
                'POST',
                sprintf('/api/projects/%s/analyses', $projectUuid),
                $branch ? array('reference' => $reference, 'branch' => $branch) : array('reference' => $reference)
            ),
            'SensioLabs\Insight\Sdk\Model\Analysis',
            'xml'
        );
    }

    /**
     * Use this method to call a specific API resource.
     */
    public function call($method = 'GET', $uri = null, $headers = null, $body = null, array $options = array(), $classToUnserialize = null)
    {
        if ($classToUnserialize) {
            return $this->serializer->deserialize(
                $this->send($method, $uri, $body),
                $classToUnserialize,
                'xml'
            );
        }

        return $this->send($method, $uri, $body);
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

    private function send($method, $url, $body = null)
    {
        try {
            $option = [];
            if($body){
                $option['body'] = $body;
            }
            $this->logger and $this->logger->debug(sprintf('%s "%s"', $method, $url));
            $response = $this->httpClient->request($method, $url,$option);
            // block until headers arrive
            $response->getStatusCode();
            $this->logger and $this->logger->debug(sprintf("Request:\n%s", (string) $response->getInfo('debug')));

            return $response->getContent();
        } catch (ClientExceptionInterface $e) {
            $this->logException($e);

            $this->processClientError($e);
        } catch (TransportExceptionInterface $e) {
            $this->logException($e);

            throw new ApiServerException('Something went wrong with upstream', 0, $e);
        } catch (ServerExceptionInterface $e) {
            $this->logException($e);

            throw new ApiServerException('Something went wrong with upstream', 0, $e);
        }
    }

    private function processClientError(HttpExceptionInterface $e)
    {
        $statusCode = $e->getResponse()->getStatusCode();
        $error = null;
        $message = sprintf('Your request in not valid (status code: "%d").', $statusCode);

        if (400 == $statusCode) {
            $error = $this->parser->parseError((string) $e->getResponse()->getContent(false));
            $message .= 'See $error attached to the exception';
        }

        throw new ApiClientException($message, $error, 0, $e);
    }

    private function logException(HttpExceptionInterface $e)
    {
        $message = sprintf("Exception: Class: \"%s\", Message: \"%s\", Response:\n%s",
            get_class($e),
            $e->getMessage(),
            $e->getResponse()->getInfo('debug')
        );
        $this->logger and $this->logger->error($message, array('exception' => $e));
    }
}
