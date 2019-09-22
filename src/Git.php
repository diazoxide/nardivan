<?php


namespace NovemBit\nardivan;


class Git
{

    private $active;

    private $url;

    private $branch;

    public function __construct($config)
    {

        if ($config === null) {
            $this->setActive(false);
            return;
        }

        $this->setBranch($config['branch'] ?? null);

        $this->setUrl($config['url'] ?? null);

        $this->setActive(true);

    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    private function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * @param mixed $branch
     */
    private function setBranch($branch)
    {
        $this->branch = $branch;
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
    private function setActive(bool $active)
    {
        $this->active = $active;
    }

}