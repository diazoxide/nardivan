<?php


namespace NovemBit\nardivan;


class Environment
{
    public $target;

    public $package;

    public function __construct(array $config)
    {
        $this->setPackage($config['package']);
        $this->setTarget($config['target']);
    }

    public function getComposerRepository()
    {

        return [
            'type' => 'package',
            'package' => (array)$this->package
        ];
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @param array $package
     */
    public function setPackage(array $package)
    {
        $this->package = new Package($package);
    }

}