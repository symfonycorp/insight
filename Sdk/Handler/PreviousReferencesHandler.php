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

class PreviousReferencesHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type' => 'previousAnalysesReferences',
                'format' => 'xml',
                'method' => 'unserializeAnalysesReferences',
            )
        );
    }

    public function unserializeAnalysesReferences(XmlDeserializationVisitor $visitor, \SimpleXMLElement $element)
    {
        $result = array();
        foreach ($element->children() as $reference) {
            $attributes = $reference->attributes();
            $result[(int) $attributes['number']] = (string) $reference;
        }

        return $result;
    }
}
