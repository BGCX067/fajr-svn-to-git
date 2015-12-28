<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Tento súbor obsahuje triedu reprezentujúcu AST node pre "url" tag
 *
 * @package    Fajr
 * @subpackage Rendering__Tags
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\rendering\tags;

use Twig_Node;
use Twig_Compiler;
use Twig_Token;

/**
 * AST node for "url" tag
 * @package    Fajr
 * @subpackage Rendering__Tags
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class URLNode extends Twig_Node
{

  /**
   * Construct a new "url" node
   * @param string $name name of the variable to set
   * @param string $parameters node returning array to use as link parameters
   * @param int $lineno
   * @param string $tag
   */
  public function __construct($name, $parameters, $lineno, $tag=null)
  {
    parent::__construct(array('parameters'=>$parameters), array('name'=>$name), $lineno, $tag);
  }

  /**
   * Compile this node
   * @param Twig_Compiler $compiler
   */
  public function compile($compiler)
  {
    $compiler
      ->addDebugInfo($this)
      ->write('$context[\''.$this['name'].'\'] = ')
      ->raw('\\fajr\\FajrUtils::buildUrl(')
      ->subcompile($this->parameters)
      ->raw(");\n");
  }


}