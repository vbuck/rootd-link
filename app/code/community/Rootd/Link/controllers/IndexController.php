<?php

/**
 * Rootd link controller.
 *
 * @package   Rootd_Link
 * @author    Rick Buczynski <me@rickbuczynski.com>
 * @copyright 2014 Rick Buczynski. All Rights Reserved.
 */

class Rootd_Link_IndexController
    extends Mage_Core_Controller_Front_Action
{

    const ID_PARAM_NAME = 'node';

    /**
     * Authenticate (gateway) action.
     * 
     * @return void
     */
    public function authAction()
    {
        $this->loadLayout();

        Mage::register('protected_link', $this->getRequest()->getParam('protect'));

        $this->renderLayout();
    }

    /**
     * Primary action.
     * 
     * @return void
     */
    public function indexAction()
    {
        // Check for auth requests
        if (($this->getRequest()->getParam('protect'))) {
            $this->_forward('auth');
        } else if (!Mage::getSingleton('link/node')->rewrite($this->getRequest(), $this->getResponse())) { // Else attempt a rewrite
            // Fallback to no route
            $this->norouteAction();

            return;
        }
    }

}