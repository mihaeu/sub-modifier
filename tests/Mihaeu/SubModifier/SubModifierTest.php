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

    public function testConvertsMsToSrtTimeFormat()
    {
        $this->assertEquals('00:00:01,001', $this->subModifier->msToSrtFormat('1001'));
    }

    public function testConvertsSrtTimeFormatToMs()
    {
        $this->assertEquals(1001, $this->subModifier->srtTimeToMs('00:00:01,001'));
        
        $h   = 1 * 60 * 60 * 1000;
        $min = 2 * 60 * 1000;
        $s   = 3 * 1000;
        $ms  = 4;
        $this->assertEquals($h + $min + $s + $ms, $this->subModifier->srtTimeToMs('01:02:03,004'));
    }

    public function testValidatesSrtTimeFormats()
    {
        $this->assertTrue($this->subModifier->isSrtTime('01:02:03,984'));

        // legal characters but semantically wrong
        $this->assertFalse($this->subModifier->isSrtTime('01:99:03,004'));
        $this->assertFalse($this->subModifier->isSrtTime('01:03:88,004'));

        $this->assertFalse($this->subModifier->isSrtTime('1:2:3,04'));
        $this->assertFalse($this->subModifier->isSrtTime('01:02:03.004'));
        $this->assertFalse($this->subModifier->isSrtTime('01.02.03'));
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

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage file
     */
    public function testDoesntAcceptBadSrtFile()
    {
        $legalSrtOffset = '00:00:00,000';
        $badSrtFile = '/tmp/subtitleFileWhichDoesntExist';
        $this->subModifier->modifySrtFile($badSrtFile, $legalSrtOffset);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage time format
     */
    public function testDoesntAcceptBadSrtFormat()
    {
        $goodSrtFile = $temp_file = tempnam(sys_get_temp_dir(), 'Tux');
        $badSrtOffset = '00-00-00-000';
        $this->subModifier->modifySrtFile($goodSrtFile, $badSrtOffset);
    }

    public function testNormalizesLinefeedsOfInputSrtFile()
    {
        foreach (["\r\n", "\r", "\n"] as $lf) {
            $goodSrtSubtitle = 
                "1$lf".
                "00:00:01,000 --> 00:00:04,074$lf".
                "Subtitles downloaded from www.OpenSubtitles.org$lf".
                "$lf".
                "2$lf".
                "00:00:38,437 --> 00:00:40,639$lf".
                "All started in my old forge$lf".
                "in Ventura, California";
            $expectedNormalizedSrtSubtitle =
                "1".PHP_EOL.
                "00:00:01,000 --> 00:00:04,074".PHP_EOL.
                "Subtitles downloaded from www.OpenSubtitles.org".PHP_EOL.
                "".PHP_EOL.
                "2".PHP_EOL.
                "00:00:38,437 --> 00:00:40,639".PHP_EOL.
                "All started in my old forge".PHP_EOL.
                "in Ventura, California";
            $actualNormalizedSrtSubtitle =
                $this->subModifier->normalizeLinefeeds($goodSrtSubtitle);
            $this->assertEquals($expectedNormalizedSrtSubtitle, $actualNormalizedSrtSubtitle);
        }
    }

    public function testDelaysSubtitlesByGivenTime()
    {
        $srtSubtitleBefore =
            "1".PHP_EOL.
            "00:00:01,000 --> 00:00:04,074".PHP_EOL.
            "Subtitles downloaded from www.OpenSubtitles.org".PHP_EOL.
            "".PHP_EOL.
            "2".PHP_EOL.
            "00:00:38,437 --> 00:00:40,639".PHP_EOL.
            "All started in my old forge".PHP_EOL.
            "in Ventura, California";
        $expectedSrtSubtitleAfterOneMinuteDelay =
            "1".PHP_EOL.
            "00:01:01,000 --> 00:01:04,074".PHP_EOL.
            "Subtitles downloaded from www.OpenSubtitles.org".PHP_EOL.
            "".PHP_EOL.
            "2".PHP_EOL.
            "00:01:38,437 --> 00:01:40,639".PHP_EOL.
            "All started in my old forge".PHP_EOL.
            "in Ventura, California";
        $goodSrtFile = tempnam(sys_get_temp_dir(), 'Tux');
        file_put_contents($goodSrtFile, $srtSubtitleBefore);

        $actualSrtSubtitleAfterOneMinuteDelay = 
            $this->subModifier->modifySrtFile($goodSrtFile, '-00:01:00,000');;

        unlink($goodSrtFile);
        $this->assertEquals(
            $expectedSrtSubtitleAfterOneMinuteDelay,
            $actualSrtSubtitleAfterOneMinuteDelay
        );
    }
}