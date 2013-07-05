<?php

/*
 * This file is part of the SensioLabsInsight package.
 *
 * (c) SensioLabs <contact@sensiolabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Insight\Sdk\Exception;

use SensioLabs\Insight\Sdk\Model\Error;

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
