<?php

class Magestore_Megamenu_Block_Adminhtml_Megamenu_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct(){
            parent::__construct();
            $this->setId('megamenuGrid');
            $this->setDefaultSort('megamenu_id');
            $this->setDefaultDir('ASC');
            $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection(){
            $collection = Mage::getModel('megamenu/megamenu')->getCollection();
            $this->setCollection($collection);
            return parent::_prepareCollection();
    }

    protected function _prepareColumns(){
            $this->addColumn('megamenu_id', array(
                    'header'	=> Mage::helper('megamenu')->__('ID'),
                    'align'	 =>'right',
                    'width'	 => '50px',
                    'index'	 => 'megamenu_id',
            ));

            $this->addColumn('name_menu', array(
                    'header'	=> Mage::helper('megamenu')->__('Name'),
                    'align'	 =>'left',
                    'index'	 => 'name_menu',
            ));
            $this->addColumn('megamenu_type', array(
                'header'    =>  Mage::helper('megamenu')->__('Menu Type'),
                'align'     =>  'left',
                'index'     =>  'megamenu_type',
                'type'      =>  'options',
                'options'   =>  Mage::helper('megamenu')->megamenuTypeToOptionArray(),
            ));
            $this->addColumn('menu_type', array(
                'header'    =>  Mage::helper('megamenu')->__('SubMenu Type'),
                'align'     =>  'left',
                'index'     =>  'menu_type',
                'type'      =>  'options',
                'options'   =>  Mage::helper('megamenu')->menuTypeToOptionArray(),
            ));
            $this->addColumn('link', array(
                    'header'	=> Mage::helper('megamenu')->__('Link'),
                    'align'	 =>'left',
                    'index'	 => 'link',
            ));

            /*$this->addColumn('staticblock_id', array(
                    'header'	=> Mage::helper('megamenu')->__('Static Block'),
                    'width'	 => '150px',
                    'index'	 => 'staticblock_id',
        'type' => 'options',
                    'options' => Mage::helper('megamenu')->getNameBlock(),
            ));*/

            $this->addColumn('status', array(
                    'header'	=> Mage::helper('megamenu')->__('Status'),
                    'align'	 => 'left',
                    'width'	 => '80px',
                    'index'	 => 'status',
                    'type'		=> 'options',
                    'options'	 => array(
                            1 => 'Enabled',
                            2 => 'Disabled',
                    ),
            ));
            $this->addColumn('sort_order', array(
                    'header'	=> Mage::helper('megamenu')->__('Sort Order'),
                    'width'	 => '50px',
                    'index'	 => 'sort_order',           			
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
            $this->setMassactionIdField('megamenu_id');
            $this->getMassactionBlock()->setFormFieldName('megamenu');

            $this->getMassactionBlock()->addItem('delete', array(
                    'label'		=> Mage::helper('megamenu')->__('Delete'),
                    'url'		=> $this->getUrl('*/*/massDelete'),
                    'confirm'	=> Mage::helper('megamenu')->__('Are you sure?')
            ));

            $statuses = Mage::getSingleton('megamenu/status')->getOptionArray();

            array_unshift($statuses, array('label'=>'', 'value'=>''));
            $this->getMassactionBlock()->addItem('status', array(
                    'label'=> Mage::helper('megamenu')->__('Change status'),
                    'url'	=> $this->getUrl('*/*/massStatus', array('_current'=>true)),
                    'additional' => array(
                            'visibility' => array(
                                    'name'	=> 'status',
                                    'type'	=> 'select',
                                    'class'	=> 'required-entry',
                                    'label'	=> Mage::helper('megamenu')->__('Status'),
                                    'values'=> $statuses
                            ))
            ));
            return $this;
    }

    public function getRowUrl($row){
            return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}