SensioLabsInsight SDK
=====================

About
-----

This is the official SDK for the SensioLabsInsight API.

Installation
------------

Add `sensiolabs/insight` to the list of requirements of your application's
`composer.json` file.

Command Line Tool
-----------------

The easiest way to use the SensioLabsInsight API is via the built-in command
line tool.

List all the projects in your account:

    $ ./bin/insight projects

The first time, you will be prompted for your SensioLabsInsight API key and
user UUID (which can be found under the "Account" section on the website).
These information are then stored locally, but can still be overridden via the
`--api-token` and `--user-uuid` options.

To run an analysis:

    $ ./bin/insight analyze UUID

where `UUID` is the UUID of the project you want to analyze (the UUIDs are
listed by the `projects` command).

To export an analysis report:

    $ ./bin/insight analysis UUID --format="xml" # or --format="json"

Configuration
-------------

    use SensioLabs\Insight\Sdk\Api;

    $api = new Api(array(
        'api_token' => 'your api token',
        'user_uuid' => 'your user uuid',
    ));

If you want, your can give a `Guzzle\Http\Client` and a
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

License
-------

This library is licensed under the MIT license.
