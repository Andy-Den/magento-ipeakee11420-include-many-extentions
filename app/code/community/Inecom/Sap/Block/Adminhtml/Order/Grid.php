<?php

class Inecom_Sap_Block_Adminhtml_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('orderGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('sap/queue')->getCollection();
        $collection
            ->addFieldToFilter('main_table.created_at', array(
                'from' => date('Y-m-d H:i:s', strtotime('-3 months'))
            ))
            ->getSelect()
            ->joinLeft(
                //array('table_alias' => Mage::getResourceModel('sap/order')->getTable('sap/order_queue')),
                'sales_flat_order',
                'sales_flat_order.entity_id = main_table.order_id',
                array('sales_flat_order.increment_id')
            );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('queue_id', array(
            'header' => Mage::helper('inecom_sap')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'queue_id',
        ));
        $this->addColumn('increment_id', array(
            'header' => Mage::helper('inecom_sap')->__('Order #'),
            'align' => 'right',
            'width' => '50px',
            'type'  => 'text',
            'index' => 'increment_id',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('inecom_sap')->__('Status'),
            'align' => 'center',
            'width' => '100px',
            'index' => 'status',
            'filter_index' => 'main_table.status',
            'type' => 'options',
            'options' => array(
                'delivered' => 'Delivered',
                'pending' => 'Pending',
                'failed' => 'Failed',
                'reported' => 'Reported',
            )
        ));

        $this->addColumn('messages', array(
            'header' => Mage::helper('inecom_sap')->__('Response Message'),
            'align' => 'left',
            'type'  => 'text',
            'index' => 'messages',
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('inecom_sap')->__('Queued At'),
            'index' => 'created_at',
            'filter_index' => 'main_table.created_at',
            'type' => 'datetime',
            'width' => '150px',
        ));

        $this->addColumn('updated_at', array(
            'header' => Mage::helper('inecom_sap')->__('Last Updated At'),
            'index' => 'updated_at',
            'filter_index' => 'main_table.updated_at',
            'type' => 'datetime',
            'width' => '150px',
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('inecom_sap')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('inecom_sap')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('queue_id');
        $this->getMassactionBlock()->setFormFieldName('queue');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('inecom_sap')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('inecom_sap')->__('Are you sure?')
        ));

        $statuses = Mage::helper('inecom_sap/order')->getOptionArray();

        array_unshift($statuses, array('label' => '', 'value' => ''));

        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('inecom_sap')->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('inecom_sap')->__('Status'),
                    'values' => $statuses
                )
            )
        ));
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/show', array('id' => $row->getId()));
    }

}