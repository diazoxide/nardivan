<?php

namespace NovemBit\nardivan;


use Composer\Console\Application;
use Composer\Package\Package;
use Exception;
use Symfony\Component\Console\Input\ArrayInput;

class Run
{

    public $logo = <<<html
  _  _                _  _                  
 | \| | __ _  _ _  __| |(_)__ __ __ _  _ _  
 | .` |/ _` || '_|/ _` || |\ V // _` || ' \ 
 |_|\_|\__,_||_|  \__,_||_| \_/ \__,_||_||_|
html;
    public $pwd;

    public $instance_directory = ".nardivan";


    public $use_global_git = true;

    /**
     * @var string
     */
    private $environment_dir = 'environments';
    private $config;

    /**
     * nardivan constructor.
     * @throws Exception
     */
    public function __construct()
    {
        if (!isset($_SERVER['PWD'])) {
            Output::error("Please run this file only with CLI");
            die;
        }

        $this->pwd = $_SERVER['PWD'];

        if (!file_exists($this->pwd . '/nardivan.json')) {
            Output::error('nardivan.json not detected');
            return false;
        }

        $json = file_get_contents($this->pwd . '/nardivan.json');
        $config = json_decode($json, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            Output::error("Bad nardivan.json file");
            return false;
        }

        $this->setConfig(new Config($config));

        return $this->init();
    }


    /**
     * @return bool
     * @throws Exception
     */
    private function init()
    {

        /*
         * If update command exists
         * */
        if (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'update') {
            Output::print($this->logo, 3, 'yellow');

            $this->update();
        } elseif (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'install') {
            $this->install();
        } else {
            $this->help();
        }


        return true;
    }

    /**
     * Install command
     * Creating instance directories
     * Running composer installation
     */
    private function install()
    {
        Output::print("=> Installing nardivan: ", false, 'yellow');

        if (!file_exists($this->instance_directory) && !is_dir($this->instance_directory)) {
            mkdir($this->instance_directory);
        }

        if (!file_exists($this->environment_dir) && !is_dir($this->environment_dir)) {
            mkdir($this->environment_dir);
        }

        Output::print("success", true, 'light_green');
    }

    private static function removeDirectory($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        self::removeDirectory($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Delete directory with contain files
     *
     * @param $file
     * @return bool
     */
    private static function deleteFile($file)
    {
        if (!file_exists($file)) {
            return true;
        }
        if (!is_writable($file)) {
            return false;
        }

        if (is_link($file)) {
            unlink($file);
        } elseif (is_dir($file)) {
            self::removeDirectory($file);
        } elseif (is_file($file)) {
            unlink($file);
        }
        return true;
    }


    private static function execCommand($directory, $command)
    {
        system(sprintf('cd %s && %s', $directory, $command));
    }

    /**
     * @param $directory
     * @param array $commands
     */
    private static function execCommands($directory, $commands)
    {
        foreach ($commands as $command) {
            self::execCommand($directory, $command);
        }
    }

    private static function dirIsEmpty($dir)
    {
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                closedir($handle);
                return false;
            }
        }
        closedir($handle);
        return true;
    }

    /**
     * Update command
     *
     * To update all repos and create symlinks
     * @param bool $ignore_changes
     */
    private function update()
    {
        Output::tree("Update", 1, 'green');

        $scripts = $this->getConfig()->getScripts();

        if ($scripts->isActive()) {

            self::execCommands($this->getConfig()->getDirectory(), $scripts->getPreUpdate());
        }
        $this->updateEnvironments();

        self::execCommands($this->getConfig()->getDirectory(), $scripts->getPostUpdate());
    }


    /**
     * Update Environments
     *
     */
    private function updateEnvironments()
    {

        Output::tree("Update environments", 2, 'green');

        $ignore_changes = in_array('--ignore-changes', $_SERVER['argv'])
            || in_array('-ic', $_SERVER['argv']);

        if ($ignore_changes) {
            Output::warning("Ignoring all changes.");
        }

        $environments_scripts = $this->getConfig()->getEnvironmentsScripts();

        foreach ($this->getConfig()->getEnvironments() as $index => $environment) {

            Output::tree(($index + 1) . '. ' . $environment->getName(), 3, 'yellow');

            if (!$environment->isActive() ||(
                    !empty($environment->getSpecialCommandArguments())
                    && count(array_intersect($environment->getSpecialCommandArguments(), $_SERVER['argv']))==0
                )) {
                Output::tree("Skipped.",4,'warning');
                continue;
            }


            $scripts = $environment->getScripts();

            $is_use_symlink_method = $environment->isUseSymlinkMethod() === null ?
                $this->getConfig()->isUseSymlinkMethod() : $environment->isUseSymlinkMethod();

            /*
             * Doing filesystem actions before
             * Updating environment
             * */
            if ($is_use_symlink_method) {

                $dir = sprintf('%s/%s/%s',
                    $this->instance_directory,
                    $this->environment_dir,
                    $environment->getName()
                );

                if (!file_exists($dir)) {
                    mkdir($dir);
                }

                $target_dir = sprintf('%s/%s',
                    $this->getConfig()->getDirectory(), $environment->getTarget());

                if (!self::deleteFile($target_dir)) {
                    Output::tree("You dont have write access to ($target_dir) directory.", 4, 'error');
                    continue;
                }

                $target_parts = explode('/', $target_dir);

                $target_dir_name = $target_parts[count($target_parts) - 1];

                /*
                 * Building backtrace relative path prefix
                 * */
                $dir_relative_prefix = str_repeat("../", count($target_parts) - 1);
                $dir_relative = './' . $dir_relative_prefix . $dir . '/';

                /*
                 * Unset target name from target path to get target directory
                 * */
                unset($target_parts[count($target_parts) - 1]);
                $target_relative_dir = implode('/', $target_parts);
                $pwd = getcwd();

                chdir($target_relative_dir);
                exec('ln -s ' . $dir_relative . ' ' . $target_dir_name);
                chdir($pwd);


            } else {
                $dir = sprintf('%s/%s/%s',
                    $this->pwd, $this->getConfig()->getDirectory(), $environment->getTarget());
                if (is_link($dir)) {
                    if (!self::deleteFile($dir)) {
                        Output::tree("You dont have write access to ($dir) directory.", 4, 'error');
                        continue;
                    }
                } elseif (!file_exists($dir)) {
                    mkdir($dir);
                }
            }

            /*
            * Execute pre update scripts
            * */
            Output::tree("Running pre-update actions", 4, 'blue');
            self::execCommands($dir, array_merge($scripts->getPreUpdate(), $environments_scripts->getPreUpdate()));

            /*
             * Running git actions
             * */
            if ($environment->getSource()->isActive() && $environment->getSource()->getGit()->isActive()) {

                $git = $environment->getSource()->getGit();
                Output::tree("Running git actions", 4, 'blue');

                if (file_exists($dir . '/.git') && is_dir($dir . '/.git')) {

                    if ($ignore_changes) {
                        system(sprintf('git -C %s fetch --all', $dir));
                        system(sprintf("git -C %s reset --hard origin/%s", $dir, $git->getBranch()));
                    } else {
                        system(sprintf("git -C %s pull", $dir));
                    }

                } else {
                    if (!$ignore_changes && !self::dirIsEmpty($dir)) {

                        Output::tree("The directory (" . $dir . ") is not empty.", 4, 'error');
                        continue;
                    }

                    system(sprintf("git clone -b %s %s %s",
                            $git->getBranch(),
                            $git->getUrl(),
                            $dir
                        )
                    );
                }
            }

            /*
             * Execute post update scripts
             * */
            Output::tree("Running post-update actions", 4, 'blue');
            self::execCommands($dir, array_merge($scripts->getPostUpdate(), $environments_scripts->getPostUpdate()));

        }
    }

    /**
     * Help Command get all commands list
     */
    private function help()
    {
        Output::print("Please choose command.");
        Output::print("install: First time run, to create instances directories.");
        Output::print("update: Updates repos and create symlinks");
        /*Todo: create commands list*/
    }


    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @param Config $config
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

}