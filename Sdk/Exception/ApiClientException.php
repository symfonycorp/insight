<?php

/*
 * This file is part of the SymfonyInsight package.
 *
 * (c) Symfony <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCorp\Insight\Sdk\Exception;

use SymfonyCorp\Insight\Sdk\Model\Error;

class ApiClientException extends \LogicException implements ExceptionInterface
{
    private $error;

    public function __construct($message = '', Error $error = null, $code = 0, $e = null)
    {
        $this->error = $error;

        parent::__construct($message, $code, $e);
    }

    public function getError()
    {
        return $this->error;
    }
}
