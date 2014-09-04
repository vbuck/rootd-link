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
                'align'     => 'right',
                'index'     => 'link_id',
                'width'     => '80px',
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

        $this->addColumn(
            'description', 
            array(
                'header'    => $helper->__('Description'),
                'align'     => 'left',
                'index'     => 'description',
                'width'     => '220px',
            )
        );

        $this->addColumn(
            'request_path', 
            array(
                'header'    => $helper->__('Request Path'),
                'align'     => 'left',
                'index'     => 'request_path',
            )
        );

        $this->addColumn(
            'is_active', 
            array(
                'header'    => $helper->__('Status'),
                'align'     => 'left',
                'index'     => 'is_active',
                'type'      => 'options',
                'width'     => '120px',
                'options'   => array(0 => $this->__('Disabled'), 1 => $this->__('Enabled')),
                'frame_callback' 
                            => array($this, 'decorateStatus'),
            )
        );

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
     * Decorate status column values.
     *
     * @return string
     */
    public function decorateStatus($value, $row, $column, $isExport)
    {
        if ($row->getStatus()) {
            $cell = '
                <span class="grid-severity-critical">
                    <span>' . $value . '</span>
                </span>
            ';
        } else {
            $cell = '
                <span class="grid-severity-notice">
                    <span>' . $value . '</span>
                </span>
            ';
        }

        return $cell;
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
