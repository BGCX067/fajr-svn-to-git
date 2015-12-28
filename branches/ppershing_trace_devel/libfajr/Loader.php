<?php
/* {{{
Copyright (c) 2010 Peter Perešíni

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
 * start of each script. Loads used classes
 * and sets default options (assert,error reporting)
 *
 * PHP version 5.2.6
 *
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Peter Peresini <ppershing+fajr@gmail.com>
 * @filesource
 */

/**
 * static class providing loading capatibility to classes
 *
 * Class will automatically register self as 'autoload' function,
 * so when PHP tries to use undefined class, it will be first tried
 * to be autoloaded from files. 
 *
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Peter Peresini <ppershing+fajr@gmail.com>
 * @see        http://sk.php.net/autoload
 */
class Loader
{
  /**
   * @var array((string)$className=>(string)$classPath) 
   * array holding information
   * about found classes
   */
  private static $_classPaths = array();

  /**
   * add information about class
   *
   * @param string $className Name of the class
   * @param string $classPath Path of the class
   *
   * @return void
   * @throws Exception if trying to add duplicate class
   * (this means that it found two files with the same name
   * and it is impossible to determine which is right)
   */
  public static function addClass($className,$classPath)
  {
    assert(is_string($className));
    assert(is_string($classPath));
    if (isset(self::$_classPaths[$className])) {
      throw new Exception(
                "Loader:: class $className already registered!");
    }
    self::$_classPaths[$className] = $classPath;
  }

  /**
   * return path where class is stored
   *
   * @param string $className name of class which we lookup
   *
   * @return string|false In case class is 'cached' and we know it's
   * path, function returns path, false otherwise
   */
  public static function getClassPath($className)
  {
    assert(is_string($className));
    // TODO(ppershing): make Loader namespace-aware
    // in case of name collisions.
    // get rid of namespace
    $className = preg_replace("@.*\\\\@", "", $className); 
    if (isset(self::$_classPaths[$className])) {
      return self::$_classPaths[$className];
    }
    return false;
  }

  /**
   * search for classes in $path
   *
   * Note that this function assumes, that classes are per-file and
   * name of file is exactly name of class.
   *
   * @param string $path      path to directory where files are located
   * @param bool   $recursive true if we want recursive traversal
   * 
   * @return void
   */
  public static function searchForClasses($path,$recursive)
  {
    assert(is_string($path));
    assert(is_bool($recursive));

    if (!is_dir($path)) {
      throw new Exception("Loader::searchForClasses "
          ."path $path is not a directory!");
    }

    @$d = dir($path);

    if ($d === false) {
      // TODO provide a way to explicitly exclude subtrees from being searched
      error_log('Cannot open '.$path.' while searching for classes');
      return;
    }

    while (false !== ($entry = $d->read())) {
      if ($entry == "." || $entry == "..") {
        continue; // skip backlinks
      }

      $name = "$path/$entry";
      if (is_dir($name) && $recursive) {
        self::searchForClasses($name, $recursive);
      }

      if (is_file($name)) {

        $info = pathinfo($name);
        if (isset($info['extension']) && 
            $info['extension']=='php') {
          self::addClass($info['filename'], $name);
        }
      }
    }
  }

  /**
   * try to automatically load a class
   *
   * Note that if class is not found, function writes error message
   * to stdout and returns (and possibly ends script raising
   * exception because class is still not loaded)
   *
   * @param string $className name of class that needs to be loaded
   *
   * @return void
   */
  public static function autoload($className)
  {
    assert(is_string($className));
    $path = self::getClassPath($className);
    if ($path === false) {
      echo "Autoload of class $className failed";
      return;
    }
    include_once $path;
  }

  /**
   * register as autoloader function
   *
   * @return void
   */
  public static function register()
  {
    spl_autoload_register(array('Loader','autoload'));
  }
}
