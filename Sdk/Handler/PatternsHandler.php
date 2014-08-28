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

use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\XmlDeserializationVisitor;

class PatternsHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type' => 'patterns',
                'format' => 'xml',
                'method' => 'unserializeXmlPatterns',
            )
        );
    }

    public function unserializeXmlPatterns(XmlDeserializationVisitor $visitor, \SimpleXMLElement $element)
    {
        $result = array();
        foreach ($element->children() as $type => $patterns) {
            $result[$type] = array();
            foreach ($patterns as $pattern) {
                $result[$type][] = (string) $pattern;
            }
        }

        return $result;
    }
}
