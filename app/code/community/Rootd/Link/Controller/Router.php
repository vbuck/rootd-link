<?php

/**
 * Module router.
 *
 * @package   Rootd_Link
 * @author    Rick Buczynski <me@rickbuczynski.com>
 * @copyright 2014 Rick Buczynski. All Rights Reserved.
 */

class Rootd_Link_Controller_Router 
    extends Mage_Core_Controller_Varien_Router_Standard
{

    /**
     * Ensure that we are not in admin area and that
     * the module is enabled.
     *
     * @return bool
     */
    protected function _beforeModuleMatch()
    {
        if (Mage::getStoreConfigFlag('rootd_link/options/enabled')) {
            return parent::_beforeModuleMatch();
        }

        return false;
    }

    /**
     * Initialize router
     *
     * @param Varien_Event_Observer $observer
     *
     * @return void
     */
    public function initRouter($observer)
    {
        $front = $observer->getEvent()->getFront();

        $front->addRouter('link', $this);
    }

    /**
     * Match the request.
     *
     * @param Zend_Controller_Request_Http $request The request object.
     * 
     * @return boolean
     */
    public function match(Zend_Controller_Request_Http $request)
    {
        if (!$this->_beforeModuleMatch()) {
            return false;
        }

        $this->fetchDefault();

        $front  = $this->getFront();
        $path   = trim($request->getPathInfo(), '/');
        $module = null;

        if ($path) {
            $parts = explode('/', $path);
        } else {
            $parts = explode('/', $this->_getDefaultPath());
        }

        // get module name
        if ($request->getModuleName()) {
            $module = $request->getModuleName();
        }

        if (!$module) {
            if (Mage::app()->getStore()->isAdmin()) {
                return false;
            }
        }

        // Does module match front name
        $loadByPath = false;
        if ($module != Mage::helper('link')->getFrontName()) {
            // If not, check the table for a match on the path
            if ( ($link = Mage::getModel('link/node')->loadByRequestPath($path)) ) {
                $module     = Mage::helper('link')->getFrontName();
                $loadByPath = true;
            }
        }

        // Translate frontName to module
        $realModule = Mage::helper('link')->getModuleName();

        $request->setRouteName($this->getRouteByFrontName($module));

        // Get controller name
        $controller = $front->getDefault('controller');
        // Get action name
        $action = $front->getDefault('action');
        
        if ($loadByPath) {
            $request->setAlias(
                Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
                ltrim( implode('/', $parts ), '/' )
            );
        } else {
            $request->setAlias(
                Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
                ltrim( implode('/', ( array_slice($parts, 1) ) ), '/' )
            );
        }

        // Checking if this place should be secure
        $this->_checkShouldBeSecure($request, "/{$module}/{$controller}/{$action}");

        $controllerClassName = $this->_validateControllerClassName($realModule, $controller);

        if (!$controllerClassName) {
            return false;
        }

        // Instantiate controller class
        $controllerInstance = Mage::getControllerInstance($controllerClassName, $request, $front->getResponse());

        if (!$controllerInstance->hasAction($action)) {
            return false;
        }

        // Set values
        $request->setModuleName($module);
        $request->setControllerName($controller);
        $request->setActionName($action);
        $request->setControllerModule($realModule);

        // Set parameters from pathinfo
        for ($i = 1, $length = sizeof($parts); $i < $length; $i += 2) {
            $request->setParam($parts[$i], isset($parts[$i + 1]) ? urldecode($parts[$i + 1]) : '');
        }

        // Dispatch action
        $request->setDispatched(true);
        $controllerInstance->dispatch($action);

        return true;
    }

}