<?php

namespace NovemBit\nardivan;

use Composer\Package\Package;
use Composer\Script\Event;
use Composer\Installer\PackageEvent;
use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

class PackageInstaller
{
    public static function postUpdate(Event $event)
    {
        $composer = $event->getComposer();
        // do stuff
    }

    public static function postPackageInstall(PackageEvent $event)
    {
        $operation = $event->getOperation();
        $package = $operation->getTargetPackage();
        self::runInstaller($package);

    }

    public static function runComposerCommand($path, $command)
    {
        $pwd = getcwd();
        chdir($path);
        Nardivan::print("==> Update composer : ", false, 'yellow');
        putenv('COMPOSER=' . $path . '/composer.json');
        // call `composer install` command programmatically
        $input = new ArrayInput(array('command' => $command/*, '--quiet'*/));
        $application = new Application();
        $application->setAutoExit(false);
        $application->run($input);
        Nardivan::print("success", true, 'light_green');
        chdir($pwd);
    }

    /**
     * @param PackageEvent $event
     */
    public static function postPackageUpdate(PackageEvent $event)
    {

        $operation = $event->getOperation();
        $package = $operation->getPackage();

        $package_directory = $event->getComposer()->getInstallationManager()->getInstallPath($package);


        self::runInstaller($package_directory);
        // do stuff
    }

    public static function runNpmCommand($path, $command)
    {
        $pwd = getcwd();
        chdir($path);
        exec('npm ' . $command);
        chdir($pwd);
    }

    public static function runInstaller($directory)
    {

        if (file_exists($directory . '/composer.json')) {
            self::runComposerCommand($directory, 'update');
        }
        if (file_exists($directory . '/package.json')) {
            self::runNpmCommand($directory, 'install');
        }
    }

}