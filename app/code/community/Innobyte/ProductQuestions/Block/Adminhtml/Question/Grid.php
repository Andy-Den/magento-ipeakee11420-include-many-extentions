<?php

class Innobyte_ProductQuestions_Block_Adminhtml_Question_Grid 
    extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('questionGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        if (Mage::registry('inno_IsProductTabView')) {
            $this->setUseAjax(true);
        }
    }

    protected function _prepareCollection()
    {
        $productId = $this->getRequest()->getParam('id');
        $collection = Mage::getModel('innobyte_product_questions/question')
            ->getCollection();
        if ($productId) {
            $collection->addFieldToFilter('product_id',
                                          array('in' => $productId));
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('innobyte_product_questions');
        $this->addColumn('id',
                         array(
            'header' => $helper->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'id',
        ));
        $this->addColumn('created',
                         array(
            'header' => $helper->__('Date'),
            'align' => 'left',
            'index' => 'created',
            'filter' => 'Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Datetime',
            'renderer' => 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Datetime',
        ));
        $this->addColumn('customer_name',
                         array(
            'header' => $helper->__('Author'),
            'align' => 'left',
            'index' => 'customer_name',
        ));
        $this->addColumn('customer_email',
                         array(
            'header' => $helper->__('Email'),
            'align' => 'left',
            'index' => 'customer_email',
        ));

        $this->addColumn('content',
                         array(
            'header' => $helper->__('Question'),
            'align' => 'left',
            'index' => 'content',
        ));
        $this->addColumn('answer',
                         array(
            'header' => $helper->__('Answer'),
            'align' => 'left',
            'index' => 'answer',
            'renderer' => 'Innobyte_ProductQuestions_Block_Renderer_Html',
        ));
        $this->addColumn('status',
                         array(
            'header' => $helper->__('Status'),
            'align' => 'left',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                Innobyte_ProductQuestions_Block_Question::STATUS_NOT_RESPONSED => $helper->__('Not responsed'),
                Innobyte_ProductQuestions_Block_Question::STATUS_RESPONSED => $helper->__('Responsed')
            ),
        ));
        
        if ( ! Mage::registry('inno_IsProductTabView') ) {
            $this->addColumn('product_id',
                             array(
                'header' => $helper->__('Product'),
                'align' => 'left',
                'index' => 'product_id',
                'type' => 'action',
                'renderer' => 'Innobyte_ProductQuestions_Block_Renderer_ProductLink',
                'filter' => false,
                'sortable' => false,
            ));
        }
        
        $this->addColumn('visibility',
                         array(
            'header' => $helper->__('Visibility'),
            'align' => 'left',
            'index' => 'visibility',
            'type' => 'options',
            'options' => array(
                Innobyte_ProductQuestions_Block_Question::NOT_VISIBLE => $helper->__('Private'),
                Innobyte_ProductQuestions_Block_Question::VISIBLE => $helper->__('Public')
            ),
        ));

        $this->addColumn('votes',
                         array(
            'header' => $helper->__('Votes Count'),
            'align' => 'left',
            'index' => 'votes',
            'sortable' => false,
        ));
        
        
        $this->addColumn('action',
            array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('catalog')->__('Edit'),
                        'url'     => array(
                            'base'=>'*/*/edit',
                            'params'=>array('store'=>$this->getRequest()->getParam('store'))
                        ),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}