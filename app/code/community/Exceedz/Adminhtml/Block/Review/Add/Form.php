<?php
/**
 * Adminhtml add product review form
 *
 * @category   Exceedz
 * @package    Exceedz_Adminhtml
 */

class Exceedz_Adminhtml_Block_Review_Add_Form extends Mage_Adminhtml_Block_Review_Add_Form
{
    protected function _prepareForm()
    {
        $statuses = Mage::getModel('review/review')
            ->getStatusCollection()
            ->load()
            ->toOptionArray();

        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('add_review_form', array('legend' => Mage::helper('review')->__('Review Details')));

        $fieldset->addField('product_name', 'note', array(
            'label'     => Mage::helper('review')->__('Product'),
            'text'      => 'product_name',
        ));

        $fieldset->addField('detailed_rating', 'note', array(
            'label'     => Mage::helper('review')->__('Product Rating'),
            'required'  => true,
            'text'      => '<div id="rating_detail">' . $this->getLayout()->createBlock('adminhtml/review_rating_detailed')->toHtml() . '</div>',
        ));

        $fieldset->addField('status_id', 'select', array(
            'label'     => Mage::helper('review')->__('Status'),
            'required'  => true,
            'name'      => 'status_id',
            'values'    => $statuses,
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('select_stores', 'multiselect', array(
                'label'     => Mage::helper('review')->__('Visible In'),
                'required'  => true,
                'name'      => 'select_stores[]',
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm()
            ));
        }

        $fieldset->addField('nickname', 'text', array(
            'name'      => 'nickname',
            'title'     => Mage::helper('review')->__('Nickname'),
            'label'     => Mage::helper('review')->__('Nickname'),
            'maxlength' => '50',
            'required'  => true,
        ));

        $fieldset->addField('title', 'text', array(
            'name'      => 'title',
            'title'     => Mage::helper('review')->__('Summary of Review'),
            'label'     => Mage::helper('review')->__('Summary of Review'),
            'maxlength' => '255',
            'required'  => true,
        ));

        $fieldset->addField('detail', 'textarea', array(
            'name'      => 'detail',
            'title'     => Mage::helper('review')->__('Review'),
            'label'     => Mage::helper('review')->__('Review'),
            'style'     => 'width: 98%; height: 600px;',
            'required'  => true,
        ));

        $fieldset->addField('product_id', 'hidden', array(
            'name'      => 'product_id',
        ));

        $fieldset->addField('created_at', 'date', array(
                'name'      => 'created_at',
                'label'     => Mage::helper('review')->__('Created At'),
                'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
                'time'      => true,
                'image'     => $this->getSkinUrl('images/grid-cal.gif')
            )
        );

        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setAction($this->getUrl('*/*/post'));

        $this->setForm($form);
    }
}
