<?php


namespace NovemBit\nardivan;


class Config
{


    private $environments = [];

    private $directory;

    private $scripts;

    private $environments_scripts;

    private $linking = true;

    public function __construct($config)
    {
        $this->setLinking($config['linking'] ?? true);

        $this->setEnvironments($config['environments'] ?? []);

        $this->setDirectory($config['directory'] ?? null);

        $this->setScripts(New Scripts($config['scripts']) ?? null);

        $this->setEnvironmentsScripts(New Scripts($config['environments-scripts']) ?? null);
    }

    /**
     * @return Environment[]
     */
    public function getEnvironments(): array
    {
        return $this->environments;
    }

    /**
     * @param array $environments
     */
    public function setEnvironments(array $environments)
    {
        foreach ($environments as $environment) {
            $this->addEnvironment(new Environment($environment));
        }
    }

    /**
     * @return bool
     */
    public function isLinking(): bool
    {
        return $this->linking;
    }

    /**
     * @param bool $linking
     */
    public function setLinking(bool $linking)
    {
        $this->linking = $linking;
    }

    /**
     * @param Environment $environment
     */
    private function addEnvironment(Environment $environment)
    {
        $this->environments[] = $environment;
    }

    /**
     * @return mixed
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @param mixed $directory
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
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
     * @return Scripts
     */
    public function getEnvironmentsScripts(): Scripts
    {
        return $this->environments_scripts;
    }

    /**
     * @param Scripts $scripts
     */
    public function setEnvironmentsScripts(Scripts $scripts)
    {
        $this->environments_scripts = $scripts;
    }

}