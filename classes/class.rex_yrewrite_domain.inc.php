<?php

/**
 * YREWRITE Addon
 * @author jan.kristinus@yakamara.de
 * @author gregor.harlan@redaxo.org
 * @package redaxo4.5
 */

class rex_yrewrite_domain
{
    private $name;
    private $mountId;
    private $startId;
    private $notfoundId;
    private $clangs;
    private $title;
    private $description;
    private $robots;

    function __construct($name, $mountId, $startId, $notfoundId, array $clangs = null, $title = '', $description = '', $robots = '')
    {
        global $REX;

        $this->name = $name;
        $this->mountId = $mountId;
        $this->startId = $startId;
        $this->notfoundId = $notfoundId;
        $this->clangs = is_null($clangs) ? array_keys($REX['CLANG']): $clangs;
        $this->title = $title;
        $this->description = $description;
        $this->robots = $robots;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getMountId()
    {
        return $this->mountId;
    }

    /**
     * @return int
     */
    public function getStartId()
    {
        return $this->startId;
    }

    /**
     * @return int
     */
    public function getNotfoundId()
    {
        return $this->notfoundId;
    }

    /**
     * @return array
     */
    public function getClangs()
    {
        return $this->clangs;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getRobots()
    {
        return $this->robots;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}
