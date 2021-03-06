<?php

/*
 * This file is part of the daryl/php-aplayer.
 *
 * (c) daryl <daryl.moe@outlook.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Aplayer.php.
 *
 * @author    daryl <daryl.moe@outlook.com>
 * @copyright 2017 daryl <daryl.moe@outlook.com
 *
 * @see      https://github.com/Daryl-L
 * @see      https://is.daryl.moe
 */

namespace Daryl;

use \Metowolf\Meting;

class Aplayer
{
    /**
     * @var \Metowolf\Meting
     */
    protected $music;

    /**
     * @var bool
     */
    protected $narrow;

    /**
     * @var bool
     */
    protected $autoplay;

    /**
     * @var string
     */
    protected $showlrc;

    /**
     * @var bool
     */
    protected $mutex;

    /**
     * @var string
     */
    protected $theme;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var string
     */
    protected $preload;

    /**
     * @var int
     */
    protected $listmaxheight;

    /**
     * @var string
     */
    protected $song;

    /**
     * @var string
     */
    protected $playlist;

    /**
     * @var string
     */
    protected $songType;

    /**
     * @var string
     */
    protected $elementId;

    public function __construct($site = 'netease')
    {
        $this->music         = new Meting($site);
        $this->narrow        = false;
        $this->autoplay      = true;
        $this->showlrc       = 1;
        $this->mutex         = true;
        $this->theme         = '#e6d0b2';
        $this->mode          = 'random';
        $this->preload       = 'metadata';
        $this->listmaxheight = 513;
        $this->song          = '22817183';
        $this->playlist      = '476998713';
        $this->songType      = 'song';
        $this->elementId     = 'player1';
    }

    /**
     * To get the format array of song.
     *
     * @param int $song
     *
     * @return array
     */
    protected function getSongs($song)
    {
        $songs = $this->music->song($song);
        $songs = json_decode($songs, true);
        if (400 == $songs['code']) {
            throw new \Exception($songs['msg']);
        }

        $lyric = $this->music->lyric($song);
        $lyric = json_decode($lyric, true);

        $pic = $this->music->pic($songs['songs'][0]['al']['pic']);
        $pic = json_decode($pic, true);

        $url = $this->music->url($song);
        $url = json_decode($url, true);

        return [
            'title' => $songs['songs'][0]['name'],
            'author' => $songs['songs'][0]['ar'][0]['name'],
            'lrc' => @$lyric['lrc']['lyric'],
            'pic'  => $pic['url'],
            'url' => $url['data'][0]['url'],
        ];
    }

    /**
     * To get the format array of playlist.
     *
     * @return array
     */
    protected function getPlaylist()
    {
        $playlist = $this->music->playlist($this->playlist);
        $songs = json_decode($playlist, true);
        if (400 == $songs['code']) {
            throw new \Exception($songs['msg']);
        }

        $res = [];
        foreach ($songs['playlist']['tracks'] as $key => $song) {
            $res[] = $this->getSongs($song['id']);
        }

        return $res;
    }

    /**
     * To print the full text of aplayer.
     */
    public function out()
    {
        if ('song' == $this->songType) {
            try {
                $songs = $this->getSongs($this->song);
            } catch (Exception $e) {
                exit($e->getMessage());
            }
        } elseif ('playlist' == $this->songType) {
            try {
                $songs = $this->getPlaylist($this->song);
            } catch (Exception $e) {
                exit($e->getMessage());
            }
        }

        $options = [
            'element'       => 'document.getElementById(\'' . $this->elementId . '\')',
            'narrow'        => $this->narrow,
            'showlrc'       => $this->showlrc,
            'mutex'         => $this->mutex,
            'theme'         => $this->theme,
            'mode'          => $this->mode,
            'preload'       => $this->preload,
            'listmaxheight' => $this->listmaxheight . 'px',
            'music'         => $songs,
        ];
        $str = str_replace(
            '"document.getElementById(\'' . $this->elementId . '\')"', 
            'document.getElementById(\'' . $this->elementId . '\')', 
            json_encode($options, JSON_UNESCAPED_SLASHES)
        );
        $src = '<div id="' . $this->elementId . '" class="aplayer"></div>';
        $src .= '<script src="//cdn.bootcss.com/aplayer/1.6.0/APlayer.min.js"></script>';
        $src .= '<script>';
        $src .= 'var ap=new APlayer('. $str . ');';
        $src .= '</script>';

        echo $src;
    }

    /**
     * To set a song id.
     *
     * @param string $song
     */
    public function setSong($song = '22817204')
    {
        if (!is_numeric($song)) {
            throw new \Exception('Invalid id of song.');
        }

        $this->song = $song;
    }

    /**
     * To set a playlist id.
     *
     * @param string $playlist
     */
    public function setPlaylist($playlist = '476998713')
    {
        if (!is_numeric($playlist)) {
            throw new \Exception('Invalid id of playlist.');
        }

        $this->playlist = $playlist;
    }

    /**
     * Sets the value of narrow.
     *
     * @param bool $narrow
     */
    public function setNarrow($narrow)
    {
        $this->narrow = $narrow ? true : false;
    }

    /**
     * Sets the value of autoplay.
     *
     * @param bool $autoplay
     */
    public function setAutoplay($autoplay)
    {
        $this->autoplay = $autoplay ? true : false;
    }

    /**
     * Sets the value of showlrc.
     *
     * @param int $showlrc
     */
    public function setShowlrc($showlrc)
    {
        if (in_array($show, [0, 1, 2])) {
            $this->showlrc = $showlrc;
        } else {
            throw new \Exception('Invalid value for showlrc.');
        }
    }

    /**
     * Sets the value of mutex.
     *
     * @param bool $mutex
     */
    public function setMutex($mutex)
    {
        $this->mutex = $mutex ? true : false;
    }

    /**
     * Sets the value of theme.
     *
     * @param string $theme
     */
    public function setTheme($theme)
    {
        $themeHex = substr($theme, 1);
        if (!ctype_xdigit($themeHex)) {
            throw new \Exception('Invalid value for theme.');
        } elseif (0x000000 > '0x' . $themeHex && 0xffffff < '0x' . $themeHex) {
            throw new \Exception('Invalid value for theme.');
        } else {
            $this->theme = $theme;
        }
    }

    /**
     * Sets the value of mode.
     *
     * @param string $mode
     */
    public function setMode($mode)
    {
        if (in_array($mode, ['random', 'single', 'circulation', 'order'])) {
            $this->mode = $mode;
        } else {
            throw new \Exception('Invalid value for mode.');
        }
    }

    /**
     * Sets the value of preload.
     *
     * @param string $preload
     */
    public function setPreload($preload)
    {
        if (in_array($prelo, ['none', 'metadata', 'auto'])) {
            throw new \Exception('Invalid value for preload.');
        }
        return $this->preload;
    }

    /**
     * Sets the value of listmaxheight.
     *
     * @param int listmaxheight
     */
    public function setListmaxheight($listmaxheight)
    {
        if (is_numeric($listmaxheight)) {
            $this->listmaxheight = $listmaxheight;
        } else {
            throw new \Exception('Invalid value for listmaxheight.');
        }
    }

    /**
     * Sets the value of songType.
     *
     * @param string $songType
     */
    public function setSongType($songType)
    {
        if (in_array($songType, ['song', 'playlist'])) {
            $this->songType = $songType;
        } else {
            throw new \Exception('Invalid value for song type.');
        }
    }

    /**
     * Set the id of the element which aplayer displays on.
     *
     * @param string $elementId
     */
    public function setElementId($elementId)
    {
        $this->elementId = $elementId;
    }
}