<?php

namespace Blesta\Composer\Installer\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Blesta\Composer\Installer\Installer;
use Blesta\Composer\Installer\InstallerPlugin;
use Composer\Composer;
use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Installer\InstallationManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Blesta\Composer\Installer\Installer
 */
class InstallerTest extends TestCase
{
    private IOInterface|MockObject $io;
    private Composer|MockObject $composer;
    private Config $config;
    private InstallationManager|MockObject $installationManager;

    protected function setUp(): void
    {
        $this->io = $this->createMock(IOInterface::class);
        $this->composer = $this->createMock(Composer::class);
        $this->config = new Config();
        $this->installationManager = $this->createMock(InstallationManager::class);

        $this->composer->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->config);
        $this->composer->expects($this->any())
            ->method('getInstallationManager')
            ->willReturn($this->installationManager);
    }

    /**
     * Test activate method
     */
    public function testActivate(): void
    {
        $installer = new Installer();

        /** @var InstallationManager|MockObject $installationManager */
        $installationManager = $this->installationManager;
        $installationManager->expects($this->once())
            ->method('addInstaller')
            ->with($this->isInstanceOf(InstallerPlugin::class));

        $installer->activate($this->composer, $this->io);

        // Verify that the properties are set correctly
        $reflection = new \ReflectionClass($installer);

        $composerProperty = $reflection->getProperty('composer');
        $composerProperty->setAccessible(true);
        $this->assertSame($this->composer, $composerProperty->getValue($installer));

        $ioProperty = $reflection->getProperty('io');
        $ioProperty->setAccessible(true);
        $this->assertSame($this->io, $ioProperty->getValue($installer));

        $installerProperty = $reflection->getProperty('installer');
        $installerProperty->setAccessible(true);
        $this->assertInstanceOf(InstallerPlugin::class, $installerProperty->getValue($installer));
    }

    /**
     * Test deactivate method
     */
    public function testDeactivate(): void
    {
        $installer = new Installer();

        // Set up expectations for deactivation
        /** @var InstallationManager|MockObject $installationManager */
        $installationManager = $this->installationManager;

        // Verify it doesn't throw, as this method does nothing
        $installationManager->expects($this->never())
            ->method('removeInstaller');

        $installer->deactivate($this->composer, $this->io);

        // Since deactivate is currently a no-op, we're just testing it doesn't throw
        $this->addToAssertionCount(1);
    }

    /**
     * Test uninstall method
     */
    public function testUninstall(): void
    {
        $installer = new Installer();

        // Set up expectations for uninstallation
        /** @var InstallationManager|MockObject $installationManager */
        $installationManager = $this->installationManager;

        // Verify it doesn't throw, as this method does nothing
        $installationManager->expects($this->never())
            ->method('removeInstaller');

        $installer->uninstall($this->composer, $this->io);

        // Since uninstall is currently a no-op, we're just testing it doesn't throw
        $this->addToAssertionCount(1);
    }
}
