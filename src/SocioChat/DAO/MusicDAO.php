<?php

namespace SocioChat\DAO;

use Core\DAO\DAOBase;

class MusicDAO extends DAOBase
{
	const TRACK_ID = 'track_id';
	const ARTIST = 'artist';
	const SONG = 'song';
	const QUALITY = 'quality';
	const URL = 'url';

	public function __construct()
	{
		parent::__construct(
			[
				self::TRACK_ID,
				self::ARTIST,
				self::SONG,
				self::QUALITY,
				self::URL,
			]
		);

		$this->dbTable = 'music_info';
	}

	public function getTrackId()
	{
		return $this[self::TRACK_ID];
	}

	public function getArtist()
	{
		return $this[self::ARTIST];
	}

	public function getSong()
	{
		return $this[self::SONG];
	}

    public function getUrl()
    {
        return $this[self::URL];
    }

	public function getQuality()
	{
		return $this[self::QUALITY];
	}

	public function getByTrackId($trackId)
	{
		return $this->getByPropId(self::TRACK_ID, $trackId);
	}

	public function setTrackId($trackId)
	{
		$this[self::TRACK_ID] = $trackId;
		return $this;
	}

	public function setArtist($artist)
	{
		$this[self::ARTIST] = $artist;
		return $this;
	}

	public function setSong($song)
	{
		$this[self::SONG] = $song;
		return $this;
	}

    public function setUrl($url)
    {
        $this[self::URL] = $url;
        return $this;
    }

	public function setQuality($quality)
	{
		$this[self::QUALITY] = $quality;
		return $this;
	}

	protected function getForeignProperties()
	{
		return [];
	}
}

