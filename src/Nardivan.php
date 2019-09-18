<?php

namespace NovemBit\nardivan;

class Nardivan
{

    public $pwd;

    public $repos;

    public $directory;

    public $use_symlink_structure;

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

        if (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'update') {
            $this->update();
        } else {
            $this->help();
        }

        return true;
    }

    private function update()
    {
        foreach ($this->repos as $name => $config) {
            self::print("Repo: " . $name . " - " . $config->url);
        }
    }

    private function help()
    {
        self::print("Please choose command.");
    }

    private function fetchRepos()
    {
        foreach ($this->repos as $name => &$repo) {
            $repo = new Repo($repo);
        }
    }

}