<?php

namespace Mihaeu\SubModifier;

define('S_TO_MS',   1000);
define('MIN_TO_MS', S_TO_MS * 60);
define('H_TO_MS',   MIN_TO_MS * 60);

/**
 * SubModifier
 *
 * SubModifier modifies subtitle files by increasing or decreasing
 * the subtitle files and by re-indexing the subtitles (in case a subtitle file
 * has to be split up for multi-part movies).
 * 
 * Currently only supports .srt files.
 *
 * @author Michael Haeuslmann <haeuslmann@gmail.com>
 */
class SubModifier
{
    /**
     * @var string
     */
    private $indexRegex = '/^\d+$/';
    
    /**
     * @var string
     */
    private $srtTimeFormat = '%02d:%02d:%02d,%03d';
    
    /**
     * @var string
     */
    private $srtTimeLineRegex = '/
        (?<from>\d\d:\d\d:\d\d,\d\d\d)  # from timestamp, e.g. 00:00:00,000
        \ -->\ 
        (?<to>\d\d:\d\d:\d\d,\d\d\d)    # to timestamp, e.g. 00:00:00,000
        /x';
    
    /**
     * @var string
     */
    private $srtTimeRegex = '/
        (?<neg>-)?              # optional minus
        (?<h>\d\d)              # hours from 00 to 99
        :(?<min>[0-5][0-9])     # minutes from 00 to 59
        :(?<s>[0-5][0-9])       # seconds from 00 to 59
        ,(?<ms>\d\d\d)          # ms from 000 to 999
        /x';

    /**
     * Modifies the timestamps in an .srt subtitle file.
     * 
     * @param  string $srtFile
     * @param  string $offsetSrtTime in srt time format
     * @return void
     */
    public function modifySrtFile($srtFile, $offsetSrtTime)
    {
        if (!$this->isSrtTime($offsetSrtTime)) {
            throw new \InvalidArgumentException(
                "$offsetSrtTime is not a valid SRT time format (eg. 01:02:03,004).\n"
            );
        }
        if (!is_readable($srtFile)) {
            throw new \InvalidArgumentException(
                "$srtFile file does not exist or is not readable.\n"
            );
        }

        $entryNumber = 1;
        $offsetTimeMs = $this->srtTimeToMs($offsetSrtTime);

        $contents = $this->normalizeLinefeeds(file_get_contents($srtFile));
        $lines = explode("\n", $contents);
        $output = [];
        foreach ($lines as $line) {
            $matches = [];
            if (preg_match($this->indexRegex, $line)) {
                $output[] = $entryNumber;
                ++$entryNumber;
            } else if (preg_match($this->srtTimeLineRegex, $line, $matches)) {
                $fromMs = $this->srtTimeToMs($matches['from']) - $offsetTimeMs;
                $toMs   = $this->srtTimeToMs($matches['to']) - $offsetTimeMs;
                $output[] = $this->msToSrtFormat($fromMs).' --> '.$this->msToSrtFormat($toMs);
            } else {
                $output[] = $line;
            }
        }
        return implode(PHP_EOL, $output);
    }

    /**
     * Converts the srt time format into ms.
     * 
     * @param  string $srtFormatString
     * @return int
     */
    public function srtTimeToMs($srtFormatString)
    {
        $matches = [];
        preg_match($this->srtTimeRegex, $srtFormatString, $matches);
        $neg = ($matches['neg'] === '-') ? -1 : 1;
        return $neg * 
            ($matches['ms'] 
            + ($matches['s'] * S_TO_MS)
            + ($matches['min'] * MIN_TO_MS)
            + ($matches['h'] * H_TO_MS));
    }

    /**
     * Converts ms to the srt time format.
     * 
     * @param  int $ms
     * @return string
     */
    public function msToSrtFormat($ms)
    {
        $h   = floor($ms / H_TO_MS);
        $min = floor(($ms - ($h * H_TO_MS)) / MIN_TO_MS);
        $s   = floor(($ms - ($h * H_TO_MS) - ($min * MIN_TO_MS)) / S_TO_MS);
        $ms  = $ms - ($h * H_TO_MS) - ($min * MIN_TO_MS) - ($s * S_TO_MS);

        return sprintf($this->srtTimeFormat, $h, $min, $s, $ms);
    }

    /**
     * Tests if a given string follows the srt time format: h:min:s,ms
     * 
     * @param  strin  $srtTime
     * @return bool
     */
    public function isSrtTime($srtTime)
    {
        return 1 === preg_match($this->srtTimeRegex, $srtTime);
    }

    /**
     * Normalizes the different linefeeds of different OS (Mac: \r, Windows: \r\n) to
     * contain only UNIX style linefeeds (\n).
     *
     * Order matters! First remove windows, then Mac linefeeds, otherwise we might
     * end up having twice the linefeeds.
     * 
     * @param string $content
     * @return string
     */
    public function normalizeLinefeeds($content)
    {
        $contentWithoutWindowsLinefeeds =
            str_replace("\r\n", "\n", $content);
        $contentWithoutMacLinefeeds =
            str_replace("\r", "\n", $contentWithoutWindowsLinefeeds);

        return $contentWithoutMacLinefeeds;
    }
}
