<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Tento súbor obsahuje parser pre "url" tag
 *
 * @package    Fajr
 * @subpackage Rendering__Tags
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\rendering\tags;

use Twig_TokenParser;
use Twig_Token;
use fajr\rendering\tags\URLNode;

/**
 * Token parser for "url" tag
 * @package    Fajr
 * @subpackage Rendering__Tags
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class URLTokenParser extends Twig_TokenParser
{

  /**
   * Returns this tag's name, i.e. literal "unique"
   * @return string string of value "unique"
   */
  public function getTag()
  {
    return "url";
  }

  /**
   * Parse a token stream for "url" tag and return URLNode for it
   *
   * {% url name for parameters %}
   *
   * e.g.
   *
   * {% url link for ["param": "value"] b %}
   * {{ b }}
   *
   * @param Twig_Token $token
   * @returns URLNode parsed node
   */
  public function parse(Twig_Token $token)
  {
    $lineno = $token->getLine();
    $name = $this->parser->getStream()->expect(Twig_Token::NAME_TYPE)->getValue();
    $this->parser->getStream()->expect('for');

    $parameters = $this->parser->getExpressionParser()->parseExpression();

    $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);

    return new URLNode($name, $parameters, $lineno, $this->getTag());
  }
}