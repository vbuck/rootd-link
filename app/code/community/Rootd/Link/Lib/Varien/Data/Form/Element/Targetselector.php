<?php

class Rootd_Link_Lib_Varien_Data_Form_Element_Targetselector
    extends Varien_Data_Form_Element_Text
{

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);

        $this->setType('label');

        // Disable target path input when link points to an attachment
        if ($this->getHasAttachment()) {
            $this->setDisabled(true);
        }
    }

    protected function _getAttachmentSelectorHtml()
    {
        $checkbox = new Varien_Data_Form_Element_Checkbox(
            array(
                'html_id'   => 'attachment_toggle',
                'checked'   => false,
                'value'     => '1',
                'onchange'  => 'toggleAttachment(this.checked);',
                'after_element_html'
                            => Mage::helper('link')->__('Use an attachment'),
            )
        );

        $checkbox->setForm($this->getForm());

        $file = new Varien_Data_Form_Element_File(
            array(
                'name'      => 'target_file',
                'html_id'   => 'target_file',
            )
        );

        $file->setForm($this->getForm());

        return 
            $this->_getCurrentAttachmentHtml() . '
            <label class="target-label" id="target_file_toggle" style="display:' . ($this->getHasAttachment() ? 'none' : 'block') . ';">
                ' . $checkbox->getElementHtml() . '
            </label>
            <label class="target-label" id="target_file_container" style="display:none;">
                ' . $file->getElementHtml() . '
            </label>
        ';
    }

    protected function _getCurrentAttachmentHtml()
    {
        $helper = Mage::helper('link');
        $link   = Mage::registry('link_model');
        $html   = '';

        if ($link && $link->getAttachment()->getId()) {
            $attachment = $link->getAttachment();

            $html .= '
                <div id="target_file_status" class="target-label">
                    <div>' . $helper->__('Linked to attachment: ') . basename($attachment->getTargetPath()) . '</div>
                    <div>
                        <button 
                            type="button" 
                            onclick="popWin(\'' . $this->_escape($attachment->getTargetUrl()) . '\');"
                            class="scalable"
                            >' . $helper->__('Open') . '</button>
                        <button 
                            type="button" 
                            onclick="removeAttachment(\'' . Mage::helper('adminhtml')->getUrl('adminhtml/link/deleteattachment', array('id' => $attachment->getId())) . '\');"
                            class="scalable"
                            >' . $helper->__('Remove') . '</button>
                    </div>
                </div>
            ';
        }

        return $html;
    }

    public function getElementHtml()
    {
        $html  = '
            <div class="target-control">
                ' . parent::getElementHtml() . '
                ' . $this->getOptionsHtml() . '
            </div>
        ';

        return $html;
    }

    public function getOptionsHtml()
    {
        $html = '';

        $html .= $this->_getAttachmentSelectorHtml();

        return $html;
    }

}