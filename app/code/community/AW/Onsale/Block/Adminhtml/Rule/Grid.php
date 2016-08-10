<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Onsale
 * @version    2.5.4
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Onsale_Block_Adminhtml_Rule_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('onsale_rule_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        /** @var $collection AW_Onsale_Model_Mysql4_Rule_Collection */
        $collection = Mage::getModel('onsale/rule')
            ->getResourceCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'rule_id', array(
                'header' => $this->__('ID'),
                'align'  => 'right',
                'width'  => '50px',
                'index'  => 'rule_id',
            )
        );

        $this->addColumn(
            'name', array(
                'header' => $this->__('Rule Name'),
                'align'  => 'left',
                'index'  => 'name',
            )
        );

        $this->addColumn(
            'from_date', array(
                'header'  => $this->__('Date Start'),
                'align'   => 'left',
                'width'   => '120px',
                'type'    => 'date',
                'default' => '--',
                'index'   => 'from_date',
            )
        );

        $this->addColumn(
            'to_date', array(
                'header'  => $this->__('Date Expire'),
                'align'   => 'left',
                'width'   => '120px',
                'type'    => 'date',
                'default' => '--',
                'index'   => 'to_date',
            )
        );

        $this->addColumn(
            'is_active', array(
                'header'  => $this->__('Status'),
                'align'   => 'left',
                'width'   => '80px',
                'index'   => 'is_active',
                'type'    => 'options',
                'options' => array(
                    1 => $this->__('Active'),
                    0 => $this->__('Inactive')
                ),
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_ids',
                array(
                   'header'                    => $this->__('Store View'),
                   'index'                     => 'store_ids',
                   'type'                      => 'store',
                   'width'                     => '100px',
                   'store_all'                 => true,
                   'store_view'                => true,
                   'sortable'                  => false,
                   'renderer'                  => 'onsale/adminhtml_grid_renderer_multiStores',
                   'filter_condition_callback' => array($this, 'filterStore'),
                )
            );
        }

        $this->addColumn('sort_order',
            array(
                'header' => $this->__('Priority'),
                'align'  => 'right',
                'index'  => 'sort_order',
                'width'  => 100,
            )
        );
        $this->addColumn('action',
            array(
                'header'    =>  $this->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption' => $this->__('Edit'),
                        'url'     => array('base'=> '*/*/edit'),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            )
        );
        parent::_prepareColumns();
        return $this;
    }

    /**
     * Retrieve row click URL
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getRuleId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('rule');
        $this->getMassactionBlock()->addItem('status', array(
            'label'=> $this->__('Change status'),
            'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $this->__('Status'),
                    'values' => array(
                        1 => $this->__('Active'),
                        0 => $this->__('Inactive')
                    )
                )
            )
        ));
        return $this;
    }

    protected function filterStore($collection, $column)
    {
        $collection->addStoreFilter($column->getFilter()->getValue());
        return $this;
    }
}
