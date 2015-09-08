<?php
namespace Blesta\Composer\Installer\Tests\Unit;

use PHPUnit_Framework_TestCase;
use Blesta\Composer\Installer\Installer;
use Composer\Composer;
use Composer\Config;

/**
 * @coversDefaultClass \Blesta\Composer\Installer\Installer
 */
class InstallerTest extends PHPUnit_Framework_TestCase
{
    private $io;
    private $composer;

    public function setUp()
    {
        $this->io = $this->getMockBuilder('\Composer\IO\IOInterface')
            ->getMock();
        $this->composer = new Composer();
        $this->config = new Config();
        $this->composer->setConfig($this->config);
    }

    /**
     * @covers ::supports
     * @covers ::supportedType
     * @dataProvider packageTypeProvider
     * @param string $packageType
     * @param boolean $expected
     */
    public function testSupports($packageType, $expected)
    {
        $installer = new Installer($this->io, $this->composer);

        $this->assertEquals($expected, $installer->supports($packageType));
    }

    /**
     * Data provider for testSupports
     *
     * @return array
     */
    public function packageTypeProvider()
    {
        return array(
            array('blesta-plugin', true),
            array('blesta-module', true),
            array('blesta-gateway-merchant', true),
            array('blesta-gateway-nonmerchant', true),
            array('blesta-invoice-template', true),
            array('blesta-report', true),
            array('blesta-', false),
            array('blesta', false)
        );
    }
}
