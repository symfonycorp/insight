<?php

/*
 * This file is part of the SymfonyInsight package.
 *
 * (c) Symfony <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCorp\Insight\Sdk\Tests;

use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;
use PHPUnit\Framework\TestCase;
use SymfonyCorp\Insight\Sdk\Api;
use SymfonyCorp\Insight\Sdk\Model\Project;

class ApiTest extends TestCase
{
    /**
     * @var Api
     */
    private $api;
    private $logger;

    /**
     * @var MockPlugin
     */
    private $pluginMockResponse;

    public function setUp()
    {
        $this->pluginMockResponse = new MockPlugin();
        $client = new Client();
        $client->addSubscriber($this->pluginMockResponse);

        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();

        $this->api = new Api(array('api_token' => 'my-token', 'user_uuid' => 'my-user-uuid'), $client, null, $this->logger);
    }

    /**
     * @expectedException \Guzzle\Common\Exception\InvalidArgumentException
     * @expectedExceptionMessage Config is missing the following keys: api_token, user_uuid
     */
    public function testConstructorWithoutOption()
    {
        $api = new Api();
    }

    public function testGetProjects()
    {
        $this->pluginMockResponse->addResponse($this->createResponse('projects'));
        $projects = $this->api->getProjects();

        $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Projects', $projects);
        $this->assertCount(10, $projects->getProjects());
        $this->assertSame(1, $projects->getPage());
        $this->assertSame(12, $projects->getTotal());
        $this->assertSame(10, $projects->getLimit());

        $projects = $projects->getProjects();
        $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Project', reset($projects));
    }

    public function testGetProjectsWithPage()
    {
        $this->logger
            ->expects($this->exactly(3))
            ->method('debug')
        ;
        $this->logger
            ->expects($this->at(1))
            ->method('debug')
            ->with($this->stringContains('/api/projects?page=2'))
        ;

        $this->pluginMockResponse->addResponse($this->createResponse('projects2'));
        $projects = $this->api->getProjects(2);

        $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Projects', $projects);
        $this->assertCount(2, $projects->getProjects());
        $this->assertSame(2, $projects->getPage());
        $this->assertSame(12, $projects->getTotal());
        $this->assertSame(10, $projects->getLimit());

        $projects = $projects->getProjects();
        $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Project', reset($projects));
    }

    public function testGetProject()
    {
        $this->pluginMockResponse->addResponse($this->createResponse('project'));
        $project = $this->api->getProject('6718526f-ecdf-497d-bffb-8512f0b402ea');

        $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Project', $project);
        $this->assertSame('demo', $project->getName());
        $this->assertNotnull($project->getConfiguration());
        $this->assertSame('git@github.com:lyrixx/demoer.git', $project->getRepositoryUrl());
        $this->assertTrue($project->isPublic());
        $this->assertTrue($project->isReportAvailable());
        $this->assertSame(1, $project->getType());

        $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Analysis', $project->getLastAnalysis());
    }

    public function testCreateProjectOk()
    {
        $project = new Project();

        $this->pluginMockResponse->addResponse($this->createResponse('project'));
        $project = $this->api->createProject($project);

        $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Project', $project);
    }

    public function testCreateProjectNOk()
    {
        $project = new Project();

        $this->pluginMockResponse->addResponse($this->createResponse('errors', 400));
        try {
            $project = $this->api->createProject($project);
            $this->fail('Something should go wrong');
        } catch (\Exception $e) {
            $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Exception\ApiClientException', $e);
            $this->assertSame('Your request in not valid (status code: "400", reason phrase: "Bad Request").See $error attached to the exception', $e->getMessage());
            $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Error', $e->getError());
        }
    }

    public function testupdateProjectOk()
    {
        $project = new Project();

        $this->pluginMockResponse->addResponse($this->createResponse('project'));
        $project = $this->api->updateProject($project);

        $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Project', $project);
    }

    public function testupdateProjectNOk()
    {
        $project = new Project();

        $this->pluginMockResponse->addResponse($this->createResponse('errors', 400));
        try {
            $project = $this->api->updateProject($project);
            $this->fail('Something should go wrong');
        } catch (\Exception $e) {
            $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Exception\ApiClientException', $e);
            $this->assertSame('Your request in not valid (status code: "400", reason phrase: "Bad Request").See $error attached to the exception', $e->getMessage());
            $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Error', $e->getError());
        }
    }

    public function testGetAnalyses()
    {
        $this->pluginMockResponse->addResponse($this->createResponse('analyses'));
        $analyses = $this->api->getAnalyses('6718526f-ecdf-497d-bffb-8512f0b402ea');

        $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Analyses', $analyses);
        $this->assertCount(2, $analyses->getAnalyses());

        $analyses = $analyses->getAnalyses();
        $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Analysis', reset($analyses));
    }

    public function testGetAnalysis()
    {
        $this->pluginMockResponse->addResponse($this->createResponse('analysis'));
        $analysis = $this->api->getAnalysis('6718526f-ecdf-497d-bffb-8512f0b402ea', 1);

        $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Analysis', $analysis);
        $this->assertSame(49, $analysis->getNumber());
        $this->assertSame('error', $analysis->getGrade());
        $this->assertSame('bronze', $analysis->getNextGrade());
        $this->assertSame(array('error', 'bronze', 'silver', 'gold', 'platinum'), $analysis->getGrades());
        $this->assertSame(181.75, $analysis->getRemediationCost());
        $this->assertSame(55.5, $analysis->getRemediationCostForNextGrade());
        $this->assertSame('2013-06-25T19:37:20+02:00', $analysis->getBeginAt()->format('c'));
        $this->assertSame('2013-06-25T19:37:53+02:00', $analysis->getEndAt()->format('c'));
        $this->assertSame('0', $analysis->getDuration()->format('%s'));
        $this->assertSame(250, $analysis->getNbViolations());
        $this->assertNull($analysis->getFailureMessage());
        $this->assertNull($analysis->getFailureCode());
        $this->assertFalse($analysis->isAltered());
        $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Violations', $analysis->getViolations());
        $this->assertCount(250, $analysis->getViolations()->getViolations());

        $violations = $analysis->getViolations()->getViolations();
        $firstViolation = reset($violations);
        $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Violation', $firstViolation);

        $this->assertSame(7, $firstViolation->getLine());
        $this->assertSame('critical', $firstViolation->getSeverity());
        $this->assertSame('security', $firstViolation->getCategory());
        $this->assertSame('snippets/001-HelloWorld.php', $firstViolation->getResource());
    }

    public function testGetAnalysisStatus()
    {
        $this->pluginMockResponse->addResponse($this->createResponse('status'));
        $analysis = $this->api->getAnalysisStatus('6718526f-ecdf-497d-bffb-8512f0b402ea', 1);

        $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Analysis', $analysis);
        $this->assertSame(49, $analysis->getNumber());
        $this->assertSame('2013-06-25T19:37:20+02:00', $analysis->getBeginAt()->format('c'));
        $this->assertSame('2013-06-25T19:37:53+02:00', $analysis->getEndAt()->format('c'));
        $this->assertSame('finished', $analysis->getStatus());
    }

    public function testAnalyze()
    {
        $this->pluginMockResponse->addResponse($this->createResponse('analysis'));
        $analysis = $this->api->analyze('6718526f-ecdf-497d-bffb-8512f0b402ea', 'SHA');

        $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Analysis', $analysis);
    }

    public function tearDown()
    {
        $this->logger = null;
        $this->api = null;
        $this->pluginMockResponse = null;
    }

    private function createResponse($fixture, $statusCode = 200)
    {
        return new Response($statusCode, null, file_get_contents(sprintf('%s/fixtures/%s.xml', __DIR__, $fixture)));
    }
}
