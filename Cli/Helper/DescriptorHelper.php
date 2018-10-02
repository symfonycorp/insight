<?php

/*
 * This file is part of the SymfonyInsight package.
 *
 * (c) Symfony <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCorp\Insight\Cli\Helper;

use JMS\Serializer\Serializer;
use SymfonyCorp\Insight\Cli\Descriptor\AbstractDescriptor;
use SymfonyCorp\Insight\Cli\Descriptor\JsonDescriptor;
use SymfonyCorp\Insight\Cli\Descriptor\PmdDescriptor;
use SymfonyCorp\Insight\Cli\Descriptor\TextDescriptor;
use SymfonyCorp\Insight\Cli\Descriptor\XmlDescriptor;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;

class DescriptorHelper extends Helper
{
    private $descriptors = array();

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
        $options = array(
            'raw_text' => false,
            'format' => $format ?: 'txt',
            'output' => $output,
            'show_ignored_violations' => $showIgnoredViolation,
        );
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
    public function getName()
    {
        return 'descriptor';
    }
}
