<?php

/**
 * Link node resource model.
 *
 * @package   Rootd_Link
 * @author    Rick Buczynski <me@rickbuczynski.com>
 * @copyright 2014 Rick Buczynski. All Rights Reserved.
 */

class Rootd_Link_Model_Resource_Node
    extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Initialize table.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('link/node', 'link_id');
    }

    /**
     * Retrieve select object for load object data.
     *
     * @param string                $field  The field.
     * @param mixed                 $value  The value.
     * @param Rootd_Link_Model_Node $object The link model.
     * 
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ( !is_null($object->getStoreId()) ) {
            $select->where('store_id IN(?)', array(Mage_Core_Model_App::ADMIN_STORE_ID, $object->getStoreId()))
                ->order('store_id ' . Varien_Db_Select::SQL_DESC)
                ->limit(1);
        }

        return $select;
    }

    /**
     * Load rewrite information for request.
     *
     * @param Rootd_Link_Model_Node $object The link object.
     * @param array|string          $path   The path(s) to load.
     * 
     * @return Rootd_Link_Model_Resource_Node
     */
    public function loadByRequestPath(Rootd_Link_Model_Node $object, $path)
    {
        $today = date(
            Varien_Db_Adapter_Pdo_Mysql::DATE_FORMAT, 
            Mage::getSingleton('core/date')->timestamp(time())
        );

        if (!is_array($path)) {
            $path = array($path);
        }

        $pathBind = array();

        foreach ($path as $key => $url) {
            $pathBind['path' . $key] = 
                $path[$key] = ltrim($url, (Mage::helper('link')->getFrontName() . '/'));
        }

        // Determine column by which to load
        $parts = explode('/', $path[0]);
        if ($parts[0] == 'node') {
            $loadByName = 'link_id';
            $loadBy     = "`main_table`.`{$loadByName}`";
            $pathBind   = array('id0' => $parts[1]);
        } else {
            $loadByName = 'request_path';
            $loadBy     = "`main_table`.`{$loadByName}`";
        }

        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from(
                array('main_table' => $this->getMainTable()), 
                array('main_table.*', 'options' => new Zend_Db_Expr("'RP'")) // Force permanent redirect option
            )
            ->where($loadBy. ' IN (:' . implode(', :', array_flip($pathBind)) . ')')
            ->where('`main_table`.`store_id` IN(?)', array(Mage_Core_Model_App::ADMIN_STORE_ID, (int)$object->getStoreId()))
            ->where(
                new Zend_Db_Expr('
                    (`main_table`.`is_active` = 1 AND `main_table`.`active_from` IS NULL AND `main_table`.`active_to` IS NULL) OR 
                    (`main_table`.`is_active` = 1 AND `main_table`.`active_from` <= ? AND `main_table`.`active_to` >= ?)'), 
                array($today)
            )
            ->joinLeft(
                array('a' => Mage::getResourceSingleton('link/node_attachment')->getMainTable()),
                "`main_table`.`object_id` = `a`.`attachment_id`",
                array()
            );

        //Zend_Debug::dump($select->assemble());exit;

        $items = $adapter->fetchAll($select, $pathBind);
        
        //Zend_Debug::dump($items);exit;

        // Determine path to use by lowest penalty
        if ($loadByName == 'link_id') {
            $foundItem = array_shift($items);
        } else {
            $mapPenalty     = array_flip(array_values($path)); // Mapping array: lower index = better
            $currentPenalty = null;
            $foundItem      = null;

            foreach ($items as $item) {
                if (!array_key_exists($item['request_path'], $mapPenalty)) {
                    continue;
                }

                $penalty = $mapPenalty[$item['request_path']] << 1 + ($item['store_id'] ? 0 : 1);

                if (!$foundItem || $currentPenalty > $penalty) {
                    $foundItem      = $item;
                    $currentPenalty = $penalty;

                    if (!$currentPenalty) {
                        break; // Found best matching item with zero penalty, no reason to continue
                    }
                }
            }
        }

        //Zend_Debug::dump($foundItem);exit;

        // Set data and finish loading
        if ($foundItem) {
            $object->setData($foundItem);
        }

        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
    }

    /**
     * Manage the attachment on the link.
     * 
     * @param Rootd_Link_Model_Node $object The link object.
     *
     * @return Rootd_Link_Model_Resource_Node
     */
    public function setAttachment($model)
    {
        // Remove existing attachment
        if ($model->getObjectId()) {
            $model->getAttachment()->delete();
        }

        if ($model->getTargetFile()) {
            $attachment = Mage::getModel('link/node_attachment');

            $attachment->setLinkId($model->getId())
                // Currently only supports files
                ->setTypeId(Rootd_Link_Model_Node_Attachment::TYPE_FILE)
                ->setTargetPath($model->getTargetFile());

            $attachment->save();

            $model->setObjectId($attachment->getId())
                ->setAttachment($attachment)
                ->setTargetPath('');
        }

        return $this;
    }

}