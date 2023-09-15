<?php

/*
 * This file is part of the SymfonyInsight package.
 *
 * (c) Symfony <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SensioLabs\Insight\Cli\Helper;

use JMS\Serializer\Serializer;
use SensioLabs\Insight\Cli\Descriptor\AbstractDescriptor;
use SensioLabs\Insight\Cli\Descriptor\JsonDescriptor;
use SensioLabs\Insight\Cli\Descriptor\PmdDescriptor;
use SensioLabs\Insight\Cli\Descriptor\TextDescriptor;
use SensioLabs\Insight\Cli\Descriptor\XmlDescriptor;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;

class DescriptorHelper extends Helper
{
    private $descriptors = [];

    public function __construct(Serializer $serializer)
    {
        $this
            ->register('json', new JsonDescriptor($serializer))
            ->register('pmd', new PmdDescriptor())
            ->register('txt', new TextDescriptor())
            ->register('xml', new XmlDescriptor($serializer))
        ;
    }

    public function describe(OutputInterface $output, $object, $format = null, $showIgnoredViolation = false)
    {
        $options = [
            'raw_text' => false,
            'format' => $format ?: 'txt',
            'output' => $output,
            'show_ignored_violations' => $showIgnoredViolation,
        ];
        $options['type'] = 'txt' === $options['format'] ? OutputInterface::OUTPUT_NORMAL : OutputInterface::OUTPUT_RAW;

        if (!isset($this->descriptors[$options['format']])) {
            throw new \InvalidArgumentException(sprintf('Unsupported format "%s".', $options['format']));
        }

        $this->descriptors[$options['format']]->describe($object, $options);
    }

    public function register($format, AbstractDescriptor $descriptor)
    {
        $this->descriptors[$format] = $descriptor;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'descriptor';
    }
}
