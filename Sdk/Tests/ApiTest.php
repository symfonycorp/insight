<?php

/*
 * This file is part of the SensioLabsInsight package.
 *
 * (c) SensioLabs <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCorp\Insight\Sdk\Tests;

use PHPUnit\Framework\TestCase;
use SymfonyCorp\Insight\Sdk\Api;
use SymfonyCorp\Insight\Sdk\Model\Project;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class ApiTest extends TestCase
{
    /**
     * @var Api
     */
    private $api;
    private $logger;

    protected function setUp(): void
    {
        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
    }

    public function testConstructorWithoutOption()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Config is missing the following keys: api_token, user_uuid');
        new Api();
    }

    public function testGetProjects()
    {
        $api = $this->createApi('projects');

        $projects = $api->getProjects();

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
            ->expects($this->exactly(2))
            ->method('debug')
        ;
        $this->logger
            ->expects($this->at(1))
            ->method('debug')
            ->with($this->stringContains('/api/projects?page=2'))
        ;
        $api = $this->createApi('projects2', ['debug' => '/api/projects?page=2']);

        $projects = $api->getProjects(2);

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
        $api = $this->createApi('project');

        $project = $api->getProject('6718526f-ecdf-497d-bffb-8512f0b402ea');

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

        $api = $this->createApi('project');

        $project = $api->createProject($project);

        $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Project', $project);
    }

    public function testCreateProjectNOk()
    {
        $project = new Project();

        $api = $this->createApi('errors', ['http_code' => 400]);

        try {
            $project = $api->createProject($project);
            $this->fail('Something should go wrong');
        } catch (\Exception $e) {
            $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Exception\ApiClientException', $e);
            $this->assertSame('Your request in not valid (status code: "400").See $error attached to the exception', $e->getMessage());
            $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Error', $e->getError());
        }
    }

    public function testupdateProjectOk()
    {
        $project = new Project();

        $api = $this->createApi('project');

        $project = $api->updateProject($project);

        $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Project', $project);
    }

    public function testupdateProjectNOk()
    {
        $project = new Project();
        $api = $this->createApi('errors', ['http_code' => 400]);

        try {
            $project = $api->updateProject($project);
            $this->fail('Something should go wrong');
        } catch (\Exception $e) {
            $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Exception\ApiClientException', $e);
            $this->assertSame('Your request in not valid (status code: "400").See $error attached to the exception', $e->getMessage());
            $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Error', $e->getError());
        }
    }

    public function testGetAnalyses()
    {
        $api = $this->createApi('analyses');

        $analyses = $api->getAnalyses('6718526f-ecdf-497d-bffb-8512f0b402ea');

        $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Analyses', $analyses);
        $this->assertCount(2, $analyses->getAnalyses());

        $analyses = $analyses->getAnalyses();
        $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Analysis', reset($analyses));
    }

    public function testGetAnalysis()
    {
        $api = $this->createApi('analysis');

        $analysis = $api->getAnalysis('6718526f-ecdf-497d-bffb-8512f0b402ea', 1);

        $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Analysis', $analysis);
        $this->assertSame(49, $analysis->getNumber());
        $this->assertSame('error', $analysis->getGrade());
        $this->assertSame('bronze', $analysis->getNextGrade());
        $this->assertSame(['error', 'bronze', 'silver', 'gold', 'platinum'], $analysis->getGrades());
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
        $api = $this->createApi('status');

        $analysis = $api->getAnalysisStatus('6718526f-ecdf-497d-bffb-8512f0b402ea', 1);

        $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Analysis', $analysis);
        $this->assertSame(49, $analysis->getNumber());
        $this->assertSame('2013-06-25T19:37:20+02:00', $analysis->getBeginAt()->format('c'));
        $this->assertSame('2013-06-25T19:37:53+02:00', $analysis->getEndAt()->format('c'));
        $this->assertSame('finished', $analysis->getStatus());
    }

    public function testAnalyze()
    {
        $api = $this->createApi('analysis');

        $analysis = $api->analyze('6718526f-ecdf-497d-bffb-8512f0b402ea', 'SHA');

        $this->assertInstanceOf('SymfonyCorp\Insight\Sdk\Model\Analysis', $analysis);
    }

    protected function tearDown(): void
    {
        $this->logger = null;
        $this->api = null;
        $this->pluginMockResponse = null;
    }

    private function createResponse($fixture, $statusCode = 200)
    {
        return file_get_contents(sprintf('%s/fixtures/%s.xml', __DIR__, $fixture));
    }

    private function createApi($fixture, $option = [])
    {
        $client = new MockHttpClient([new MockResponse($this->createResponse($fixture), $option)]);

        return new Api(['api_token' => 'my-token', 'user_uuid' => 'my-user-uuid'], $client, null, $this->logger);
    }
}
