<?php

namespace Blesta\Composer\Installer\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Blesta\Composer\Installer\BlestaInstaller;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;

/**
 * @covers \Blesta\Composer\Installer\BlestaInstaller
 */
class BlestaInstallerTest extends TestCase
{
    private PackageInterface $package;
    private Composer $composer;
    private IOInterface $io;
    private BlestaInstaller $installer;

    protected function setUp(): void
    {
        $this->package = $this->createMock(PackageInterface::class);
        $this->composer = $this->createMock(Composer::class);
        $this->io = $this->createMock(IOInterface::class);
        $this->installer = new BlestaInstaller($this->package, $this->composer, $this->io);
    }

    /**
     * Test getLocations property
     */
    public function testGetLocations(): void
    {
        $expectedLocations = [
            'plugin' => 'plugins/{$name}/',
            'gateway-merchant' => 'components/gateways/merchant/{$name}/',
            'gateway-nonmerchant' => 'components/gateways/nonmerchant/{$name}/',
            'module' => 'components/modules/{$name}/',
            'messenger' => 'components/messengers/{$name}/',
            'invoice-template' => 'components/invoice_templates/{$name}/',
            'report' => 'components/reports/{$name}/',
        ];

        // Create a reflection class to access the protected property
        $reflection = new \ReflectionClass($this->installer);
        $property = $reflection->getProperty('locations');
        $property->setAccessible(true);
        $locations = $property->getValue($this->installer);

        foreach ($expectedLocations as $key => $loc) {
            $this->assertArrayHasKey($key, $locations);
            $this->assertEquals($loc, $locations[$key]);
        }
    }

    /**
     * Test getLocations with a package
     */
    public function testGetLocationsWithPackage(): void
    {
        $expectedLocations = [
            'plugin' => 'plugins/{$name}/',
            'gateway-merchant' => 'components/gateways/merchant/{$name}/',
            'gateway-nonmerchant' => 'components/gateways/nonmerchant/{$name}/',
            'module' => 'components/modules/{$name}/',
            'messenger' => 'components/messengers/{$name}/',
            'invoice-template' => 'components/invoice_templates/{$name}/',
            'report' => 'components/reports/{$name}/',
        ];

        $package = $this->createMock(PackageInterface::class);

        $locations = $this->installer->getLocations('blesta');

        $this->assertEquals($expectedLocations, $locations);
    }

    /**
     * Test getInstallPath method
     */
    public function testGetInstallPath(): void
    {
        // Create a mock package with the necessary methods
        $package = $this->createMock(PackageInterface::class);
        $package->expects($this->once())
            ->method('getPrettyName')
            ->willReturn('vendor/my-plugin');
        $package->expects($this->once())
            ->method('getType')
            ->willReturn('blesta-plugin');
        $package->expects($this->once())
            ->method('getExtra')
            ->willReturn([]);

        // Create a mock composer with the necessary methods
        $composer = $this->createMock(Composer::class);
        $rootPackage = $this->createMock(RootPackageInterface::class);
        $rootPackage->expects($this->once())
            ->method('getExtra')
            ->willReturn([]);
        $composer->expects($this->once())
            ->method('getPackage')
            ->willReturn($rootPackage);

        // Create a new installer with our mocks
        $installer = new BlestaInstaller($package, $composer, $this->io);

        // Call the method
        $result = $installer->getInstallPath($package, 'blesta');

        // The result should be the path with the name replaced
        $this->assertEquals('plugins/my-plugin/', $result);
    }

    /**
     * Test getInstallPath with different package types
     */
    public function testGetInstallPathWithDifferentTypes(): void
    {
        // Test with plugin type
        $pluginPackage = $this->createMock(PackageInterface::class);
        $pluginPackage->expects($this->once())
            ->method('getPrettyName')
            ->willReturn('vendor/test-package');
        $pluginPackage->expects($this->once())
            ->method('getType')
            ->willReturn('blesta-plugin');
        $pluginPackage->expects($this->once())
            ->method('getExtra')
            ->willReturn([]);

        // Create a mock composer with the necessary methods
        $composer = $this->createMock(Composer::class);
        $rootPackage = $this->createMock(RootPackageInterface::class);
        $rootPackage->expects($this->once())
            ->method('getExtra')
            ->willReturn([]);
        $composer->expects($this->once())
            ->method('getPackage')
            ->willReturn($rootPackage);

        // Create a new installer with our mocks
        $pluginInstaller = new BlestaInstaller($pluginPackage, $composer, $this->io);

        // Call the method
        $result = $pluginInstaller->getInstallPath($pluginPackage, 'blesta');

        // The result should be the path with the name replaced
        $this->assertEquals('plugins/test-package/', $result);

        // Test with module type
        $modulePackage = $this->createMock(PackageInterface::class);
        $modulePackage->expects($this->once())
            ->method('getPrettyName')
            ->willReturn('vendor/test-package');
        $modulePackage->expects($this->once())
            ->method('getType')
            ->willReturn('blesta-module');
        $modulePackage->expects($this->once())
            ->method('getExtra')
            ->willReturn([]);

        // Create a mock composer with the necessary methods
        $composer = $this->createMock(Composer::class);
        $rootPackage = $this->createMock(RootPackageInterface::class);
        $rootPackage->expects($this->once())
            ->method('getExtra')
            ->willReturn([]);
        $composer->expects($this->once())
            ->method('getPackage')
            ->willReturn($rootPackage);

        // Create a new installer with our mocks
        $moduleInstaller = new BlestaInstaller($modulePackage, $composer, $this->io);

        // Call the method
        $result = $moduleInstaller->getInstallPath($modulePackage, 'blesta');

        // The result should be the path with the name replaced
        $this->assertEquals('components/modules/test-package/', $result);
    }
}
