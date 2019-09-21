<?php


namespace NovemBit\nardivan;


class Environment
{
    private $target;

    private $name;

    private $git;

    private $scripts;

    public function __construct(array $config)
    {
        $this->setName($config['name'] ?? null);

        $this->setTarget($config['target'] ?? null);

        $this->setGit(new Git($config['git'] ?? []));

        $this->setScripts(New Scripts($config['scripts'] ?? []));

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
     * @return Git|null
     */
    public function getGit()
    {
        return $this->git;
    }

    /**
     * @param mixed $git
     */
    private function setGit(Git $git)
    {
        $this->git = $git;
    }

    /**
     * @return Scripts
     */
    public function getScripts() : Scripts
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


}