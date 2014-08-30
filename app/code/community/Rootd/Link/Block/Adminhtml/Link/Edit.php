<?php

/**
 * Rootd link form container block.
 *
 * @package   Rootd_Link
 * @author    Rick Buczynski <me@rickbuczynski.com>
 * @copyright 2014 Rick Buczynski. All Rights Reserved.
 */

class Rootd_Link_Block_Adminhtml_Link_Edit 
    extends Mage_Adminhtml_Block_Widget_Form_Container
{

    /**
     * Prepare the container.
     *
     * @return void
     */
    public function __construct()
    {
        $helper             = Mage::helper('link');
        $this->_controller  = 'adminhtml_link';
        $this->_blockGroup  = 'link';
        $this->_objectId    = 'link_id';

        parent::__construct();

        $this->_updateButton('save', 'label', $helper->__('Save Link'));
        $this->_updateButton('delete', 'label', $helper->__('Delete Link'));

        $this->_addButton(
            'saveandcontinue', 
            array(
                'label'     => $helper->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit()',
                'class'     => 'save',
            ), 
            -100
        );

        $this->_formScripts[] = "
            function saveAndContinueEdit() {
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $helper = Mage::helper('link');
        $model  = Mage::registry('link_model');

        if ($model && $model->getId()) {
            return $helper->__('Edit Link');
        } else {
            return $helper->__('New Block');
        }
    }

}
