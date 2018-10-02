<?php

namespace SymfonyCorp\Insight\Cli;

class Configuration
{
    private $storagePath;
    private $userUuid;
    private $apiToken;
    private $apiEndpoint;

    public function __construct()
    {
        $this->storagePath = $this->getStoragePath();
        $this->load();
    }

    public function getUserUuid()
    {
        return $this->userUuid;
    }

    public function setUserUuid($userUuid)
    {
        $this->userUuid = $userUuid;
    }

    public function getApiToken()
    {
        return $this->apiToken;
    }

    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;
    }

    public function getApiEndpoint()
    {
        return $this->apiEndpoint;
    }

    public function setApiEndpoint($apiEndpoint)
    {
        $this->apiEndpoint = $apiEndpoint;
    }

    public function toArray()
    {
        return array(
            'user_uuid' => $this->userUuid,
            'api_token' => $this->apiToken,
            'api_endpoint' => $this->apiEndpoint,
        );
    }

    public function save()
    {
        file_put_contents($this->storagePath, json_encode($this->toArray()));
    }

    public function equals(Configuration $configuration)
    {
        if ($this->userUuid !== $configuration->userUuid) {
            return false;
        }
        if ($this->apiToken !== $configuration->apiToken) {
            return false;
        }
        if ($this->apiEndpoint !== $configuration->apiEndpoint) {
            return false;
        }

        return true;
    }

    private function load()
    {
        if (!file_exists($this->storagePath)) {
            return;
        }

        $data = json_decode(file_get_contents($this->storagePath), true);

        if (array_key_exists('user_uuid', $data)) {
            $this->userUuid = $data['user_uuid'];
        }
        if (array_key_exists('api_token', $data)) {
            $this->apiToken = $data['api_token'];
        }
        if (array_key_exists('api_endpoint', $data)) {
            $this->apiEndpoint = $data['api_endpoint'];
        }
    }

    private function getStoragePath()
    {
        $storagePath = getenv('INSIGHT_HOME');

        if (!$storagePath) {
            if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
                if (!getenv('APPDATA')) {
                    throw new \RuntimeException('The APPDATA or INSIGHT_HOME environment variable must be set for insight to run correctly');
                }
                $storagePath = strtr(getenv('APPDATA'), '\\', '/').'/Sensiolabs';
            } else {
                if (!getenv('HOME')) {
                    throw new \RuntimeException('The HOME or INSIGHT_HOME environment variable must be set for insight to run correctly');
                }
                $storagePath = rtrim(getenv('HOME'), '/').'/.sensiolabs';
            }
        }

        if (!is_dir($storagePath) && !@mkdir($storagePath, 0777, true)) {
            throw new \RuntimeException(sprintf('The directory "%s" does not exist and could not be created.', $storagePath));
        }

        if (!is_writable($storagePath)) {
            throw new \RuntimeException(sprintf('The directory "%s" is not writable.', $storagePath));
        }

        return $storagePath.'/insight.json';
    }
}
