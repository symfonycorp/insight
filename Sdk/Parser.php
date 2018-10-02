<?php

/*
 * This file is part of the SymfonyInsight package.
 *
 * (c) Symfony <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCorp\Insight\Sdk;

use SymfonyCorp\Insight\Sdk\Model\Error;
use SymfonyCorp\Insight\Sdk\Exception\ApiParserException;

class Parser
{
    public function parseError($content)
    {
        if (!$content) {
            throw new ApiParserException('Could not transform this xml to a \DOMDocument instance.');
        }

        $internalErrors = libxml_use_internal_errors(true);
        $disableEntities = libxml_disable_entity_loader(true);
        libxml_clear_errors();

        $document = new \DOMDocument();
        $document->validateOnParse = true;
        if (!$document->loadXML($content, LIBXML_NONET | (defined('LIBXML_COMPACT') ? LIBXML_COMPACT : 0))) {
            libxml_disable_entity_loader($disableEntities);

            libxml_clear_errors();
            libxml_use_internal_errors($internalErrors);

            throw new ApiParserException('Could not transform this xml to a \DOMDocument instance.');
        }

        $document->normalizeDocument();

        libxml_use_internal_errors($internalErrors);
        libxml_disable_entity_loader($disableEntities);

        $xpath = new \DOMXpath($document);

        $nodes = $xpath->evaluate('./error');
        if (1 === $nodes->length) {
            throw new ApiParserException('The dom contains more than one error node.');
        }

        $error = new Error();

        $parameters = $xpath->query('./entity/body/parameter', $nodes->item(0));
        foreach ($parameters as $parameter) {
            $name = $parameter->getAttribute('name');
            $error->addEntityBodyParameter($name);

            $messages = $xpath->query('./message', $parameter);
            foreach ($messages as $message) {
                $error->addEntityBodyParameterError($name, $this->sanitizeValue($message->nodeValue));
            }
        }

        return $error;
    }

    protected function sanitizeValue($value)
    {
        if ('true' === $value) {
            $value = true;
        } elseif ('false' === $value) {
            $value = false;
        } elseif (empty($value)) {
            $value = null;
        }

        return $value;
    }
}
