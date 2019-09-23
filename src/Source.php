<?php


namespace NovemBit\nardivan;


class Source
{
    private $active;

    private $git;

    private $archive;

    public function __construct($config)
    {
        if ($config === null) {
            $this->setActive(false);
            return;
        }

        $this->setGit(new Git($config['git'] ?? null));
        $this->setArchive(new Archive($config['archive'] ?? null));

        $this->setActive(true);
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
     * @return Archive|null
     */
    public function getArchive()
    {
        return $this->archive;
    }

    /**
     * @param Archive $archive
     */
    private function setArchive(Archive $archive)
    {
        $this->archive = $archive;
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

}