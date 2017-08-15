<?php

namespace React\Tests\Dns\Config;

use React\Tests\Dns\TestCase;
use React\Dns\Config\HostsFile;

class HostsFileTest extends TestCase
{
    public function testLoadsFromDefaultPath()
    {
        $hosts = HostsFile::loadFromPathBlocking();

        $this->assertInstanceOf('React\Dns\Config\HostsFile', $hosts);
    }

    public function testDefaultShouldHaveLocalhostMapped()
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped('Not supported on Windows');
        }

        $hosts = HostsFile::loadFromPathBlocking();

        $this->assertContains('127.0.0.1', $hosts->getIpsForHost('localhost'));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testLoadThrowsForInvalidPath()
    {
        HostsFile::loadFromPathBlocking('does/not/exist');
    }

    public function testContainsSingleLocalhostEntry()
    {
        $hosts = new HostsFile('127.0.0.1 localhost');

        $this->assertEquals(array('127.0.0.1'), $hosts->getIpsForHost('localhost'));
        $this->assertEquals(array(), $hosts->getIpsForHost('example.com'));
    }

    public function testSkipsComments()
    {
        $hosts = new HostsFile('# start' . PHP_EOL .'#127.0.0.1 localhost' . PHP_EOL . '127.0.0.2 localhost # example.com');

        $this->assertEquals(array('127.0.0.2'), $hosts->getIpsForHost('localhost'));
        $this->assertEquals(array(), $hosts->getIpsForHost('example.com'));
    }

    public function testContainsSingleLocalhostEntryWithCaseIgnored()
    {
        $hosts = new HostsFile('127.0.0.1 LocalHost');

        $this->assertEquals(array('127.0.0.1'), $hosts->getIpsForHost('LOCALHOST'));
    }

    public function testEmptyFileContainsNothing()
    {
        $hosts = new HostsFile('');

        $this->assertEquals(array(), $hosts->getIpsForHost('example.com'));
    }

    public function testSingleEntryWithMultipleNames()
    {
        $hosts = new HostsFile('127.0.0.1 localhost example.com');

        $this->assertEquals(array('127.0.0.1'), $hosts->getIpsForHost('example.com'));
        $this->assertEquals(array('127.0.0.1'), $hosts->getIpsForHost('localhost'));
    }

    public function testMergesEntriesOverMultipleLines()
    {
        $hosts = new HostsFile("127.0.0.1 localhost\n127.0.0.2 localhost\n127.0.0.3 a localhost b\n127.0.0.4 a localhost");

        $this->assertEquals(array('127.0.0.1', '127.0.0.2', '127.0.0.3', '127.0.0.4'), $hosts->getIpsForHost('localhost'));
    }
}
