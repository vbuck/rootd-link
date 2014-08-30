<?php

/**
 * Link gateway block.
 *
 * @package   Rootd_Link
 * @author    Rick Buczynski <me@rickbuczynski.com>
 * @copyright 2014 Rick Buczynski. All Rights Reserved.
 */

class Rootd_Link_Block_Gateway 
    extends Mage_Core_Block_Template
{

    /**
     * Set the template.
     * 
     * @return void
     */
    protected function _construct()
    {
        $this->setTemplate('link/gateway.phtml');
    }

    /**
     * Get the form action URL.
     * 
     * @return string
     */
    public function getFormAction()
    {
        return Mage::helper('link')->getNodeUrl(Mage::registry('protected_link'));
    }

    /**
     * Get a module translation string.
     * 
     * @param string $key The translation key.
     * 
     * @return string|null
     */
    public function getTranslation($key)
    {
        return Mage::getStoreConfig("rootd_link/translations/{$key}");
    }

}