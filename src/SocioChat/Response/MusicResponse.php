<?php

namespace SocioChat\Response;


class MusicResponse extends Response
{
    protected $musicInfo;

    public function setInfo(array $info)
    {
        $this->musicInfo = $info;
        return $this;
    }

    /**
     * @return null
     */
    public function getInfo()
    {
        return $this->musicInfo;
    }
}