<?php
/**
 * Injector module for Trace.class
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\modules;

use fajr\config\FajrConfig;
use fajr\config\FajrConfigOptions;
use fajr\injection\Module;
use sfServiceContainerBuilder;
use sfServiceReference;

/**
 * Injector module for Trace.class, injects NullTrace
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class TraceModule implements Module
{
  private $config;

  public function __construct(FajrConfig $config) {
    $this->config = $config;
  }

  /**
   * Configures Trace.class for injection.
   *
   * @param sfServiceContainer $container Container to configure
   */
  public function configure(sfServiceContainerBuilder $container)
  {
    if ($this->config->get(FajrConfigOptions::DEBUG_TRACE) === true) {
      $debugFile = $this->config->getDirectory(FajrConfigOptions::DEBUG_TRACE_FILE);
      if ($debugFile !== null) {
        $container->setParameter('Debug.Trace.File', $debugFile);
        $container->setParameter('Debug.Trace.File.Mode', 'a');
        $container->register('Debug.Trace.File.class', 'fajr\util\PHPFile')
                ->addArgument('%Debug.Trace.File%')
                ->addArgument('%Debug.Trace.File.Mode%');
        $container->register('Trace.class', 'fajr\FileTrace')
                ->addArgument(new sfServiceReference('Timer.class'))
                ->addArgument(new sfServiceReference('Debug.Trace.File.class'))
                ->addArgument(0)
                ->addArgument('--Trace--');
      }
      else {
        $container->register('Trace.class', 'fajr\ArrayTrace')
                  ->addArgument(new sfServiceReference('Timer.class'))
                  ->addArgument('--Trace--');
      }
    }
    else {
      $container->register('Trace.class', 'fajr\libfajr\pub\base\NullTrace');
    }
  }
}
