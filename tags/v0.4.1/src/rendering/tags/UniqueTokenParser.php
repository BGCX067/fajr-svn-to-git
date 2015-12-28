<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Tento súbor obsahuje parser pre "unique" tag
 *
 * @package    Fajr
 * @subpackage Rendering__Tags
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\rendering\tags;

use Twig_TokenParser;
use Twig_Token;
use fajr\rendering\tags\UniqueNode;

/**
 * Token parser for "unique" tag
 * @package    Fajr
 * @subpackage Rendering__Tags
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class UniqueTokenParser extends Twig_TokenParser
{

  /**
   * Returns this tag's name, i.e. literal "unique"
   * @return string string of value "unique"
   */
  public function getTag()
  {
    return "unique";
  }

  /**
   * Parse a token stream for "unique" tag and return UniqueNode for it
   *
   * {% unique type name %}
   *
   * e.g.
   *
   * {% unique table b %}
   * {{ b }}
   *
   * @param Twig_Token $token
   * @returns UniqueNode parsed node
   */
  public function parse(Twig_Token $token)
  {
    $lineno = $token->getLine();
    $type = $this->parser->getStream()->expect(Twig_Token::NAME_TYPE)->getValue();
    $name = $this->parser->getStream()->expect(Twig_Token::NAME_TYPE)->getValue();

    $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);

    return new UniqueNode($name, $type, $lineno, $this->getTag());
  }
}