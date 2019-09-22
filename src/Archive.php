<?php


namespace NovemBit\nardivan;


class Archive
{

    private $active;

    private $url;

    private $host;
    private $port;
    private $username;
    private $password;
    private $path;

    public function __construct($config)
    {

        if ($config === null) {
            $this->setActive(false);
            return;
        }

        $this->setUrl($config['url'] ?? null);

        if ($this->getUrl() != null) {
            $parsed = parse_url($this->getUrl());
            $this->setHost($parsed['host'] ?? null);
            $this->setPort($parsed['port'] ?? null);
            $this->setUsername($parsed['user'] ?? null);
            $this->setPassword($parsed['pass'] ?? null);
            $this->setPath($parsed['path'] ?? null);
        } else {
            $this->setHost($config['host'] ?? null);
            $this->setUsername($config['username'] ?? null);
            $this->setPassword($config['password'] ?? null);
            $this->setPath($config['path'] ?? null);
            $this->setPort($config['port'] ?? 22);
        }

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

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param mixed $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

}