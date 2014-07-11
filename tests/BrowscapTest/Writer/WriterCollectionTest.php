<?php
/**
 * Copyright (c) 1998-2014 Browser Capabilities Project
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the LICENSE file distributed with this package.
 *
 * @category   BrowscapTest
 * @package    Writer
 * @copyright  1998-2014 Browser Capabilities Project
 * @license    MIT
 */

namespace BrowscapTest\Writer;

use Browscap\Writer\WriterCollection;
use org\bovigo\vfs\vfsStream;

/**
 * Class WriterCollectionTest
 *
 * @category   BrowscapTest
 * @package    Writer
 * @author     Thomas Müller <t_mueller_stolzenhain@yahoo.de>
 */
class WriterCollectionTest extends \PHPUnit_Framework_TestCase
{
    const STORAGE_DIR = 'storage';

    /**
     * @var \Browscap\Writer\WriterCollection
     */
    private $object = null;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root = null;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $file = null;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     */
    public function setUp()
    {
        $this->root = vfsStream::setup(self::STORAGE_DIR);
        $this->file = vfsStream::url(self::STORAGE_DIR) . DIRECTORY_SEPARATOR . 'test.xml';

        $this->object = new WriterCollection();
    }

    /**
     * @return \Browscap\Writer\WriterCollection
     */
    public function testAddWriter()
    {
        $mockFilter = $this->getMock('\Browscap\Filter\FullFilter', array('isOutput', 'getType'), array(), '', false);
        $mockFilter
            ->expects(self::once())
            ->method('isOutput')
            ->will(self::returnValue(true))
        ;
        $mockFilter
            ->expects(self::once())
            ->method('getType')
            ->will(self::returnValue('Test'))
        ;
        
        $mockFormatter = $this->getMock(
            '\Browscap\Formatter\XmlFormatter',
            array('getType'),
            array(),
            '',
            false
        );
        $mockFormatter
            ->expects(self::once())
            ->method('getType')
            ->will(self::returnValue('test'))
        ;

        $mockWriter = $this->getMock('\Browscap\Writer\CsvWriter', array('getFilter', 'getFormatter'), array(), '', false);
        $mockWriter
            ->expects(self::once())
            ->method('getFilter')
            ->will(self::returnValue($mockFilter))
        ;
        $mockWriter
            ->expects(self::once())
            ->method('getFormatter')
            ->will(self::returnValue($mockFormatter))
        ;

        self::assertSame($this->object, $this->object->addWriter($mockWriter));
        
        return $this->object;
    }

    /**
     * @depends testAddWriter
     *
     * @param \Browscap\Writer\WriterCollection $object
     */
    public function testSetSilent(WriterCollection $object)
    {
        $mockDivision = $this->getMock('\Browscap\Data\Division', array(), array(), '', false);

        self::assertSame($object, $object->setSilent($mockDivision));
    }

    /**
     * @depends testAddWriter
     *
     * @param \Browscap\Writer\WriterCollection $object
     */
    public function testFileStart(WriterCollection $object)
    {
        self::assertSame($object, $object->fileStart());
    }

    /**
     * @depends testAddWriter
     *
     * @param \Browscap\Writer\WriterCollection $object
     */
    public function testFileEnd(WriterCollection $object)
    {
        self::assertSame($object, $object->fileEnd());
    }

    /**
     * @depends testAddWriter
     *
     * @param \Browscap\Writer\WriterCollection $object
     */
    public function testRenderHeader(WriterCollection $object)
    {
        $header = array('TestData to be renderd into the Header');

        self::assertSame($object, $object->renderHeader($header));
    }

    /**
     * @depends testAddWriter
     *
     * @param \Browscap\Writer\WriterCollection $object
     */
    public function testRenderVersion(WriterCollection $object)
    {
        $version = 'test';

        $mockCollection = $this->getMock('\Browscap\Data\DataCollection', array('getGenerationDate'), array(), '', false);
        $mockCollection
            ->expects(self::once())
            ->method('getGenerationDate')
            ->will(self::returnValue(new \DateTime()))
        ;

        self::assertSame($object, $object->renderVersion($version, $mockCollection));
    }
}
