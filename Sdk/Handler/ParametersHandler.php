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
    private static $parametersMapping = array(
        'project_type' => 'projectType',
    );

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

    public function unserializeXmlParameters(XmlDeserializationVisitor $visitor, \SimpleXMLElement $element)
    {
        $result = array();
        foreach ($element->children() as $node) {
            $name = (string) $node->attributes()['name'];
            if (!isset(self::$parametersMapping[$name])) {
                continue;
            }

            $result[self::$parametersMapping[$name]] = (string) $node;
        }

        return $result;
    }
}
