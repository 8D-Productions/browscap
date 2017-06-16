<?php
/**
 * Copyright (c) 1998-2017 Browser Capabilities Project
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category   Browscap
 * @copyright  1998-2017 Browser Capabilities Project
 * @license    MIT
 */

namespace Browscap\Writer;

use Browscap\Data\DataCollection;
use Browscap\Filter\FilterInterface;
use Browscap\Formatter\FormatterInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CsvWriter
 *
 * @category   Browscap
 * @author     Thomas Müller <t_mueller_stolzenhain@yahoo.de>
 */
class CsvWriter implements WriterInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;

    /**
     * @var resource
     */
    private $file = null;

    /**
     * @var \Browscap\Formatter\FormatterInterface
     */
    private $formatter = null;

    /**
     * @var \Browscap\Filter\FilterInterface
     */
    private $type = null;

    /**
     * @var bool
     */
    private $silent = false;

    /**
     * @var array
     */
    private $outputProperties = [];

    /**
     * @param string $file
     */
    public function __construct($file)
    {
        $this->file = fopen($file, 'w');
    }

    /**
     * returns the Type of the writer
     *
     * @return string
     */
    public function getType()
    {
        return 'csv';
    }

    /**
     * closes the Writer and the written File
     *
     * @return \Browscap\Writer\WriterInterface
     */
    public function close()
    {
        fclose($this->file);
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return \Browscap\Writer\WriterInterface
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param \Browscap\Formatter\FormatterInterface $formatter
     *
     * @return \Browscap\Writer\WriterInterface
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * @return \Browscap\Formatter\FormatterInterface
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * @param \Browscap\Filter\FilterInterface $filter
     *
     * @return \Browscap\Writer\WriterInterface
     */
    public function setFilter(FilterInterface $filter)
    {
        $this->type             = $filter;
        $this->outputProperties = [];

        return $this;
    }

    /**
     * @return \Browscap\Filter\FilterInterface
     */
    public function getFilter()
    {
        return $this->type;
    }

    /**
     * @param bool $silent
     *
     * @return \Browscap\Writer\WriterInterface
     */
    public function setSilent($silent)
    {
        $this->silent = (boolean) $silent;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSilent()
    {
        return $this->silent;
    }

    /**
     * Generates a start sequence for the output file
     *
     * @return \Browscap\Writer\WriterInterface
     */
    public function fileStart()
    {
        return $this;
    }

    /**
     * Generates a end sequence for the output file
     *
     * @return \Browscap\Writer\WriterInterface
     */
    public function fileEnd()
    {
        return $this;
    }

    /**
     * Generate the header
     *
     * @param string[] $comments
     *
     * @return \Browscap\Writer\WriterInterface
     */
    public function renderHeader(array $comments = [])
    {
        return $this;
    }

    /**
     * renders the version information
     *
     * @param string[] $versionData
     *
     * @return \Browscap\Writer\WriterInterface
     */
    public function renderVersion(array $versionData = [])
    {
        if ($this->isSilent()) {
            return $this;
        }

        $this->getLogger()->debug('rendering version information');

        fputs($this->file, '"GJK_Browscap_Version","GJK_Browscap_Version"' . PHP_EOL);

        if (!isset($versionData['version'])) {
            $versionData['version'] = '0';
        }

        if (!isset($versionData['released'])) {
            $versionData['released'] = '';
        }

        fputs($this->file, '"' . $versionData['version'] . '","' . $versionData['released'] . '"' . PHP_EOL);

        return $this;
    }

    /**
     * renders the header for all divisions
     *
     * @param \Browscap\Data\DataCollection $collection
     *
     * @return \Browscap\Writer\WriterInterface
     */
    public function renderAllDivisionsHeader(DataCollection $collection)
    {
        $division = $collection->getDefaultProperties();
        $ua       = $division->getUserAgents();

        if (empty($ua[0]['properties']) || !is_array($ua[0]['properties'])) {
            return $this;
        }

        $defaultproperties = $ua[0]['properties'];
        $properties        = array_merge(
            ['PropertyName', 'MasterParent', 'LiteMode', 'Parent'],
            array_keys($defaultproperties)
        );

        $values = [];

        foreach ($properties as $property) {
            if (!isset($this->outputProperties[$property])) {
                $this->outputProperties[$property] = $this->getFilter()->isOutputProperty($property, $this);
            }

            if (!$this->outputProperties[$property]) {
                continue;
            }

            $values[] = $this->getFormatter()->formatPropertyName($property);
        }

        fputs($this->file, implode(',', $values) . PHP_EOL);

        return $this;
    }

    /**
     * renders the header for a division
     *
     * @param string $division
     * @param string $parent
     *
     * @return \Browscap\Writer\WriterInterface
     */
    public function renderDivisionHeader($division, $parent = 'DefaultProperties')
    {
        return $this;
    }

    /**
     * renders the header for a section
     *
     * @param string $sectionName
     *
     * @return \Browscap\Writer\WriterInterface
     */
    public function renderSectionHeader($sectionName)
    {
        return $this;
    }

    /**
     * renders all found useragents into a string
     *
     * @param string[]                      $section
     * @param \Browscap\Data\DataCollection $collection
     * @param array[]                       $sections
     * @param string                        $sectionName
     *
     * @throws \InvalidArgumentException
     * @return CsvWriter
     */
    public function renderSectionBody(array $section, DataCollection $collection, array $sections = [], $sectionName = '')
    {
        if ($this->isSilent()) {
            return $this;
        }

        $division          = $collection->getDefaultProperties();
        $ua                = $division->getUserAgents();
        $defaultproperties = $ua[0]['properties'];
        $properties        = array_merge(
            ['PropertyName', 'MasterParent', 'LiteMode', 'Parent'],
            array_keys($defaultproperties)
        );

        $values = [];

        $section['PropertyName'] = $sectionName;
        $section['MasterParent'] = $this->detectMasterParent($sectionName, $section);

        if (in_array($sectionName, ['DefaultProperties', '*'])) {
            $section['LiteMode'] = 'true';
        } else {
            $section['LiteMode'] = ((!isset($section['lite']) || !$section['lite']) ? 'false' : 'true');
        }

        foreach ($properties as $property) {
            if (!isset($this->outputProperties[$property])) {
                $this->outputProperties[$property] = $this->getFilter()->isOutputProperty($property, $this);
            }

            if (!$this->outputProperties[$property]) {
                continue;
            }

            if (isset($section[$property])) {
                $value = $section[$property];
            } else {
                $value = '';
            }

            $values[] = $this->getFormatter()->formatPropertyValue($value, $property);
        }

        fputs($this->file, implode(',', $values) . PHP_EOL);

        return $this;
    }

    /**
     * renders the footer for a section
     *
     * @param string $sectionName
     *
     * @return \Browscap\Writer\WriterInterface
     */
    public function renderSectionFooter($sectionName = '')
    {
        return $this;
    }

    /**
     * renders the footer for a division
     *
     * @return \Browscap\Writer\WriterInterface
     */
    public function renderDivisionFooter()
    {
        return $this;
    }

    /**
     * renders the footer for all divisions
     *
     * @return \Browscap\Writer\WriterInterface
     */
    public function renderAllDivisionsFooter()
    {
        return $this;
    }

    /**
     * @param string $key
     * @param array  $properties
     *
     * @return string
     */
    private function detectMasterParent($key, array $properties)
    {
        $this->getLogger()->debug('check if the element can be marked as "MasterParent"');

        if (in_array($key, ['DefaultProperties', '*'])
            || empty($properties['Parent'])
            || 'DefaultProperties' === $properties['Parent']
        ) {
            return 'true';
        }

        return 'false';
    }
}
