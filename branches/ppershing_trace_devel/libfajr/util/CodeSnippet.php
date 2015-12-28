<?php
namespace fajr\libfajr\util;

class CodeSnippet
{
  /**
   * Return string containing lines near to $line in file $file.
   *
   * @param string $file file from get content
   * @param string $line line which we are interested in
   * @param int    $distance
   *
   * @return string lines in range <$line - $distance, $line+ $ distance>
   * (starting with line number, ending with newline)
   *
   * @TODO Possible recursive asserts?
   */
  public static function getCodeSnippet($file, $line, $distance)
  {
    assert(is_string($file)); //ehm, recursive asserts?
    assert(is_int($line));

    $code = "";

    $f = fopen($file, "r");
    if ($f) {
      $lineNum = 0;
      while (!feof($f)) {
        $buf = fgets($f, 4096);
        $lineNum++;

        if (abs($lineNum - $line) <= $distance) {
          $code .=sprintf("%3d", $lineNum).":  ".$buf;
        }
      }
      fclose($f);
    }

    return $code;
  }

}
