<?php

class Magestore_Megamenu_Block_Adminhtml_Template_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct(){
            parent::__construct();
            $this->setId('megamenuGrid');
            $this->setDefaultSort('template_id');
            $this->setDefaultDir('ASC');
            $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection(){
            $collection = Mage::getModel('megamenu/template')->getCollection();
            $this->setCollection($collection);
            return parent::_prepareCollection();
    }

    protected function _prepareColumns(){
            $this->addColumn('template_id', array(
                    'header'	=> Mage::helper('megamenu')->__('ID'),
                    'align'	 =>'right',
                    'width'	 => '50px',
                    'index'	 => 'template_id',
            ));

            $this->addColumn('name_template', array(
                    'header'	=> Mage::helper('megamenu')->__('Name'),
                    'align'	 =>'left',
                    'index'	 => 'name_template',
            ));
            $this->addColumn('description', array(
                    'header'	=> Mage::helper('megamenu')->__('Description'),
                    'align'	 =>'left',
                    'index'	 => 'description',
            ));               
            $this->addColumn('action',
                    array(
                            'header'	=>	Mage::helper('megamenu')->__('Action'),
                            'width'		=> '100',
                            'type'		=> 'action',
                            'getter'	=> 'getId',
                            'actions'	=> array(
                                    array(
                                            'caption'	=> Mage::helper('megamenu')->__('Edit'),
                                            'url'		=> array('base'=> '*/*/edit'),
                                            'field'		=> 'id'
                                    )),
                            'filter'	=> false,
                            'sortable'	=> false,
                            'index'		=> 'stores',
                            'is_system'	=> true,
            ));
            $this->addExportType('*/*/exportCsv', Mage::helper('megamenu')->__('CSV'));
            $this->addExportType('*/*/exportXml', Mage::helper('megamenu')->__('XML'));

            return parent::_prepareColumns();
    }

    protected function _prepareMassaction(){
            $this->setMassactionIdField('template_id');
            $this->getMassactionBlock()->setFormFieldName('template');

            $this->getMassactionBlock()->addItem('delete', array(
                    'label'		=> Mage::helper('megamenu')->__('Delete'),
                    'url'		=> $this->getUrl('*/*/massDelete'),
                    'confirm'	=> Mage::helper('megamenu')->__('Are you sure?')
            ));

            return $this;
    }

    public function getRowUrl($row){
            return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}