<?php

/**
 * Rootd link form block.
 *
 * @package   Rootd_Link
 * @author    Rick Buczynski <me@rickbuczynski.com>
 * @copyright 2014 Rick Buczynski. All Rights Reserved.
 */

class Rootd_Link_Block_Adminhtml_Link_Edit_Form 
    extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Initialize form.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('link_form');
        $this->setTitle(Mage::helper('link')->__('Link Setup'));
    }

    /**
     * Prepare layout.
     *
     * @return Rootd_Link_Block_Adminhtml_Link_Edit_Form
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        return $this;
    }

    /**
     * Prepare the form.
     * 
     * @return Rootd_Link_Block_Adminhtml_Link_Edit_Form
     */
    protected function _prepareForm()
    {
        $helper = Mage::helper('link');
        $model  = Mage::registry('link_model');

        /**
         * Form setup
         *******************************************/

        $form   = new Varien_Data_Form(
            array(
                'id'        => 'edit_form', 
                'action'    => $this->getUrl('adminhtml/link/save'), 
                'method'    => 'post',
                'enctype'   => 'multipart/form-data',
            )
        );

        $form->setHtmlIdPrefix('link_');

        /**
         * Fieldset setup
         *******************************************/

        $fieldsetBase = $form->addFieldset(
            'base_fieldset', 
            array(
                'legend' => $helper->__('Settings'), 
            )
        );

        $fieldsetBase->addType('target_selector', 'Rootd_Link_Lib_Varien_Data_Form_Element_Targetselector');

        $fieldsetActive = $form->addFieldset(
            'active_fieldset', 
            array(
                'legend' => $helper->__('Availability'), 
            )
        );

        $fieldsetAdvanced = $form->addFieldset(
            'advanced_fieldset', 
            array(
                'legend'    => $helper->__('Advanced'), 
            )
        );

        /**
         * Base fields
         *******************************************/

        if ($model->getLinkId()) {
            $fieldsetBase->addField(
                'link_id', 
                'hidden', 
                array(
                    'name' => 'link_id',
                )
            );
        }

        if ($model->getId() && $model->getRequestPath()) {
            $fieldsetBase->addField(
                'public_url', 
                'label', 
                array(
                    'label'     => $helper->__('Public URL'),
                    'title'     => $helper->__('Public URL'),
                    'after_element_html'
                                => '<a href="' . $model->getPublicUrl() . '" target="_blank">' . $model->getPublicUrl() . '</a>',
                )
            );

            $fieldsetBase->addField(
                'auto_url', 
                'label', 
                array(
                    'label'     => $helper->__('Natural URL'),
                    'title'     => $helper->__('Natural URL'),
                    'after_element_html'
                                => '<a href="' . $helper->getNodeUrl($model->getId()) . '" target="_blank">' . $helper->getNodeUrl($model->getId()) . '</a>',
                )
            );
        }

        if ($model->getCreatedAt()) {
            $fieldsetBase->addField(
                'created_at', 
                'label', 
                array(
                    'name'      => 'created_at',
                    'label'     => $helper->__('Created'),
                    'title'     => $helper->__('Created'),
                    'required'  => false,
                )
            );
        }

        if (!Mage::app()->isSingleStoreMode()) {
            $field = $fieldsetBase->addField(
                'store_id', 
                'multiselect', 
                array(
                    'name'      => 'stores[]',
                    'label'     => $helper->__('Store View'),
                    'title'     => $helper->__('Store View'),
                    'required'  => true,
                    'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
                )
            );

            $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');

            $field->setRenderer($renderer);
        } else {
            $fieldsetBase->addField(
                'store_id',
                'hidden',
                array(
                    'name'      => 'stores[]',
                    'value'     => Mage::app()->getStore(true)->getId(),
                )
            );
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }

        $fieldsetBase->addField(
            'description', 
            'text', 
            array(
                'name'      => 'description',
                'label'     => $helper->__('Description'),
                'title'     => $helper->__('Description'),
                'required'  => false,
            )
        );

        $fieldsetBase->addField(
            'request_path', 
            'text', 
            array(
                'name'      => 'request_path',
                'label'     => $helper->__('Request Path'),
                'title'     => $helper->__('Request Path'),
                'onchange'  => "validateRequestPath(this.value, '{$this->getUrl('adminhtml/link/validatepath')}');",
                'required'  => true,
                'after_element_html'
                            => $helper->__('
                                <p class="note">The incoming path used by the visitor.</p>
                                <script type="text/javascript">
                                    $(document).observe(
                                        "dom:loaded", 
                                        validateRequestPath.bind(null, $("link_request_path").value, "' . $this->getUrl('adminhtml/link/validatepath') . '")
                                    );
                                </script>
                            '),
            )
        );

        $fieldsetBase->addField(
            'target_path', 
            'target_selector', 
            array(
                'name'                  => 'target_path',
                'label'                 => $helper->__('Target Path'),
                'title'                 => $helper->__('Target Path'),
                'required'              => true,
                'has_attachment'        => $model->getObjectId(),
                'after_element_html'    => $helper->__('<p class="note">The outgoing path.</p>'),
            )
        );

        /**
         * Availability Fields
         *******************************************/

        $fieldsetActive->addField(
            'is_active', 
            'select', 
            array(
                'name'      => 'is_active',
                'label'     => $helper->__('Status'),
                'title'     => $helper->__('Status'),
                'values'    => Mage::getModel('adminhtml/system_config_source_enabledisable')->toOptionArray(),
                'required'  => false,
                'after_element_html'
                            => $helper->__('<p class="note">When disabled, this link will not be available to visitors.</p>'),
            )
        );

        $fieldsetActive->addField(
            'active_from', 
            'date', 
            array(
                'name'      => 'active_from',
                'label'     => $helper->__('Active From'),
                'title'     => $helper->__('Active From'),
                'image'     => $this->getSkinUrl('images/grid-cal.gif'),
                'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
                'required'  => false,
            )
        );

        $fieldsetActive->addField(
            'active_to', 
            'date', 
            array(
                'name'      => 'active_to',
                'label'     => $helper->__('Active To'),
                'title'     => $helper->__('Active To'),
                'image'     => $this->getSkinUrl('images/grid-cal.gif'),
                'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
                'required'  => false,
            )
        );

        $fieldsetActive->addField(
            'password', 
            'password', 
            array(
                'name'      => 'password',
                'label'     => $helper->__('Password'),
                'title'     => $helper->__('Password'),
                'required'  => false,
                'after_element_html'
                            => $helper->__('<p class="note">Visitors will be prompted to enter this password to access the link.</p>'),
            )
        );

        /**
         * Advanced Fields
         *******************************************/

        $fieldsetAdvanced->addField(
            'event_triggers', 
            'textarea', 
            array(
                'name'      => 'event_triggers',
                'label'     => $helper->__('Events'),
                'title'     => $helper->__('Events'),
                'style'     => 'height:4em;',
                'required'  => false,
                'after_element_html'
                            => $helper->__('
                                <p>' . Rootd_Link_Model_Node::EVENT_VIEW . '</p>
                                <p class="note">A list of system events to trigger on link view. Enter one event name per line.</p>
                            '),
            )
        );

        /**
         * Finish preparations
         *******************************************/

        // Change target path to attachment path for display only
        if ($model->getObjectId()) {
            $model->setTargetPath($model->getAttachment()->getTargetPath());
        }

        // Decrypt password for edit
        if ($model->getPassword()) {
            $model->setPassword(Mage::helper('core')->decrypt($model->getPassword()));
        }

        // Expand events into lines
        if ($model->getEventTriggers()) {
            $model->setEventTriggers(preg_replace('/,+/', "\n", $model->getEventTriggers()));
        }

        $form->setValues($model->getData())
            ->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
