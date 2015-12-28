<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains dependency injector.
 *
 * @package    Fajr
 * @subpackage Injection
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\injection;

use sfServiceContainerBuilder;
use fajr\libfajr\base\Preconditions;

/**
 * Dependency injector.
 *
 * Currently, we do not provide own implementation, rather we just
 * wrap existing Symfony framework. Wrapping is neccessary to be
 * independent from underlying library.
 * Example usage:
 * <code>
 * $injector = new Injector($modules_which_configures_this_injector);
 * $myObject = $injector->getInstance('MyObject.class');
 * </code>
 *
 * @package    Fajr
 * @subpackage Injection
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class Injector
{
  /** @var sfServiceContainerBuilder Underlying Symfony container builder */
  private $container;

  /**
   * Construct injector configured with passed modules.
   *
   * @param array(Module) $modules Modules which specifies behaviour of this
   *    injector. Note that order of Modules is important, as they are
   *    configuring the injector in given order.
   */
  public function __construct(array $modules)
  {
    $this->container = new sfServiceContainerBuilder();
    foreach ($modules as $module) {
      $module->configure($this->container);
    }
  }

  /**
   * Return instance of object given its name.
   *
   * Note that the scope of the object (singleron/per call) depends on
   * how the object is configured by injector modules.
   *
   * @returns mixed instance of whatever is asked for
   */
  public function getInstance($name)
  {
    Preconditions::checkIsString($name);
    return $this->container->getService($name);
  }

  /**
   * Return configured parameter value.
   * @deprecated If possible, use injector to inject
   * instances of initialized objects.
   *
   * @param string $name name of the parameter to return
   *
   * @returns mixed configured value
   */
  public function getParameter($name)
  {
    Preconditions::checkIsString($name);
    assert($this->container->hasParameter($name));
    return $this->container->getParameter($name);
  }
}
