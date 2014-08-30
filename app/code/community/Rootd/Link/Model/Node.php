<?php

/**
 * Link node model.
 *
 * @package   Rootd_Link
 * @author    Rick Buczynski <me@rickbuczynski.com>
 * @copyright 2014 Rick Buczynski. All Rights Reserved.
 */

class Rootd_Link_Model_Node
    extends Mage_Core_Model_Abstract
{

    const EVENT_VIEW = 'rootd_link_node_view';

    /* @var $_attachment Rootd_Link_Model_Node_Attachment */
    protected $_attachment;

    /**
     * Initialize the model.
     * 
     * @return void
     */
    protected function _construct()
    {
        $this->_init('link/node');
    }

    /**
     * Save the attachment if requested.
     * 
     * @return Rootd_Link_Model_Node
     */
    protected function _beforeSave()
    {
        if ($this->getSaveAttachmentFlag()) {
            $this->_getResource()->setAttachment($this);
        }

        return $this;
    }

    /**
     * Check if a path is an absolute, external URL.
     * 
     * @param string $path The input path.
     * 
     * @return boolean
     */
    protected function _getIsExternal($path = '')
    {
        $part = substr($path, 0, 6);

        return $part === 'http:/' || $part === 'https:';
    }

    /**
     * Get the query string from request.
     * 
     * @return string|boolean
     */
    protected function _getQueryString()
    {
        if (!empty($_SERVER['QUERY_STRING'])) {
            $queryParams = array();

            parse_str($_SERVER['QUERY_STRING'], $queryParams);

            $hasChanges = false;

            foreach ($queryParams as $key => $value) {
                if (substr($key, 0, 3) === '___') {
                    unset($queryParams[$key]);

                    $hasChanges = true;
                }
            }

            if ($hasChanges) {
                return http_build_query($queryParams);
            } else {
                return $_SERVER['QUERY_STRING'];
            }
        }

        return false;
    }
    
    /**
     * Redirect to given URL.
     *
     * @param string  $url         The target URL.
     * @param boolean $isPermanent A flag to set the redirect headers (301/302).
     *
     * @return void
     */
    protected function _sendRedirectHeaders($url, $isPermanent = false)
    {
        if ($isPermanent) {
            header('HTTP/1.1 301 Moved Permanently');
        }

        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Pragma: no-cache');
        header('Location: ' . $url);

        exit;
    }

    /**
     * Dispatch registered events on link view.
     * 
     * @return Rootd_Link_Model_Node
     */
    protected function _triggerEvents()
    {
        Mage::dispatchEvent(self::EVENT_VIEW, array('object' => $this));

        $otherEvents = explode(',', $this->getEventTriggers());

        foreach ($otherEvents as $event) {
            Mage::dispatchEvent($event, array('object' => $this));
        }

        return $this;
    }

    /**
     * Get the attachment model.
     * 
     * @return Rootd_Link_Model_Node_Attachment
     */
    public function getAttachment()
    {
        if (!$this->_attachment) {
            $attachment = Mage::getModel('link/node_attachment');

            if ($this->getObjectId()) {
                $attachment->load($this->getObjectId());
            }

            $this->_attachment = $attachment;
        }

        return $this->_attachment;
    }

    /**
     * Get the gateway page URL.
     * 
     * @return string
     */
    public function getGatewayUrl()
    {
        return Mage::getUrl(Mage::helper('link')->getFrontName() . "/protect/{$this->getId()}");
    }

    /**
     * Get the public link URL.
     * 
     * @return string
     */
    public function getPublicUrl()
    {
        if ($this->getObjectId()) {
            $path = $this->getAttachment()->getRequestPath();
        } else {
            $path = $this->getRequestPath();
        }

        if (substr($path, 0, 1) != '/') {
            $path = '/' . $path;
        }

        if ($this->_getIsExternal($path)) {
            $base = '';
        } else {
            $base = rtrim(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB), '/');
        }

        return $base . $path;
    }

    /**
     * Get the store ID.
     * 
     * @return integer|null
     */
    public function getStoreId()
    {
        return $this->_getData('store_id');
    }

    /**
     * Check for an option on the link.
     * 
     * @param string $key The option key.
     * 
     * @return boolean
     */
    public function hasOption($key)
    {
        $options = explode(',', $this->getOptions());

        return array_search($key, $options) !== false;
    }

    /**
     * Load link information for request.
     *
     * @param mixed $path
     * 
     * @return Rootd_Link_Model_Node
     */
    public function loadByRequestPath($path)
    {
        $this->setId(null);

        $this->_getResource()->loadByRequestPath($this, $path);

        $this->_afterLoad();

        $this->setOrigData();
        $this->_hasDataChanges = false;

        return $this;
    }

    /**
     * Implement logic of link rewrites.
     *
     * @param Zend_Controller_Request_Http  $request  The request object.
     * @param Zend_Controller_Response_Http $response The response object.
     * 
     * @return boolean
     */
    public function rewrite(Zend_Controller_Request_Http $request = null, Zend_Controller_Response_Http $response = null)
    {
        if (!Mage::isInstalled()) {
            return false;
        }

        if (is_null($request)) {
            $request = Mage::app()->getFrontController()->getRequest();
        }

        if (is_null($response)) {
            $response = Mage::app()->getFrontController()->getResponse();
        }

        if (is_null($this->getStoreId()) || false===$this->getStoreId()) {
            $this->setStoreId(Mage::app()->getStore()->getId());
        }

        /**
         * We have two cases of incoming paths - with and without slashes at the end ("/somepath/" and "/somepath").
         * Each of them matches two url rewrite request paths - with and without slashes at the end ("/somepath/" and "/somepath").
         * Choose any matched rewrite, but in priority order that depends on same presence of slash and query params.
         */
        $requestCases = array();
        $pathInfo = $request->getPathInfo();
        $origSlash = (substr($pathInfo, -1) == '/') ? '/' : '';
        $requestPath = trim($pathInfo, '/');

        // If there were final slash - add nothing to less priority paths. And vice versa.
        $altSlash = $origSlash ? '' : '/';

        $queryString = $this->_getQueryString(); // Query params in request, matching "path + query" has more priority

        if ($queryString) {
            $requestCases[] = $requestPath . $origSlash . '?' . $queryString;
            $requestCases[] = $requestPath . $altSlash . '?' . $queryString;
        }

        $requestCases[] = $requestPath . $origSlash;
        $requestCases[] = $requestPath . $altSlash;
        
        $this->loadByRequestPath($requestCases);

        if (!$this->getId()) {
            return false;
        }

        if (
            $this->getPassword() && 
            (Mage::helper('core')->encrypt($request->getParam('link_password')) != $this->getPassword())
        )
        {
            // Add message if password was specified
            if (!is_null($request->getParam('link_password'))) {
                Mage::getSingleton('core/session')->addError(Mage::helper('link')->__('The password provided for this link is invalid.'));
            }

            $this->_sendRedirectHeaders($this->getGatewayUrl(), true);

            exit;
        }

        $request->setAlias(Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS, $this->getRequestPath());
        $isPermanentRedirectOption = $this->hasOption('RP');

        if ($this->_getIsExternal($this->getTargetPath())) {
            $destinationStoreCode = Mage::app()->getStore($this->getStoreId())->getCode();
            Mage::app()->getCookie()->set(Mage_Core_Model_Store::COOKIE_NAME, $destinationStoreCode, true);

            $this->_triggerEvents();
            $this->_sendRedirectHeaders($this->getTargetPath(), $isPermanentRedirectOption);
        } else {
            $targetUrl = $request->getBaseUrl(). '/' . $this->getTargetPath();
        }

        $isRedirectOption = $this->hasOption('R');

        if ($isRedirectOption || $isPermanentRedirectOption) {
            if (Mage::getStoreConfig('web/url/use_store') && $storeCode = Mage::app()->getStore()->getCode()) {
                $targetUrl = $request->getBaseUrl(). '/' . $storeCode . '/' .$this->getTargetPath();
            }

            $this->_triggerEvents();
            $this->_sendRedirectHeaders($targetUrl, $isPermanentRedirectOption);
        }

        if (Mage::getStoreConfig('web/url/use_store') && $storeCode = Mage::app()->getStore()->getCode()) {
            $targetUrl = $request->getBaseUrl(). '/' . $storeCode . '/' .$this->getTargetPath();
        }

        $queryString = $this->_getQueryString();

        if ($queryString) {
            $targetUrl .= '?'.$queryString;
        }

        $request->setRequestUri($targetUrl);
        $request->setPathInfo($this->getTargetPath());
        $this->_triggerEvents();

        return true;
    }

    public function setAttachment(Rootd_Link_Model_Node_Attachment $attachment)
    {
        $this->_attachment = $attachment;

        return $this;
    }

}