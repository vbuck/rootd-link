<?php

/**
 * Link collection model.
 *
 * @package   Rootd_Link
 * @author    Rick Buczynski <me@rickbuczynski.com>
 * @copyright 2014 Rick Buczynski. All Rights Reserved.
 */

class Rootd_Link_Model_Resource_Node_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('link/node');
    }

}