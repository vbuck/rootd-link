<?php

class Rootd_Link_Adminhtml_LinkController 
    extends Mage_Adminhtml_Controller_Action
{

    /**
     * Check for an existing URL rewrite at the given path.
     * 
     * @param string $path The request path to check.
     * 
     * @return boolean|integer
     */
    protected function _checkUrlRewrite($path)
    {
        // Check against every rewrite, irrespective of store
        $resource   = Mage::getResourceSingleton('core/url_rewrite');
        $select     = $resource->getReadConnection()
            ->select()
            ->from($resource->getMainTable())
            ->where('request_path IN (?)', $path);

        $id = $resource->getReadConnection()->fetchOne($select);

        return $id;
    }

    /**
     * Initialize the action.
     * 
     * @return void
     */
    protected function _initAction() {
        $this->loadLayout()
            ->_addBreadcrumb(
                Mage::helper('link')->__('Rootd Links'), 
                Mage::helper('link')->__('Rootd Links')
            );

        return $this;
    }

    /**
     * Verify area permissions.
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('link/link');
    }

    /**
     * Save the link attachment
     *
     * @param Rootd_Link_Model_Node The link model.
     * 
     * @return Rootd_Link_Adminhtml_LinkController
     */
    protected function _saveAttachment(Rootd_Link_Model_Node $model)
    {
        $helper = Mage::helper('link');

        try {
            if (isset($_FILES['target_file']) && !empty($_FILES['target_file']['name'])) {
                $file   = $_FILES['target_file']['name'];                      
                $path   = $helper->generateAttachmentPath($file, true);

                $uploader = new Varien_File_Uploader('target_file');

                $uploader->setAllowCreateFolders(true);
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);

                $uploader->save(dirname($path), $file);

                $model->setTargetFile($helper->generateAttachmentPath($file, false))
                    ->setSaveAttachmentFlag(true);
            }
        } catch (Exception $error) {
            Mage::getSingleton('adminhtml/session')->addNotice($helper->__("Failed to upload attachment: {$error->getMessage()}"));
        }

        return $this;
    }

    /**
     * Delete attachment AJAX action.
     * 
     * @return void
     */
    public function deleteattachmentAction()
    {
        try {
            if ( ($id = $this->getRequest()->getParam('id')) ) {
                $attachment = Mage::getModel('link/node_attachment')->load($id);

                $attachment->delete();
            }
        } catch (Exception $error) { }
    }

    /**
     * Delete action.
     *
     * @return void
     */
    public function deleteAction()
    {
        $helper = Mage::helper('link');

        if ( ($id = $this->getRequest()->getParam('link_id')) ) {
            try {
                Mage::getModel('link/node')
                    ->load($id)
                    ->delete();
                
                Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('This link has been deleted.'));
                
                $this->_redirect('*/*/');

                return;

            } catch (Exception $error) {
                Mage::getSingleton('adminhtml/session')->addError($error->getMessage());
                
                $this->_redirect('*/*/edit', array('link_id' => $id));

                return;
            }
        }

        Mage::getSingleton('adminhtml/session')->addError($helper->__('Unable to find a block to delete.'));

        $this->_redirect('*/*/');
    }

    /**
     * Edit link action.
     *
     * @return void
     */
    public function editAction()
    {
        $helper = Mage::helper('link');

        $this->_title($helper->__('CMS'))
            ->_title($helper->__('Rootd Links'));

        $id     = $this->getRequest()->getParam('link_id');
        $model  = Mage::getModel('link/node');

        if ($id) {
            $model->load($id);

            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($helper->__('This link does not exist.'));

                $this->_redirect('*/*/');

                return;
            }
        }

        $this->_title($model->getId() ? $model->getTitle() : $helper->__('New Link'));

        // Set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);

        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('link_model', $model);

        $this->_initAction()
            ->_addBreadcrumb(
                $id ? $helper->__('Edit Link') : $helper->__('New Link'), 
                $id ? $helper->__('Edit Link') : $helper->__('New Link')
            )
            ->renderLayout();
    }

    /**
     * Default grid action.
     * 
     * @return void
     */
    public function indexAction() {
        $this->_initAction()
            ->renderLayout();
    }

    /**
     * New link action.
     *
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Save action.
     *
     * @return void
     */
    public function saveAction()
    {
        $helper = Mage::helper('link');

        if ( ($data = $this->getRequest()->getPost()) ) {

            $id     = $this->getRequest()->getParam('link_id');
            $model  = Mage::getModel('link/node')->load($id);

            if (!$model->getId() && $id) {
                Mage::getSingleton('adminhtml/session')->addError($helper->__('This link no longer exists.'));

                $this->_redirect('*/*/');

                return;
            }

            $model->setData($data);

            // Encrypt passwords
            if ($model->getPassword()) {
                $model->setPassword(Mage::helper('core')->encrypt($model->getPassword()));
            }

            // Convert dates
            if ($model->getActiveFrom()) {
                $model->setActiveFrom(date(Varien_Date::DATE_PHP_FORMAT, strtotime($model->getActiveFrom())));
            }

            if ($model->getActiveTo()) {
                $model->setActiveTo(date(Varien_Date::DATE_PHP_FORMAT, strtotime($model->getActiveTo())));
            }

            // Concatenate events list
            if ($model->getEventTriggers()) {
                $events = array_filter( (preg_split("/[\r\n\t\s]+/", $model->getEventTriggers())) );
                $model->setEventTriggers(implode(',', $events));
            }

            try {
                $this->_saveAttachment($model);
                
                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('The link has been saved.'))
                    ->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('link_id' => $model->getId()));

                    return;
                }
                
                $this->_redirect('*/*/');

                return;

            } catch (Exception $error) {
                Mage::getSingleton('adminhtml/session')->addError($error->getMessage())
                    ->setFormData($data);
                
                $this->_redirect('*/*/edit', array('link_id' => $this->getRequest()->getParam('link_id')));

                return;
            }
        }

        $this->_redirect('*/*/');
    }

    /**
     * Request path validation action.
     * 
     * @return void
     */
    public function validatepathAction()
    {
        $helper     = Mage::helper('link');
        $response   = array(
            'status'    => 'error',
            'message'   => 'unknown',
        );

        try {
            $path = $this->getRequest()->getParam('request_path');

            // Check against CMS pages
            if (Mage::getSingleton('cms/page')->checkIdentifier($path, Mage::app()->getStore()->getStoreId())) {
                $response['status']     = 'warning';
                $response['message']    = $helper->__('Potential conflict detected: a CMS page with this identifier already exists.');
            } else if ($this->_checkUrlRewrite($path)) { // Check against URL rewrites
                $response['status']     = 'warning';
                $response['message']    = $helper->__('Potential conflict detected: a URL rewrite with this path already exists.');
            } else {
                $response['status']     = 'success';
                $response['message']    = $helper->__('Request path is unique in the system.');
            }
        } catch (Exception $error) {
            $response['status']     = 'error';
            $response['message']    = $error->getMessage();
        }

        $this->getResponse()
            ->setHeader('Content-type', 'application/json')
            ->setBody(Mage::helper('core')->jsonEncode($response));
    }

}