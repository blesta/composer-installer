<?php

namespace Blesta\Composer\Installer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackage;
use Composer\Repository\InstalledRepositoryInterface;
use InvalidArgumentException;
use React\Promise\PromiseInterface;
use RuntimeException;

class InstallerPlugin extends LibraryInstaller
{
    /**
     * @var array<string, string>
     */
    protected array $supportedTypes = [
        'blesta' => 'BlestaInstaller'
    ];

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package): string
    {
        $type = $package->getType();
        $supportedType = $this->supportedType($type);

        if ($supportedType === false) {
            throw new InvalidArgumentException(
                sprintf('Sorry the package type "%s" is not supported.', $type)
            );
        }

        $class = 'Blesta\\Composer\\Installer\\' . $this->supportedTypes[$supportedType];

        try {
            /** @var BlestaInstaller $installer */
            $installer = new $class($package, $this->composer, $this->io);
            return $installer->getInstallPath($package, $supportedType);
        } catch (\Throwable $e) {
            throw new RuntimeException(
                sprintf('Failed to create installer for package type "%s": %s', $type, $e->getMessage()),
                0,
                $e
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package): ?PromiseInterface
    {
        if (!$repo->hasPackage($package)) {
            throw new InvalidArgumentException(
                sprintf('Package is not installed: %s', $package)
            );
        }

        $repo->removePackage($package);

        try {
            $installPath = $this->getInstallPath($package);
            $this->io->write(
                sprintf(
                    'Deleting %s - %s',
                    $installPath,
                    $this->filesystem->removeDirectory($installPath)
                    ? '<comment>deleted</comment>'
                    : '<error>not deleted</error>'
                )
            );
        } catch (\Throwable $e) {
            $this->io->writeError(
                sprintf('Error during uninstall of %s: %s', $package->getName(), $e->getMessage())
            );
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(string $packageType): bool
    {
        $supportedType = $this->supportedType($packageType);

        if ($supportedType === false) {
            return false;
        }

        try {
            // Create a dummy package for testing
            $package = new RootPackage('dummy/package', '1.0.0.0', '1.0.0');
            $package->setType($packageType);

            $class = 'Blesta\\Composer\\Installer\\' . $this->supportedTypes[$supportedType];
            /** @var BlestaInstaller $installer */
            $installer = new $class($package, $this->composer, $this->io);

            // Pass the package to getLocations method
            $locations = $installer->getLocations($package);

            foreach ($locations as $type => $path) {
                if ($supportedType . '-' . $type === $packageType) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            $this->io->writeError(
                sprintf('Error checking support for package type "%s": %s', $packageType, $e->getMessage())
            );
            return false;
        }

        return false;
    }

    /**
     * Find the matching installer type
     *
     * @param string $type
     * @return string|false The supported type if found, false otherwise
     */
    protected function supportedType(string $type): string|false
    {
        $pos = strpos($type, '-');
        if ($pos === false) {
            return false;
        }

        $baseType = substr($type, 0, $pos);

        if (array_key_exists($baseType, $this->supportedTypes)) {
            return $baseType;
        }

        return false;
    }
}
