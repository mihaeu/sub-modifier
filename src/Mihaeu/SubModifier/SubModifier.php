<?php

namespace Mihaeu\SubModifier;

define('S_TO_MS', 1000);
define('MIN_TO_MS', S_TO_MS * 60);
define('H_TO_MS', MIN_TO_MS * 60);

class SubModifier
{
    public $indexRegex = '/^\d+$/';
    public $timeRegex = '/(?<from>\d\d:\d\d:\d\d,\d\d\d) --> (?<to>\d\d:\d\d:\d\d,\d\d\d)/';
    public $srtTimeFormat = 'H:i:s,u';

    public function parseSrtFile($srtFile, $offsetSrtTime)
    {
        $entryNumber = 1;
        $offsetTimeMs = $this->srtTimeToMs($offsetSrtTime);

        $contents = file_get_contents($srtFile);
        foreach (explode("\n", $contents) as $line) {
            $matches = [];
            if (preg_match($this->indexRegex, $line)) {
                echo "$entryNumber\n\r";
                ++$entryNumber;
            } else if (preg_match($this->timeRegex, $line, $matches)) {
                $fromMs = $this->srtTimeToMs($matches['from']) - $offsetTimeMs;
                $toMs   = $this->srtTimeToMs($matches['to']) - $offsetTimeMs;
                echo $this->msToSrtFormat($fromMs).' --> '.$this->msToSrtFormat($toMs)."\n\r";
            } else {
                echo "$line\n\r";
            }
        }
    }

    public function srtTimeToMs($srtFormatString)
    {
        $matches = [];
        preg_match(
            '/(?<neg>-)?(?<h>\d\d):(?<min>\d\d):(?<s>\d\d),(?<ms>\d\d\d)/',
            $srtFormatString,
            $matches
        );
        $neg = ($matches['neg'] === '-') ? -1 : 1;
        return $neg * 
            ($matches['ms'] 
            + ($matches['s'] * S_TO_MS)
            + ($matches['min'] * MIN_TO_MS)
            + ($matches['h'] * H_TO_MS));
    }

    public function msToSrtFormat($ms)
    {
        assert(is_numeric($ms));

        $h   = floor($ms / H_TO_MS);
        $min = floor(($ms - ($h * H_TO_MS)) / MIN_TO_MS);
        $s   = floor(($ms - ($h * H_TO_MS) - ($min * MIN_TO_MS)) / S_TO_MS);
        $ms  = $ms - ($h * H_TO_MS) - ($min * MIN_TO_MS) - ($s * S_TO_MS);

        return sprintf('%02d:%02d:%02d,%03d', $h, $min, $s, $ms);
    }

    public function tests()
    {
        
    }
}
