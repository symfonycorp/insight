<?php

/*
 * This file is part of the SensioLabsInsight package.
 *
 * (c) SensioLabs <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Insight\Sdk\Model;

class Error
{
    private $entityBodyParameters = [];

    public function getEntityBodyParameters()
    {
        return $this->entityBodyParameters;
    }

    public function hasEntityBodyParameter($name)
    {
        return \array_key_exists($name, $this->entityBodyParameters);
    }

    public function addEntityBodyParameter($name)
    {
        if (!$this->hasEntityBodyParameter($name)) {
            $this->entityBodyParameters[$name] = [];
        }

        return $this;
    }

    public function addEntityBodyParameterError($name, $message)
    {
        $this->entityBodyParameters[$name][] = $message;

        return $this;
    }
}
