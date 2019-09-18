<?php


namespace NovemBit\nardivan;


class Repo
{
    public $url;

    public function __construct(array $config)
    {
        foreach ($config as $name => $value) {
            $this->{$name} = $value;
        }

    }

}