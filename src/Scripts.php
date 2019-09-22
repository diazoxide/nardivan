<?php


namespace NovemBit\nardivan;


class Scripts
{

    private $active;

    private $pre_install = [];
    private $post_install = [];
    private $pre_update = [];
    private $post_update = [];

    public function __construct($config)
    {
        if ($config === null) {
            $this->setActive(false);
            return;
        }
        $this->setPreInstall($config['pre-install'] ?? []);

        $this->setPostInstall($config['post-install'] ?? []);

        $this->setPreUpdate($config['pre-update'] ?? []);

        $this->setPostUpdate($config['post-update'] ?? []);

        $this->setActive(true);

    }

    /**
     * @return array
     */
    public function getPostUpdate(): array
    {
        return $this->post_update;
    }

    /**
     * @param array $post_update
     */
    public function setPostUpdate(array $post_update)
    {
        $this->post_update = $post_update;
    }

    /**
     * @return array
     */
    public function getPreUpdate(): array
    {
        return $this->pre_update;
    }

    /**
     * @param array $pre_update
     */
    public function setPreUpdate(array $pre_update)
    {
        $this->pre_update = $pre_update;
    }

    /**
     * @return array
     */
    public function getPostInstall(): array
    {
        return $this->post_install;
    }

    /**
     * @param array $post_install
     */
    public function setPostInstall(array $post_install)
    {
        $this->post_install = $post_install;
    }

    /**
     * @return array
     */
    public function getPreInstall(): array
    {
        return $this->pre_install;
    }

    /**
     * @param array $pre_install
     */
    public function setPreInstall(array $pre_install)
    {
        $this->pre_install = $pre_install;
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