<?php

namespace Blesta\Composer\Installer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class Installer implements PluginInterface
{
    /**
     * @var Composer
     */
    protected Composer $composer;

    /**
     * @var IOInterface
     */
    protected IOInterface $io;

    /**
     * @var InstallerPlugin
     */
    protected InstallerPlugin $installer;

    /**
     * Activate the plugin
     *
     * @param Composer $composer
     * @param IOInterface $io
     * @return void
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->installer = new InstallerPlugin($io, $composer);
        $composer->getInstallationManager()->addInstaller($this->installer);
    }

    /**
     * Remove any hooks from Composer
     *
     * This method is called when the plugin is being deactivated.
     * No cleanup is needed as this plugin doesn't add any hooks that need to be removed.
     * The InstallerPlugin is automatically removed from the InstallationManager by Composer.
     *
     * @param Composer $composer
     * @param IOInterface $io
     * @return void
     */
    public function deactivate(Composer $composer, IOInterface $io): void
    {
        // No cleanup needed when the plugin is deactivated
    }

    /**
     * Prepare the plugin to be uninstalled
     *
     * This method is called when the plugin itself is being uninstalled.
     * No cleanup is needed as this plugin doesn't create any persistent resources.
     * The actual uninstallation of packages installed by this plugin is handled
     * by the InstallerPlugin::uninstall method.
     *
     * @param Composer $composer
     * @param IOInterface $io
     * @return void
     */
    public function uninstall(Composer $composer, IOInterface $io): void
    {
        // No cleanup needed when the plugin itself is uninstalled
    }
}
