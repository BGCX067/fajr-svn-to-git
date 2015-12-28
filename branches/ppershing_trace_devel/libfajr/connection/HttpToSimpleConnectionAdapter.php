<?php
/* {{{
Copyright (c) 2010 Peter Peresini

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
 * Provides encapsulated conversion from complex HttpConnection
 * to simple SimpleConnection.
 *
 * PHP version 5.3.0
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Peter Peresini <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\connection;

use fajr\libfajr\base\Trace;

/**
 * Adapter that provides SimpleConnection interface
 * encapsulating HttpConnection.
 *
 * @package    Fajr
 * @subpackage Libfajr__Connection
 * @author     Peter Peresini <ppershing+fajr@gmail.com>
 */
class HttpToSimpleConnectionAdapter implements SimpleConnection {
  private $connection;

  public function __construct(HttpConnection $connection) {
    $this->connection = $connection;
  }

  public function request(Trace $trace, $url, $post_data = null) {
    //TODO: assert !=null && !is_array()
    if (is_array($post_data)) {
      return $this->connection->post($trace, $url, $post_data);
    }
    else {
      return $this->connection->get($trace, $url);
    }
  }
}
