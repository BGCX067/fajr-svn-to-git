<?php

/**
 * @copyright  Copyright (c) 2010, 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 *
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Martin Králik <majak47@gmail.com>
 * @author     Jakub Marek <jakub.marek@gmail.com>
 * @filesource
 */

namespace fajr\libfajr\data_manipulation;

use fajr\libfajr\pub\data_manipulation\InformacnyListData;
use fajr\libfajr\pub\exceptions\NotImplementedException;

/**
 * Class representing infromation sheet for course.
 *
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Martin Králik <majak47@gmail.com>
 * @author     Jakub Marek <jakub.marek@gmail.com>
 */
class InformacnyListDataImpl implements InformacnyListData {

    private $list;

    public function __construct($list = null) {
        $this->list = $list;
    }


    /**
     * {@inheritdoc}
     */
    public function getAttribute($attribute) {
        if (!array_key_exists($attribute, $this->list)) {
            return false;
        } else {
            return $this->list[$attribute];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttribute($attribute) {
        return array_key_exists($attribute, $this->list);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllAttributes() {
        return $this->list;
    }

    /**
     * {@inheritdoc}
     */
    public function getListOfAttributes() {
        return array_keys($this->list);
    }

}
