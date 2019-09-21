<?php


namespace NovemBit\nardivan;


class Git
{

    private $url;

    private $branch;

    public function __construct(array $config)
    {

        $this->setBranch($config['branch'] ?? null);

        $this->setUrl($config['url'] ?? null);

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

}