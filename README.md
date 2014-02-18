# Sub Modifier

SubModifier modifies subtitle files by increasing or decreasing the subtitle files and by re-indexing the subtitles (in case a subtitle file has to be split up for multi-part movies).
 
*Currently only supports `.srt` files.*

## Installation

If you want to use this as a package for a separate project (assuming composer was installed globally):

```bash
composer require mihaeu/sub-modifier:1.*
```

In most cases however this is more useful installed globally along with other tools like phpunit, phing, etc. as a global script:

```bash
composer install --global mihaeu/sub-modifier:1.*
```

Make sure the global composer path (usually `~/.composer/bin`) is in your `$PATH`.

```bash
echo $PATH
```

## Usage

```bash
submod <SRT_FILE> <[-]SRT_OFFSET_TIME]>
```

Assuming I'm in a movie directory with a subtitle that I want to delay by 10 seconds, I would do the following (overwriting the old file):

```bash
submod Amour-2012.srt 00:10:00,000 > Amour-2012.srt
```

## Tests

Run the PHPUnit tests from the project root like so:

```bash
vendor/bin/phpunit --testdox --coverage-text
```

To get readable output like this (requires for you to have code coverage in your
php setup, otherwise, remove that option):

```bash
...

SubModifier
 [x] Converts ms to srt time format
 [x] Converts srt time format to ms
 [x] Validates srt time formats
 [x] Converts srt time to ms and back
 [x] Subtracts subtitles
 [x] Adds subtitles
 [x] Doesnt accept bad srt file
 [x] Doesnt accept bad srt format
 [x] Normalizes linefeeds of input srt file
 [x] Delays subtitles by given time


Generating code coverage report in HTML format ... done


Code Coverage Report 
  2014-02-18 09:37:27

 Summary: 
  Classes: 100.00% (1/1)
  Methods: 100.00% (5/5)
  Lines:   100.00% (45/45)

\Mihaeu\SubModifier::SubModifier
  Methods: 100.00% ( 5/ 5)   Lines: 100.00% ( 42/ 42)

```

## License

The MIT License (MIT)

Copyright (c) 2014 Michael Haeuslmann

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

## Epilogue

Goodbye and have a nice day.