<?php

namespace NovemBit\nardivan;


use Composer\Console\Application;
use Exception;
use Symfony\Component\Console\Input\ArrayInput;

class Nardivan
{

    public $logo = <<<html
  _  _                _  _                  
 | \| | __ _  _ _  __| |(_)__ __ __ _  _ _  
 | .` |/ _` || '_|/ _` || |\ V // _` || ' \ 
 |_|\_|\__,_||_|  \__,_||_| \_/ \__,_||_||_|
html;
    public $pwd;

    public $repos;

    public $directory;

    public $instance_directory = ".nardivan";

    /**
     * @var string
     */
    private $composer_dir;

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

        foreach ($config as $key => $item) {
            $this->{$key} = $item;
        }

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
    private static function print($text, $newline = true, $color = null, $background = null, $return = false)
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

        self::print($this->logo, 3, 'red');

        $this->fetchRepos();

        $this->composer_dir = $this->instance_directory . '/composer';

        if (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'update') {
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
        self::print("=> Installing nardivan:");

        if (!file_exists($this->instance_directory) && !is_dir($this->instance_directory)) {
            mkdir($this->instance_directory);
        }

        if (!file_exists($this->composer_dir) && !is_dir($this->composer_dir)) {
            mkdir($this->composer_dir);
        }
        self::print("<= Nardivan install: success.");
    }

    /**
     * Delete directory with contain files
     *
     * @param $dirPath
     */
    private static function deleteDir($dirPath)
    {
        exec("rm -rf " . escapeshellarg($dirPath));
    }

    /**
     * Generating composer.json
     */
    private function createComposerConfig()
    {
        self::print('==> Generate composer config file.', true, 'yellow');
        /*
         * Building composer config file
         * */
        $composer_config = [
            'name' => 'nardivan/' . md5(time()),
            'description' => md5(time()),
        ];

        /** @var Repo $repo */
        foreach ($this->repos as $key => $repo) {

            self::print('===> ' . ($key + 1) . '. ' . $repo->name . " -> " . $repo->target, true, 'light_blue');

            $composer_repo_config = $repo->getComposerConfig();
            $composer_config['repositories'][] = $composer_repo_config;
            $composer_config['require'][$composer_repo_config['package']['name']] = $composer_repo_config['package']['version'];

        }

        /*
         * Generate json config file
         * */
        $json = json_encode($composer_config, JSON_PRETTY_PRINT);

        $composer_json_path = $this->composer_dir . '/composer.json';

        file_put_contents($composer_json_path, $json);
    }

    /**
     * Running composer update command programmatically
     *
     * @param $command
     * @throws Exception
     */
    public function runComposerCommand($command)
    {
        self::print("==> Update composer : ", false, 'yellow');
        putenv('COMPOSER_HOME=' . __DIR__ . '/../vendor/bin/composer');
        // call `composer install` command programmatically
        $input = new ArrayInput(array('command' => $command, '--quiet'));
        $application = new Application();
        $application->setAutoExit(false);
        $application->run($input);
        self::print("success", true, 'green');

    }

    /**
     * Update command
     *
     * To update all repos and create symlinks
     * @throws Exception
     */
    private function update()
    {
        self::print("=> Update repos:", true, 'light_green');

        /*
         * Create composer.json file
         * */
        $this->createComposerConfig();

        /*
         * Changing current directory to composer directory
         * */
        chdir($this->composer_dir);

        /*
         * Run programmatically composer update command
         * */
        $this->runComposerCommand('update');

        self::print("==> Linking repos:", true, 'yellow');

        /**
         * Creating symlinks for repos on target folder
         *
         * @var Repo $repo
         */
        foreach ($this->repos as $key => $repo) {

            /*
             * Changing current directory to root of project
             * */
            chdir($this->pwd);

            self::print("===> " . ($key + 1) . ' ' . $repo->name, true, 'light_blue');

            /*
             * Building repo relative directory path
             * */
            $repo_dir = $this->composer_dir . "/vendor/nardivan/" . $repo->name;

            /*
             * Building target relative path
             * */
            $target = $this->directory . $repo->target;

            /*
             * Split path, to get count of path parts
             * And then unset target name from path
             * */
            $target_parts = explode('/', $target);

            /*
             * Building backtrace relative path prefix
             * */
            $repo_dir_prefix = str_repeat("../", count($target_parts) - 1);
            $repo_dir = './' . $repo_dir_prefix . $repo_dir . '/';

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
             * Remove old symlink or directory
             * */
            if (file_exists($repo->name)) {
                if (is_dir($repo->name)) {
                    self::deleteDir($repo->name);
                } else {
                    unlink($repo->name);
                }
            }
            /*
             * Creating symlink
             * */
            exec('ln -s ' . $repo_dir);
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
     * Creating repos objects
     */
    private function fetchRepos()
    {
        foreach ($this->repos as &$repo) {
            $repo = new Repo($repo);
        }
    }

}