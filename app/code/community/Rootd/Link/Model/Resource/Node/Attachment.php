<?php

/**
 * Link node attachment resource model.
 *
 * @package   Rootd_Link
 * @author    Rick Buczynski <me@rickbuczynski.com>
 * @copyright 2014 Rick Buczynski. All Rights Reserved.
 */

class Rootd_Link_Model_Resource_Node_Attachment
    extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Initialize table.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('link/node_attachment', 'attachment_id');
    }

    /**
     * Remove the physical attachment from disk.
     * 
     * @param Mage_Core_Model_Abstract $model The attachment model.
     * 
     * @return Rootd_Link_Model_Resource_Node_Attachment
     */
    protected function _beforeDelete(Mage_Core_Model_Abstract $model)
    {
        if ($model->getTypeId() == Rootd_Link_Model_Node_Attachment::TYPE_FILE) {
            $path = Mage::helper('link')->generateAttachmentPath(basename($model->getTargetPath()), true);

            if (file_exists($path)) {
                unlink($path);
            }
            
            // Detach from link
            $model->getLink()
                ->setObjectId(0)
                ->save();
        }

        return $this;
    }
    
}