<?php

class Balance_Exclusion_Block_Adminhtml_Exclusion_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('exclusionGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('exclusion/exclusion')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn(
            'id',
            array(
                'header' => Mage::helper('exclusion')->__('Id'),
                'align'  => 'right',
                'width'  => '50px',
                'index'  => 'id',
            )
        );

        $this->addColumn(
            'term',
            array(
                'header' => Mage::helper('exclusion')->__('Term'),
                'align'  => 'right',
                'index'  => 'term',
            )
        );


        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('term');
        $this->getMassactionBlock()->setFormFieldName('id');
        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label'   => Mage::helper('exclusion')->__('Delete'),
                'url'     => $this->getUrl('*/*/massDelete', array('' => '')),
                // public function massDeleteAction() in Mage_Adminhtml_Tax_RateController
                'confirm' => Mage::helper('exclusion')->__('Are you sure?')
            )
        );

        return $this;
    }

}