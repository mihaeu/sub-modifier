<?php

use Mihaeu\SubModifier\SubModifier;

class SubModifierTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SubModifier
     */
    private $subModifier;

    public function setUp()
    {
        $this->subModifier = new SubModifier();
    }

    public function testConvertsSrtTimeToMsAndBack()
    {
        $srtTimeBefore = '01:01:01,001';
        $ms = $this->subModifier->srtTimeToMs($srtTimeBefore);
        $srtTimeAfter = $this->subModifier->msToSrtFormat($ms);
        $this->assertEquals($srtTimeBefore, $srtTimeAfter);

        $srtTimeBefore = '00:00:00,111';
        $ms = $this->subModifier->srtTimeToMs($srtTimeBefore);
        $srtTimeAfter = $this->subModifier->msToSrtFormat($ms);
        $this->assertEquals($srtTimeBefore, $srtTimeAfter);

        $srtTimeBefore = '01:12:33,001';
        $ms = $this->subModifier->srtTimeToMs($srtTimeBefore);
        $srtTimeAfter = $this->subModifier->msToSrtFormat($ms);
    }

    public function testSubtractsSubtitles()
    {
        $srtTimeBefore = '01:12:33,987';
        $srtTimeAfter = '00:10:30,900';
        $msBefore = $this->subModifier->srtTimeToMs($srtTimeBefore);
        $msAfter = $this->subModifier->srtTimeToMs($srtTimeAfter);
        $diff = $this->subModifier->msToSrtFormat($msBefore - $msAfter);
        $this->assertEquals($diff, '01:02:03,087');
    }

    public function testAddsSubtitles()
    {
        $srtTimeBefore = '01:12:33,987';
        $srtTimeAfter = '00:10:30,900';
        $msBefore = $this->subModifier->srtTimeToMs($srtTimeBefore);
        $msAfter = $this->subModifier->srtTimeToMs($srtTimeAfter);
        $diff = $this->subModifier->msToSrtFormat($msBefore - $msAfter);
        $this->assertEquals($diff, '01:02:03,087');
    }
}