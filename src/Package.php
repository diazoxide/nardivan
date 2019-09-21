<?php


namespace NovemBit\nardivan;


class Package
{

    public $source;

    public $name;

    public $version;

    public function __construct(array $config)
    {
        foreach ($config as $name => $value) {
            $this->{$name} = $value;
        }

        if(!isset($this->version)){
            $this->version = date('Y.m.d.h.i.s');
        }

    }

}