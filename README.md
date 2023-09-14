SymfonyInsight SDK
==================

About
-----

This is the official SDK for the [SymfonyInsight](https://insight.symfony.com/) API.

Installation
------------

To install the SDK, run the command below and you will get the latest version:

    composer require sensiolabs/insight

Command Line Tool
-----------------

The easiest way to use the SymfonyInsight API is via the built-in command line tool.

A phar version of the command line tool exists to avoid installation of this
project. Download it, then use it like the command line tool:

    $ curl -o insight.phar -s https://get.insight.symfony.com/insight.phar
    # or
    $ wget https://get.insight.symfony.com/insight.phar

    # Then
    $ php insight.phar

List all the projects in your account:

    $ php insight.phar projects

The first time, you will be prompted for your SymfonyInsight API key and
user UUID (which can be found under the ["Account" section](https://insight.symfony.com/account) on the website).

These information are then stored locally, but can still be overridden via the
`--api-token` and `--user-uuid` options.

To run an analysis:

    $ php insight.phar analyze UUID

where `UUID` is the UUID of the project you want to analyze (the UUIDs are
listed by the `projects` command).

To export an analysis report:

    $ php insight.phar analysis UUID --format="xml" # or --format="json" or --format="pmd"

Configuration
-------------

    use SensioLabs\Insight\Sdk\Api;

    $api = new Api(array(
        'api_token' => 'your api token',
        'user_uuid' => 'your user uuid',
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

    // for a specific branch
    $api->getAnalyses('project uuid', 'branch name');

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

Thanks to [Jenkins PMD Plugin](https://wiki.jenkins-ci.org/display/JENKINS/PMD+Plugin) and 
SymfonyInsight SDK PMD output you can easily embed SymfonyInsight reports into your build
workflow, following these steps:

*It is assumed you already have your project up and building in Jenkins and SymfonyInsight SDK installed*.

1. Retrieve your `SymfonyInsight API Token`, `User UUID` and `Project UUID`
on your [account page](https://insight.symfony.com/account)
2. Install the Jenkins `PMD plugin`:
[How to install a jenkins plugin](https://wiki.jenkins-ci.org/display/JENKINS/Plugins#Plugins-Howtoinstallplugins)
3. Optionally you can also install the `EnvInject  Plugin`
4. Edit your project configuration
5. If you have EnvInject Plugin installed,
enabled `Set environment variables` then add and adapt the following lines to variables name:

        INSIGHT_API_TOKEN="Your API Token"
        INSIGHT_USER_UUID="Your user UUID"
        INSIGHT_PROJECT_UUID="Your project UUID"

6. Add a `Execute shell` build step
7. In the new shell step add and adapt the following command (if you don't have EnvInject plugin, replace variables by plain values):

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
