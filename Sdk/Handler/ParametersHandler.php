<?php

/*
 * This file is part of the SensioLabsInsight package.
 *
 * (c) SensioLabs <contact@sensiolabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Insight\Sdk\Handler;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\XmlDeserializationVisitor;

class ParametersHandler implements SubscribingHandlerInterface
{
    private static $parametersMapping = [
        'project_type' => 'projectType',
    ];

    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type' => 'parameters',
                'format' => 'xml',
                'method' => 'unserializeXmlParameters',
            )
        );
    }

    public function unserializeXmlParameters(XmlDeserializationVisitor $visitor, \SimpleXMLElement $element, array $type, Context $context)
    {
        $result = [];
        foreach ($element->children() as $name => $value) {
            $result[self::$parametersMapping[$name]] = (string) $value;
        }

        return $result;
    }
}
