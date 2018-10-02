<?php

/*
 * This file is part of the SymfonyInsight package.
 *
 * (c) Symfony <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCorp\Insight\Sdk\Model;

class Error
{
    private $entityBodyParameters = array();

    public function getEntityBodyParameters()
    {
        return $this->entityBodyParameters;
    }

    public function hasEntityBodyParameter($name)
    {
        return array_key_exists($name, $this->entityBodyParameters);
    }

    public function addEntityBodyParameter($name)
    {
        if (!$this->hasEntityBodyParameter($name)) {
            $this->entityBodyParameters[$name] = array();
        }

        return $this;
    }

    public function addEntityBodyParameterError($name, $message)
    {
        $this->entityBodyParameters[$name][] = $message;

        return $this;
    }
}
