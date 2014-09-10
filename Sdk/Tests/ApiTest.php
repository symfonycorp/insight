<?php

/*
 * This file is part of the SensioLabsInsight package.
 *
 * (c) SensioLabs <contact@sensiolabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Insight\Sdk\Tests;

use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;
use SensioLabs\Insight\Sdk\Api;
use SensioLabs\Insight\Sdk\Model\Project;

class ApiTest extends \PHPUnit_Framework_TestCase
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

        $this->logger = $this->getMock('Psr\Log\LoggerInterface');

        $this->api = new Api(array('api_token' => 'my-token', 'user_uuid' => 'my-user-uuid'), $client, null, $this->logger);
    }

    /**
     * @expectedException Guzzle\Common\Exception\InvalidArgumentException
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

        $this->assertInstanceOf('SensioLabs\Insight\Sdk\Model\Projects', $projects);
        $this->assertCount(10, $projects->getProjects());
        $this->assertSame(1, $projects->getPage());
        $this->assertSame(12, $projects->getTotal());
        $this->assertSame(10, $projects->getLimit());

        $projects = $projects->getProjects();
        $this->assertInstanceOf('SensioLabs\Insight\Sdk\Model\Project', reset($projects));
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

        $this->assertInstanceOf('SensioLabs\Insight\Sdk\Model\Projects', $projects);
        $this->assertCount(2, $projects->getProjects());
        $this->assertSame(2, $projects->getPage());
        $this->assertSame(12, $projects->getTotal());
        $this->assertSame(10, $projects->getLimit());

        $projects = $projects->getProjects();
        $this->assertInstanceOf('SensioLabs\Insight\Sdk\Model\Project', reset($projects));
    }

    public function testGetProject()
    {
        $this->pluginMockResponse->addResponse($this->createResponse('project'));
        $project = $this->api->getProject('6718526f-ecdf-497d-bffb-8512f0b402ea');

        $this->assertInstanceOf('SensioLabs\Insight\Sdk\Model\Project', $project);
        $this->assertSame('demo', $project->getName());
        $this->assertNotnull($project->getConfiguration());
        $this->assertSame('git@github.com:lyrixx/demoer.git', $project->getRepositoryUrl());
        $this->assertTrue($project->isPublic());
        $this->assertTrue($project->isReportAvailable());
        $this->assertSame(1, $project->getType());

        $this->assertInstanceOf('SensioLabs\Insight\Sdk\Model\Analysis', $project->getLastAnalysis());
    }

    public function testCreateProjectOk()
    {
        $project = new Project();

        $this->pluginMockResponse->addResponse($this->createResponse('project'));
        $project = $this->api->createProject($project);

        $this->assertInstanceOf('SensioLabs\Insight\Sdk\Model\Project', $project);
    }

    public function testCreateProjectNOk()
    {
        $project = new Project();

        $this->pluginMockResponse->addResponse($this->createResponse('errors', 400));
        try {
            $project = $this->api->createProject($project);
            $this->fail('Something should go wrong');
        } catch (\Exception $e) {
            $this->assertInstanceOf('SensioLabs\Insight\Sdk\Exception\ApiClientException', $e);
            $this->assertSame('Your request in not valid (status code: "400", reason phrase: "Bad Request").See $error attached to the exception', $e->getMessage());
            $this->assertInstanceOf('SensioLabs\Insight\Sdk\Model\Error', $e->getError());
        }
    }

    public function testupdateProjectOk()
    {
        $project = new Project();

        $this->pluginMockResponse->addResponse($this->createResponse('project'));
        $project = $this->api->updateProject($project);

        $this->assertInstanceOf('SensioLabs\Insight\Sdk\Model\Project', $project);
    }

    public function testupdateProjectNOk()
    {
        $project = new Project();

        $this->pluginMockResponse->addResponse($this->createResponse('errors', 400));
        try {
            $project = $this->api->updateProject($project);
            $this->fail('Something should go wrong');
        } catch (\Exception $e) {
            $this->assertInstanceOf('SensioLabs\Insight\Sdk\Exception\ApiClientException', $e);
            $this->assertSame('Your request in not valid (status code: "400", reason phrase: "Bad Request").See $error attached to the exception', $e->getMessage());
            $this->assertInstanceOf('SensioLabs\Insight\Sdk\Model\Error', $e->getError());
        }
    }

    public function testGetAnalyses()
    {
        $this->pluginMockResponse->addResponse($this->createResponse('analyses'));
        $analyses = $this->api->getAnalyses('6718526f-ecdf-497d-bffb-8512f0b402ea');

        $this->assertInstanceOf('SensioLabs\Insight\Sdk\Model\Analyses', $analyses);
        $this->assertCount(2, $analyses->getAnalyses());

        $analyses = $analyses->getAnalyses();
        $this->assertInstanceOf('SensioLabs\Insight\Sdk\Model\Analysis', reset($analyses));
    }

    public function testGetAnalysis()
    {
        $this->pluginMockResponse->addResponse($this->createResponse('analysis'));
        $analysis = $this->api->getAnalysis('6718526f-ecdf-497d-bffb-8512f0b402ea', 1);

        $this->assertInstanceOf('SensioLabs\Insight\Sdk\Model\Analysis', $analysis);
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
        $this->assertInstanceOf('SensioLabs\Insight\Sdk\Model\Violations', $analysis->getViolations());
        $this->assertCount(250, $analysis->getViolations()->getViolations());

        // Check configuration
        $configuration = $analysis->getConfiguration();
        $this->assertEquals(array('gh-pages'), $configuration->getIgnoredBranches());
        $this->assertEquals('echo "Pre composer script!"', $configuration->getPreComposerScript());
        $this->assertEquals('echo "Post composer script!"', $configuration->getPostComposerScript());
        $this->assertEquals("extension=openssl.so\nextension=mcrypt.so\n", $configuration->getPhpIni());
        $this->assertEquals(array(
            'vendor',
            'vendors',
            'test',
            'tests',
            'Tests',
            'spec',
            'features',
            'Fixtures',
            'DataFixtures',
            'var',
        ), $configuration->getGlobalExcludedDirs());

        $this->assertEquals(array(
            'app/check.php',
            'app/SymfonyRequirements.php',
            'web/config.php',
            'web/app_*.php',
        ), $configuration->getExcludedPatterns());

        $this->assertEquals(array(
            'file' => array('*.yml', 'composer.*', '*.xml', '*.yaml'),
            'php'  => array('*.php'),
            'twig' => array('*.twig'),
        ), $configuration->getPatterns());

        $this->assertEquals(array('project_type' => Project::TYPE_SYMFONY2_WEB_PROJECT), $configuration->getParameters());
        $this->assertEquals(array(
            'composer.apc_class_loader_should_be_enabled' => array('enabled' => false),
            'php.class_too_long' => array('enabled' => true, 'max_length' => '500', 'threshold' => '5'),
            'php.absolute_path_present' => array('enabled' => true, 'allowed_paths' => array('/dev', '/etc', '/proc')),
        ), $configuration->getRules());

        $this->assertEquals(array(3 => 'abcdef', 2 => 'ghijkl', 1 => 'mnopqr'), $analysis->getPreviousAnalysesReferences());

        $violations = $analysis->getViolations()->getViolations();
        $firstViolation = reset($violations);
        $this->assertInstanceOf('SensioLabs\Insight\Sdk\Model\Violation', $firstViolation);

        $this->assertSame(7, $firstViolation->getLine());
        $this->assertSame('critical', $firstViolation->getSeverity());
        $this->assertSame('security', $firstViolation->getCategory());
        $this->assertSame('snippets/001-HelloWorld.php', $firstViolation->getResource());
    }

    public function testGetAnalysisStatus()
    {
        $this->pluginMockResponse->addResponse($this->createResponse('status'));
        $analysis = $this->api->getAnalysisStatus('6718526f-ecdf-497d-bffb-8512f0b402ea', 1);

        $this->assertInstanceOf('SensioLabs\Insight\Sdk\Model\Analysis', $analysis);
        $this->assertSame(49, $analysis->getNumber());
        $this->assertSame('2013-06-25T19:37:20+02:00', $analysis->getBeginAt()->format('c'));
        $this->assertSame('2013-06-25T19:37:53+02:00', $analysis->getEndAt()->format('c'));
        $this->assertSame('finished', $analysis->getStatus());
    }

    public function testAnalyze()
    {
        $this->pluginMockResponse->addResponse($this->createResponse('analysis'));
        $analysis = $this->api->analyze('6718526f-ecdf-497d-bffb-8512f0b402ea', 'SHA');

        $this->assertInstanceOf('SensioLabs\Insight\Sdk\Model\Analysis', $analysis);
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
