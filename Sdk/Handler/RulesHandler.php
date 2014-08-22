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

    public function unserializeXmlRules(XmlDeserializationVisitor $visitor, \SimpleXMLElement $element)
    {
        $result = array();
        foreach ($element->children() as $rule) {
            $attributes = $rule->attributes();

            $ruleOptions = array('enabled' => (string) $attributes['enabled'] !== 'false');
            foreach ($rule->children() as $optionName => $optionValue) {
                $optionName = str_replace('-', '_', $optionName);
                $ruleOptions[$optionName] = $this->parseParameterOption($optionValue);
            }

            $ruleName = (string) $attributes['name'];
            $result[$ruleName] = $ruleOptions;
        }

        return $result;
    }

    private function parseParameterOption(\SimpleXMLElement $parameterOption)
    {
        if (!$parameterOption->children()->count()) {
            return (string) $parameterOption;
        }

        $result = array();
        foreach ($parameterOption as $subOptions) {
            $result[] = $this->parseParameterOption($subOptions);
        }

        return $result;
    }
}
