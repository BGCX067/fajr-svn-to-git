<?php
/* {{{
Copyright (c) 2010 Martin Králik

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.
 }}} */

	/**
	 * Function that searchs haystack for perl-like pattern and
	 * returns first sub-match from pattern.
	 * I.e. If the pattern is "example:(.*)",
	 * the full match is example:something and this
	 * function returns "something"
	 */
	function match($haystack, $pattern)
	{
		$matches = array();
		if (!preg_match($pattern, $haystack, $matches)) return false;
		assert(isset($matches[1]));
		return $matches[1];
	}
	
	function matchAll($haystack, $pattern, $singleMatch = false)
	{
		$matches = array();
		if (!preg_match_all($pattern, $haystack, $matches, PREG_SET_ORDER)) return false;
		else
		{
			if ($singleMatch == false) return $matches;
			else return $matches[0];
		}
	}

  /**
   * Function which removes all integer-indexed entries from array.
   * Useful for removing unnamed matches after matchAll with named patterns.
   * @param array() $data Array containing string&integer indexed values 
   * @return array() Data wihout any integer-indexed values
   */
  function removeIntegerIndexesFromArray($data) {
    foreach (array_keys($data) as $key) {
      if (is_numeric($key)) unset($data[$key]);
    }
    return $data;
  }
	
	function dump($s)
	{
		if (is_array($s)) foreach ($s as $value) dump($value);
		else echo '<pre>'.hescape($s).'</pre><hr/>';
	}
	
	function hescape($string)
	{
		return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
	}

	function random()
	{
		return rand(100000,999999);
	}
	
?>
