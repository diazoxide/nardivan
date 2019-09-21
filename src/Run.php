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
            echo "Please run this file only with CLI";
            die;
        }

        $this->pwd = $_SERVER['PWD'];

        if (!file_exists($this->pwd . '/nardivan.json')) {
            self::print('nardivan.json not detected');
            return false;
        }

        $json = file_get_contents($this->pwd . '/nardivan.json');
        $config = json_decode($json, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            self::print("Bad nardivan.json file");
            return false;
        }

        $this->setConfig(new Config($config));

        return $this->init();
    }

    /**
     * @param $text
     * @param bool $newline
     * @param null $color
     * @param null $background
     * @param bool $return
     * @return string
     */
    public static function print($text, $newline = true, $color = null, $background = null, $return = false)
    {
        $suffix = str_repeat(PHP_EOL, (int)$newline);
        $text = $text . $suffix;

        $text = Output::getColoredString($text, $color, $background);

        if (!$return) {
            echo $text;
        }
        return $text;
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
            self::print($this->logo, 3, 'yellow');
            $this->update(
                in_array('--ignore-changes', $_SERVER['argv'])
                || in_array('-ic', $_SERVER['argv'])
            );
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
        self::print("=> Installing nardivan: ", false, 'yellow');

        if (!file_exists($this->instance_directory) && !is_dir($this->instance_directory)) {
            mkdir($this->instance_directory);
        }

        if (!file_exists($this->environment_dir) && !is_dir($this->environment_dir)) {
            mkdir($this->environment_dir);
        }

        self::print("success", true, 'light_green');
    }

    private static function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        self::rrmdir($dir . "/" . $object);
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
     * @param $dirname
     * @return bool
     */
    private static function deleteFile($file)
    {
        if(is_link($file)){
            unlink($file);
        }elseif (is_dir($file)) {
            self::rrmdir($file);
        } else {
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

    /**
     * Update command
     *
     * To update all repos and create symlinks
     * @param bool $ignore_changes
     */
    private function update($ignore_changes = false)
    {
        self::print("=> Update repos:", false, 'light_green');

        if ($ignore_changes) {
            self::print(" => Warning: Ignoring all changes.", true, 'yellow');
        }

        $environments_scripts = $this->getConfig()->getEnvironmentsScripts();

        foreach ($this->getConfig()->getEnvironments() as $environment) {

            self::print('==> ' . $environment->getName(), true, 'light_red');

            $scripts = $environment->getScripts();

            if ($this->getConfig()->isUseSymlinkMethod()) {
                $dir = sprintf('%s/%s/%s',
                    $this->instance_directory, $this->environment_dir, $environment->getName());

                $target_dir = sprintf('%s/%s',
                    $this->getConfig()->getDirectory(), $environment->getTarget());

                self::deleteFile($target_dir);

                $target_parts = explode('/', $target_dir);

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
                exec('ln -s ' . $dir_relative);
                chdir($pwd);

            } else {
                $dir = sprintf('%s/%s/%s',
                    $this->pwd, $this->getConfig()->getDirectory(), $environment->getTarget());
                self::print($dir);
                if (is_link($dir)) {
                    unlink($dir);
                } elseif (file_exists($dir)) {
                    if (!file_exists($dir . '/.git') || !is_dir($dir . '/.git')) {
                        self::deleteFile($dir);
                    }
                } else {
                    mkdir($dir);
                }
            }


            self::execCommands($dir, $scripts->getPreUpdate());
            self::execCommands($dir, $environments_scripts->getPreUpdate());

            if (file_exists($dir . '/.git') && is_dir($dir . '/.git')) {

                if ($ignore_changes) {
                    system(sprintf('git -C %s fetch --all', $dir));
                    system(sprintf("git -C %s reset --hard origin/%s", $dir, $environment->getGit()->getBranch()));
                } else {
                    system(sprintf("git -C %s pull", $dir));
                }

            } else {
                system(sprintf("git clone -b %s %s %s",
                        $environment->getGit()->getBranch(),
                        $environment->getGit()->getUrl(),
                        $dir
                    )
                );
            }
            self::execCommands($dir, $scripts->getPostUpdate());
            self::execCommands($dir, $environments_scripts->getPostUpdate());
        }

        die;
        self::print("==> Linking repos:", true, 'yellow');

        /**
         * Creating symlinks for repos on target folder
         *
         * @var Environment $environment
         */
        foreach ($this->environments as $key => $environment) {

            /*
             * Changing current directory to root of project
             * */
            chdir($this->pwd);

            self::print("===> " . ($key + 1) . ' ' . $environment->getName(), true, 'light_blue');

            /*
             * Building repo relative directory path
             * */
            $dir_relative = $this->environment_dir . "/" . $environment->getName();

            /*
             * Building target relative path
             * */
            $target = $this->directory . $environment->getTarget();
            if (file_exists($target)) {
                if (is_dir($target)) {
                    self::deleteDir($target);
                } else {
                    unlink($target);
                }
            }

            /*
             * Split path, to get count of path parts
             * And then unset target name from path
             * */
            $target_parts = explode('/', $target);

            /*
             * Building backtrace relative path prefix
             * */
            $dir_relative_prefix = str_repeat("../", count($target_parts) - 1);
            $dir_relative = './' . $dir_relative_prefix . $dir_relative . '/';

            /*
             * Unset target name from target path to get target directory
             * */
            unset($target_parts[count($target_parts) - 1]);
            $target_dir = implode('/', $target_parts);

            /*
             * Changing current directory to target directory
             * */
            chdir($target_dir);

            /*
             * Creating symlink
             * */
            exec('ln -s ' . $dir_relative);
        }
        self::print("==> Linking repos: success", true, 'green');

        self::print("<= Update repos: success", true, 'green');
    }

    /**
     * Help Command get all commands list
     */
    private function help()
    {
        self::print("Please choose command.");
        self::print("install: First time run, to create instances directories.");
        self::print("update: Updates repos and create symlinks");
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