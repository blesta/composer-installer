<?php

namespace Blesta\Composer\Installer\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Blesta\Composer\Installer\InstallerPlugin;
use Blesta\Composer\Installer\BlestaInstaller;
use Composer\Composer;
use Composer\Config;
use Composer\Downloader\DownloadManager;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use React\Promise\PromiseInterface;
use RuntimeException;

/**
 * @covers \Blesta\Composer\Installer\InstallerPlugin
 */
class InstallerPluginTest extends TestCase
{
    private IOInterface|MockObject $io;
    private Composer|MockObject $composer;
    private Config $config;
    private DownloadManager|MockObject $downloadManager;

    protected function setUp(): void
    {
        $this->io = $this->createMock(IOInterface::class);
        $this->composer = $this->createMock(Composer::class);
        $this->config = new Config();
        $this->downloadManager = $this->createMock(DownloadManager::class);

        $this->composer->method('getConfig')->willReturn($this->config);
        $this->composer->method('getDownloadManager')->willReturn($this->downloadManager);
    }

    /**
     * Test getInstallPath with various package types
     */
    public function testGetInstallPath(): void
    {
        $installer = new InstallerPlugin($this->io, $this->composer);

        $packageTypes = [
            'blesta-plugin' => 'plugins/name/',
            'blesta-module' => 'components/modules/name/',
            'blesta-messenger' => 'components/messengers/name/',
            'blesta-gateway-merchant' => 'components/gateways/merchant/name/',
            'blesta-gateway-nonmerchant' => 'components/gateways/nonmerchant/name/',
            'blesta-invoice-template' => 'components/invoice_templates/name/',
            'blesta-report' => 'components/reports/name/'
        ];

        foreach ($packageTypes as $packageType => $expected) {
            $package = $this->createMock(PackageInterface::class);
            $package->expects($this->any())
                ->method('getType')
                ->willReturn($packageType);
            $package->expects($this->any())
                ->method('getPrettyName')
                ->willReturn('vendor/name');

            $this->assertEquals($expected, $installer->getInstallPath($package));
        }
    }

    /**
     * Test getInstallPath throws exception for invalid types
     */
    public function testGetInstallPathException(): void
    {
        $installer = new InstallerPlugin($this->io, $this->composer);

        $package = $this->createMock(PackageInterface::class);
        $package->expects($this->any())
            ->method('getType')
            ->willReturn('invalid');

        $this->expectException(InvalidArgumentException::class);
        $installer->getInstallPath($package);
    }

    /**
     * Test uninstall functionality
     */
    public function testUninstall(): void
    {
        $installer = new InstallerPlugin($this->io, $this->composer);

        /** @var IOInterface|MockObject $io */
        $io = $this->io;
        $io->expects($this->once())
            ->method('write');

        $package = $this->createMock(PackageInterface::class);
        $package->expects($this->any())
            ->method('getType')
            ->willReturn('blesta-plugin');

        $repo = $this->createMock(InstalledRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('removePackage')
            ->with($this->equalTo($package));
        $repo->expects($this->once())
            ->method('hasPackage')
            ->with($this->equalTo($package))
            ->willReturn(true);

        $result = $installer->uninstall($repo, $package);

        // Verify that the method returns null (not a Promise)
        $this->assertNull($result);
    }

    /**
     * Test uninstall throws exception when package not found
     */
    public function testUninstallException(): void
    {
        $installer = new InstallerPlugin($this->io, $this->composer);

        $package = $this->createMock(PackageInterface::class);
        $repo = $this->createMock(InstalledRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('hasPackage')
            ->willReturn(false);

        $this->expectException(InvalidArgumentException::class);
        $installer->uninstall($repo, $package);
    }

    /**
     * Test supports with various package types
     */
    public function testSupports(): void
    {
        $installer = new InstallerPlugin($this->io, $this->composer);

        $packageTypes = [
            'blesta-plugin' => true,
            'blesta-module' => true,
            'blesta-messenger' => true,
            'blesta-gateway-merchant' => true,
            'blesta-gateway-nonmerchant' => true,
            'blesta-invoice-template' => true,
            'blesta-report' => true,
            'blesta-' => false,
            'blesta' => false
        ];

        foreach ($packageTypes as $packageType => $expected) {
            $this->assertEquals($expected, $installer->supports($packageType));
        }
    }

    /**
     * Test the internal logic of the supports method when supportedType returns false
     */
    public function testSupportsWithInvalidType(): void
    {
        // Create a partial mock of InstallerPlugin to test the internal logic
        $installer = $this->getMockBuilder(InstallerPlugin::class)
            ->setConstructorArgs([$this->io, $this->composer])
            ->onlyMethods(['supportedType'])
            ->getMock();

        // Test when supportedType returns false
        $installer->expects($this->once())
            ->method('supportedType')
            ->with('invalid-type')
            ->willReturn(false);

        $this->assertFalse($installer->supports('invalid-type'));
    }

    /**
     * Test the supportedType method
     */
    public function testSupportedType(): void
    {
        $installer = new InstallerPlugin($this->io, $this->composer);

        // Use reflection to access the protected method
        $reflection = new \ReflectionClass($installer);
        $method = $reflection->getMethod('supportedType');
        $method->setAccessible(true);

        // Test valid types
        $this->assertEquals('blesta', $method->invoke($installer, 'blesta-plugin'));
        $this->assertEquals('blesta', $method->invoke($installer, 'blesta-module'));

        // Test invalid types
        $this->assertFalse($method->invoke($installer, 'invalid'));
        $this->assertFalse($method->invoke($installer, 'blesta')); // No hyphen
        $this->assertFalse($method->invoke($installer, 'unknown-type')); // Unknown base type with hyphen
    }

    /**
     * Test getInstallPath with a custom type that doesn't have an installer class
     */
    public function testGetInstallPathWithMissingInstallerClass(): void
    {
        // Override the supportedTypes array to have a non-existent installer class
        $installer = new class ($this->io, $this->composer) extends InstallerPlugin {
            protected array $supportedTypes = [
                'custom' => 'NonExistentInstaller'
            ];

            protected function supportedType(string $type): string|false
            {
                if ($type === 'test-invalid-type') {
                    return 'custom';
                }
                return false;
            }
        };

        $package = $this->createMock(PackageInterface::class);
        $package->expects($this->once())
            ->method('getType')
            ->willReturn('test-invalid-type');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to create installer for package type "test-invalid-type":');

        $installer->getInstallPath($package);
    }

    /**
     * Test uninstall with exception during getInstallPath
     */
    public function testUninstallWithGetInstallPathException(): void
    {
        $package = $this->createMock(PackageInterface::class);
        $package->expects($this->once())
            ->method('getType')
            ->willReturn('invalid-type');
        $package->expects($this->once())
            ->method('getName')
            ->willReturn('test/package');

        $repo = $this->createMock(InstalledRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('hasPackage')
            ->willReturn(true);
        $repo->expects($this->once())
            ->method('removePackage')
            ->with($package);

        $installer = new InstallerPlugin($this->io, $this->composer);

        // Expect error to be written
        $this->io->expects($this->once())
            ->method('writeError')
            ->with($this->stringContains('Error during uninstall of test/package:'));

        $result = $installer->uninstall($repo, $package);
        $this->assertNull($result);
    }

    /**
     * Test supports method with exception during installer creation
     */
    public function testSupportsWithException(): void
    {
        // Create a custom installer that will throw an exception
        $installer = new class ($this->io, $this->composer) extends InstallerPlugin {
            protected array $supportedTypes = [
                'custom' => 'NonExistentInstaller'
            ];

            protected function supportedType(string $type): string|false
            {
                if ($type === 'test-invalid-type') {
                    return 'custom';
                }
                return false;
            }
        };

        // Expect error to be written
        $this->io->expects($this->once())
            ->method('writeError')
            ->with($this->stringContains('Error checking support for package type "test-invalid-type":'));

        $result = $installer->supports('test-invalid-type');
        $this->assertFalse($result);
    }
}
