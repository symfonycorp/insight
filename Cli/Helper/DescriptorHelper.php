<?php

/*
 * This file is part of the SensioLabsInsight package.
 *
 * (c) SensioLabs <contact@sensiolabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Insight\Cli\Helper;

use JMS\Serializer\Serializer;
use SensioLabs\Insight\Cli\Descriptor\JsonDescriptor;
use SensioLabs\Insight\Cli\Descriptor\TextDescriptor;
use SensioLabs\Insight\Cli\Descriptor\XmlDescriptor;
use SensioLabs\Insight\Cli\Descriptor\PmdDescriptor;
use Symfony\Component\Console\Descriptor\DescriptorInterface;
use Symfony\Component\Console\Helper\DescriptorHelper as BaseDescriptorHelper;
use Symfony\Component\Console\Output\OutputInterface;

class DescriptorHelper extends BaseDescriptorHelper
{
    private $descriptors = array();

    public function __construct(Serializer $serializer)
    {
        $this
            ->register('txt',  new TextDescriptor())
            ->register('pmd',  new PmdDescriptor())
            ->register('xml',  new XmlDescriptor($serializer))
            ->register('json', new JsonDescriptor($serializer))
        ;
    }

    // hack to be almost forward compatible with https://github.com/symfony/symfony/issues/8371
    public function describe(OutputInterface $output, $object, $format = null, $raw = false, $namespace = null)
    {
        $options = array(
            'raw_text' => $raw,
            'format' => $format ?: 'txt',
            'output' => $output,
        );
        $options['type'] = !$raw && 'txt' === $options['format'] ? OutputInterface::OUTPUT_NORMAL : OutputInterface::OUTPUT_RAW;

        if (!isset($this->descriptors[$options['format']])) {
            throw new \InvalidArgumentException(sprintf('Unsupported format "%s".', $options['format']));
        }

        $this->descriptors[$options['format']]->describe($object, $options);
    }

    public function register($format, DescriptorInterface $descriptor)
    {
        $this->descriptors[$format] = $descriptor;

        return $this;
    }
}
