<?php


namespace NovemBit\nardivan;


class Environment
{
    private $active;

    private $target;

    private $name;

    private $source;

    private $scripts;

    private $use_symlink_method;

    private $special_command_arguments = [];

    public function __construct($config)
    {
        if ($config === null) {
            $this->setActive(false);
            return;
        }

        $this->setName($config['name'] ?? null);

        $this->setTarget($config['target'] ?? null);

        $this->setSource(new Source($config['source'] ?? null));

        $this->setScripts(New Scripts($config['scripts'] ?? []));

        $this->setUseSymlinkMethod($config['use-symlink-method'] ?? null);

        $this->setSpecialCommandArguments($config['special-command-arguments'] ?? []);

        $this->setActive(true);

    }

    public function getTarget()
    {
        return $this->target;
    }

    private function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    private function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return Source|null
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param Source $source
     */
    private function setSource(Source $source)
    {
        $this->source = $source;
    }

    /**
     * @return Scripts
     */
    public function getScripts(): Scripts
    {
        return $this->scripts;
    }

    /**
     * @param Scripts $scripts
     */
    public function setScripts(Scripts $scripts)
    {
        $this->scripts = $scripts;
    }

    /**
     * @return bool|null
     */
    public function isUseSymlinkMethod()
    {
        return $this->use_symlink_method;
    }

    /**
     * @param bool|null $use_symlink_method
     */
    public function setUseSymlinkMethod($use_symlink_method)
    {
        $this->use_symlink_method = $use_symlink_method;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active)
    {
        $this->active = $active;
    }

    /**
     * @return array
     */
    public function getSpecialCommandArguments(): array
    {
        return $this->special_command_arguments;
    }

    /**
     * @param array $special_command_arguments
     */
    public function setSpecialCommandArguments(array $special_command_arguments)
    {
        array_walk($special_command_arguments, function (&$value, $key) {
            $value="---$value";
        });

        $this->special_command_arguments = $special_command_arguments;
    }


}