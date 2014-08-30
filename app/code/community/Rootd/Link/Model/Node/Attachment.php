<?php

/**
 * Link node attachment model.
 *
 * @todo      Consider additional attachment types.
 *
 * @package   Rootd_Link
 * @author    Rick Buczynski <me@rickbuczynski.com>
 * @copyright 2014 Rick Buczynski. All Rights Reserved.
 */

class Rootd_Link_Model_Node_Attachment
    extends Mage_Core_Model_Abstract
{

    /**
     * Type constants represent the model type ID.
     * These values can determine any unique
     * functionality for the attachment when viewed.
     */
    const TYPE_FILE = 1;

    /* @var $_link Rootd_Link_Model_Node */
    protected $_link;

    /**
     * Initialize the model.
     * 
     * @return void
     */
    protected function _construct()
    {
        $this->_init('link/node_attachment');
    }

    /**
     * Get the parent link model.
     * 
     * @return Rootd_Link_Model_Node
     */
    public function getLink()
    {
        if (!$this->_link) {
            $link = Mage::getModel('link/node');

            if ($this->getLinkId()) {
                $link->load($this->getLinkId());
            }

            $this->_link = $link;
        }

        return $this->_link;
    }

    /**
     * Get the absolute URL to the attachment.
     * 
     * @return string
     */
    public function getTargetUrl()
    {
        $baseUrl    = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $baseParts  = parse_url($baseUrl);

        if (isset($baseParts['path'])) {
            return $baseUrl . str_replace($baseParts['path'], '', $this->getTargetPath());
        }

        return $baseUrl . $this->getTargetPath();
    }

}