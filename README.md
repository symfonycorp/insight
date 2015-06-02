SensioLabsInsight SDK
=====================

About
-----

This is the official SDK for the SensioLabsInsight API.

Installation
------------

To install the SDK, run the command below and you will get the latest version:

    composer require sensiolabs/insight

Command Line Tool
-----------------

The easiest way to use the SensioLabsInsight API is via the built-in command
line tool.

A phar version of the command line tool exists to avoid installation of this
project. Download it, then use it like the command line tool:

    $ curl -o insight.phar -s http://get.insight.sensiolabs.com/insight.phar
    # or
    $ wget http://get.insight.sensiolabs.com/insight.phar

    # Then
    $ php insight.phar

List all the projects in your account:

    $ php insight.phar projects

The first time, you will be prompted for your SensioLabsInsight API key and
user UUID (which can be found under the "Account" section on the website).
These information are then stored locally, but can still be overridden via the
`--api-token` and `--user-uuid` options.

To run an analysis:

    $ php insight.phar analyze UUID

where `UUID` is the UUID of the project you want to analyze (the UUIDs are
listed by the `projects` command).

To export an analysis report:

    $ php insight.phar analysis UUID --format="xml" # or --format="json" or --format="pmd"

To check violations between commits:
```shell
# Current directory is the default repository and head commit is the default commit
php insight.phar check-violations UUID

php insight.phar check-violations UUID --repository='/path/to/git/repository'

php insight.phar check-violations UUID --repository='/path/to/git/repository' --commits 722a291a6fdffe7b28f15e7b5edd6a8dc4768934

# Possibility to check multiple commits hashes
php insight.phar check-violations UUID --repository='/path/to/git/repository' --commits 722a291a6fdffe7b28f15e7b5edd6a8dc4768934 1d8ca769395071fc0807e07d3c9290464c8bf593
```

Configuration
-------------

    use SensioLabs\Insight\Sdk\Api;

    $api = new Api(array(
        'api_token' => 'your api token',
        'user_uuid' => 'your user uuid',
    ));

If you want, you can give a `Guzzle\Http\Client` and a
`Psr\Log\LoggerInterface` to this library:

    use Guzzle\Http\Client;
    use Monolog\Logger;
    use Monolog\Handler\StreamHandler;

    $config = array(
        'api_token' => 'your api token',
        'user_uuid' => 'your user uuid',
    )

    $client = new Client();

    $logger = new Logger('insight-sdk');
    $logger->pushHandler(new StreamHandler(__DIR__.'/insight-sdk.log', Logger::DEBUG));

    $api = new Api($config, $client, null, $logger);

You can also give a `cache` folder. The SDK will only cache metadatas for
serialization. And you can also give a `debug` flag:

    $api = new Api(array(
        'api_token' => 'your api token',
        'user_uuid' => 'your user uuid',
        'cache'     => __DIR__.'/cache/insight',
        'debug'     => true,
    ));

Usage
-----

### List all projects:

    $api->getProjects();
    $api->getProjects(2); // For the second page

### Get a project:

    $project = $api->getProject('project uuid');

### Update a project

    $api->updateProject($project);

Note: If something went wrong, see *Error management* section

### Post a project

    use SensioLabs\Insight\Sdk\Model\Project;

    $project = new Project();
    $project
        ->setName('Foo')
        ->setDescription('Foo')
        ->setType(TYPE_WEBSITE::TYPE_WEBSITE)
    ;

    $api->createProject($project)

Note: If something went wrong, see *Error management* section

### Run an analysis

    // on the default branch
    $api->analyze('project uuid', 'master');

    // for a specific branch or reference
    $api->analyze('project uuid', '1.0');

### Get all analyses

    $api->getAnalyses('project uuid');

### Get an analysis

    $api->getAnalysis('project uuid', 'analysis id');

### Get a status analysis

    $api->getAnalysisStatus('project uuid', 'analysis id');

### Error management

If something went wrong, an
`SensioLabs\Insight\Sdk\Exception\ExceptionInterface` will be throw:

* `ApiClientException` If you did something wrong. This exception contains the
  previous exception throw by guzzle. You can easily check if it is a:
  * 403: In this case, check your credentials
  * 404: In this case, check your request
  * 400: In this case, check the data sent. In this case, the Exception will
    contains a `SensioLabs\Insight\Sdk\Model\Error` object. Which will contains
    all form errors.
* `ApiServerException` If something went wrong with the API.

Jenkins/Hudson Integration
--------------------------
Thanks to [Jenkins PMD Plugin](https://wiki.jenkins-ci.org/display/JENKINS/PMD+Plugin) and SensioLabsInsight SDK PMD output you can easily
embed SensioLabsInsight reports into your build workflow, following these steps:

*It is assumed you already have your project up and building in Jenkins and SensioLabsInsight SDK installed*

1. Retrieve your `SensioLabsInsight API Token`, `User UUID` and `Project UUID`
on your [account page](https://insight.sensiolabs.com/account)
2. Install the Jenkins `PMD plugin`:
[How to install a jenkins plugin](https://wiki.jenkins-ci.org/display/JENKINS/Plugins#Plugins-Howtoinstallplugins)
3. Optionally you can also install the `Setenv Plugin`
4. Edit your project configuration
5. If you have Setenv Plugin installed,
enabled `Set environment variables` then add and adapt the following lines to variables name:

        INSIGHT_API_TOKEN="Your API Token"
        INSIGHT_USER_UUID="Your user UUID"
        INSIGHT_PROJECT_UUID="Your project UUID"

6. Add a `Execute shell` build step
7. In the new shell step add and adapt the following command (if you don't have Setenv plugin, replace variables by plain values):

        /path/to/insight-sdk/bin/insight analysis \
        --user-uuid $INSIGHT_USER_UUID \
        --api-token $INSIGHT_API_TOKEN \
        $INSIGHT_PROJECT_UUID --format=pmd > insight-pmd.xml

8. Enable `Publish PMD analysis results` using `insight-pmd.xml` as PMD result filename
9. Optionally, you can add the `insight-pmd.xml` file to artifacts to archive
10. Save and build!

License
-------

This library is licensed under the MIT license.
