<?php


namespace NovemBit\nardivan;


class Repo
{
    public $target;

    public $source;

    public $name;

    public function __construct(array $config)
    {
        foreach ($config as $name => $value) {
            $this->{$name} = $value;
        }

    }

    public function getComposerConfig()
    {
        return [
            'type' => 'package',
            'package' => [
                'name' => 'nardivan/'.$this->name,
                'version' => "1",
                'source' => $this->source
            ]
        ];
    }
}