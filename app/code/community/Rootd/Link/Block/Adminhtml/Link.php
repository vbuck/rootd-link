<?php

/**
 * Rootd link grid container block.
 *
 * @package   Rootd_Link
 * @author    Rick Buczynski <me@rickbuczynski.com>
 * @copyright 2014 Rick Buczynski. All Rights Reserved.
 */

class Rootd_Link_Block_Adminhtml_Link 
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    /**
     * Initialize grid.
     *
     * @return void
     */
   public function __construct()
   {
        $this->_controller = 'adminhtml_link';
        $this->_blockGroup = 'link';
        $this->_headerText = Mage::helper('link')->__('Rootd Links');

        parent::__construct();
   }
 
}
