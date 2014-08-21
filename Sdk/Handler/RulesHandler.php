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

class RulesHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type' => 'rules',
                'format' => 'xml',
                'method' => 'unserializeXmlRules',
            )
        );
    }

    public function unserializeXmlRules(XmlDeserializationVisitor $visitor, \SimpleXMLElement $element, array $type, Context $context)
    {
        $result = [];
        foreach ($element->children() as $ruleName => $ruleOptions) {
            $result[$ruleName] = array();
            foreach ($ruleOptions->children() as $optionName => $optionValue) {
                $result[$ruleName][$optionName] = (string) $optionValue;
            }
        }

        return $result;
    }
}
