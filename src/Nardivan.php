<?php

namespace NovemBit\nardivan;

use InvalidArgumentException;

class Nardivan
{

    public $pwd;

    public $repos;

    public $directory;

    public $instance_directory = ".nardivan";

    public $use_symlink_structure;
    /**
     * @var string
     */
    private $composer_dir;

    /**
     * nardivan constructor.
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
     */
    private static function print($text)
    {
        echo $text . PHP_EOL;
    }

    private function init()
    {
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

    private function install()
    {
        self::print("=> Installing nardivan:");

        if (!file_exists($this->instance_directory) && !is_dir($this->instance_directory)) {
            mkdir($this->instance_directory);
        }

        if (!file_exists($this->composer_dir) && !is_dir($this->composer_dir)) {
            mkdir($this->composer_dir);
        }

        unset($_SERVER['argv'][1]);

        $this->installComposer();

        self::print("<= Nardivan install: successful.");

    }

    /**
     *
     */
    private function installComposer()
    {
        self::print("==> Installing composer:");

        chdir($this->composer_dir);

        copy('https://getcomposer.org/installer', 'composer-setup.php');
        if (hash_file('sha384',
                'composer-setup.php') === 'a5c698ffe4b8e849a443b120cd5ba38043260d5c4023dbf93e1558871f1f07f58274fc6f4c93bcfd858c6bd0775cd8d1') {
        } else {
            unlink('composer-setup.php');
            return;
        }

        exec('php composer-setup.php');

        unlink('composer-setup.php');

        if (file_exists('composer.phar')) {
            self::print('==> Composer installed: successful.');
        }
    }

    private static function deleteDir($dirPath) {
        system("rm -rf ".escapeshellarg($dirPath));

    }

    private function createComposerConfig()
    {
        /*
         * Building composer config file
         * */
        $composer_config = [
            'name' => 'nardivan/' . md5(time()),
            'description' => md5(time()),
        ];

        /** @var Repo $repo */
        foreach ($this->repos as $repo) {

            self::print($repo->name . " ==> " . $repo->target);

            $composer_repo_config = $repo->getComposerConfig();
            $composer_config['repositories'][] = $composer_repo_config;
            $composer_config['require'][$composer_repo_config['package']['name']] = $composer_repo_config['package']['version'];

        }

        $json = json_encode($composer_config, JSON_PRETTY_PRINT);

        $composer_json_path = $this->composer_dir . '/composer.json';

        file_put_contents($composer_json_path, $json);
    }

    private function update()
    {
        self::print("=> Update repos:");

        /*
         * Create composer.json file
         * */
        $this->createComposerConfig();

        /*
         * Changing current directory to composer directory
         * */
        chdir($this->composer_dir);

        /*
         * Running composer update command
         * */
        exec('php composer.phar update', $output, $result);


        /**
         * Creating symlinks for repos on target folder
         *
         * @var Repo $repo
         */
        foreach ($this->repos as $repo) {

            /*
             * Changing current directory to root of project
             * */
            chdir($this->pwd);
            self::print("===> Linking repo: " . $repo->name);

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
                if(is_dir($repo->name)){
                    self::deleteDir($repo->name);
                }else {
                    unlink($repo->name);
                }
            }
            /*
             * Creating symlink
             * */
            exec('ln -s ' . $repo_dir);
        }

    }

    private function help()
    {
        self::print("Please choose command.");
    }

    private function fetchRepos()
    {
        foreach ($this->repos as &$repo) {
            $repo = new Repo($repo);
        }
    }

}