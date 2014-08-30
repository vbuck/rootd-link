<?php

/**
 * Rootd link grid block.
 *
 * @package   Rootd_Link
 * @author    Rick Buczynski <me@rickbuczynski.com>
 * @copyright 2014 Rick Buczynski. All Rights Reserved.
 */

class Rootd_Link_Block_Adminhtml_Link_Grid 
    extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Configure grid.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('linkGrid');
        $this->setDefaultSort('link_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare the collection.
     * 
     * @return Rootd_Link_Block_Adminhtml_Link_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('link/node')->getCollection();

        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * Prepare the grid columns.
     * 
     * @return Rootd_Link_Block_Adminhtml_Link_Grid
     */
    protected function _prepareColumns()
    {
        $helper = Mage::helper('link');

        $this->addColumn(
            'link_id', 
            array(
                'header'    => $helper->__('ID'),
                'align'     => 'left',
                'index'     => 'link_id',
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'            => $helper->__('Store'),
                'index'             => 'store_id',
                'type'              => 'store',
                'store_view'        => true,
                'display_deleted'   => true,
                'width'             => '300px',
            ));
        }

        return parent::_prepareColumns();
    }

    /**
     * Prepare mass actions.
     * 
     * @return Rootd_Link_Block_Adminhtml_Link_Grid
     */
    protected function _prepareMassaction() 
    {
        $helper = Mage::helper('link');

        if(!$this->_userMode){
            $this->setMassactionIdField('main_table.link_id');
            $this->getMassactionBlock()->setFormFieldName('link');

            $this->getMassactionBlock()->addItem(
                'delete', 
                array(
                    'label'    => $helper->__('Delete'),
                    'url'      => $this->getUrl('*/adminhtml_link/massDelete'),
                    'confirm'  => $helper->__('Are you sure you want to do this?')
                )
            );
        }

        return $this;
    }

    /**
     * Get the row click URL.
     * 
     * @param Mage_Core_Model_Abstract $row The link model.
     * 
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/link/edit', array('link_id' => $row->getId()));
    }
 
}
