<?php

/**
 * Base module helper class.
 *
 * @package   Rootd_Link
 * @author    Rick Buczynski <me@rickbuczynski.com>
 * @copyright 2014 Rick Buczynski. All Rights Reserved.
 */

class Rootd_Link_Helper_Data 
    extends Mage_Core_Helper_Abstract 
{

    const XML_ATTACHMENT_PATH   = 'default/rootd_link/attachment_base';
    const XML_FRONTNAME_PATH    = 'frontend/routers/rootd_link/args/frontName';
    const XML_MODULE_PATH       = 'frontend/routers/rootd_link/args/module';

    /* @var $_frontName string */
    protected $_frontName;
    /* @var $_moduleName string */
    protected $_moduleName;

    /**
     * Generate an expected attachment path from the given filename.
     * 
     * @return string
     */
    public function generateAttachmentPath($file, $absolute = true)
    {
        $time   = Mage::getModel('core/date')->timestamp(time());
        $year   = date('Y', $time);
        $month  = date('m', $time);
        $base   = $absolute ? $this->getAttachmentBaseDir() : $this->getAttachmentBase();

        return "{$base}{$year}/{$month}/{$file}";
    }

    /**
     * Get the attachment base directory.
     * 
     * @return string
     */
    public function getAttachmentBase()
    {
        return (string) Mage::getConfig()->getNode(self::XML_ATTACHMENT_PATH);
    }

    /**
     * Get the attachment base absolute path.
     * 
     * @return string
     */
    public function getAttachmentBaseDir()
    {
        return Mage::getBaseDir() . DS . $this->getAttachmentBase();
    }

    /**
     * Get the attachment base URL.
     * 
     * @return string
     */
    public function getAttachmentBaseUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . $this->getAttachmentBase();
    }

    /**
     * Get the module routing front name from config.
     * 
     * @return string
     */
    public function getFrontName()
    {
        if (!$this->_frontName) {
            $this->_frontName = (string) Mage::getConfig()->getNode(self::XML_FRONTNAME_PATH);
        }

        return $this->_frontName;
    }

    /**
     * Generate a real link node URL.
     * 
     * @param integer $id The node ID.
     * 
     * @return string
     */
    public function getNodeUrl($id)
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) .
            "{$this->getFrontName()}/node/{$id}";
    }

    /**
     * Get the module name from config.
     * 
     * @return string
     */
    public function getModuleName()
    {
        if (!$this->_moduleName) {
            $this->_moduleName = (string) Mage::getConfig()->getNode(self::XML_MODULE_PATH);
        }

        return $this->_moduleName;
    }

    /**
     * Get a formatted, localized date.
     * 
     * @param string $date   The input date.
     * @param string $format The output format.
     * 
     * @return string
     */
    public function getStoreDate($date, $format = Varien_Date::DATE_PHP_FORMAT)
    {
        return Mage::getSingleton('core/date')->date($format, $date);
    }
    
}