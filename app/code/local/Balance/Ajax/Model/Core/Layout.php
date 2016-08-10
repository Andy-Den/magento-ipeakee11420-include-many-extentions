<?php

class Balance_Ajax_Model_Core_Layout
    extends Mage_Core_Model_Layout
{
    const AJAX_BLOCK_REGISTER_KEY = 'ajax/block/registery/key';

    /**
     * Add block object to layout based on xml node data
     *
     * @param Varien_Simplexml_Element $node
     * @param Varien_Simplexml_Element $parent
     *
     * @return Mage_Core_Model_Layout
     */
    protected function _generateBlock($node, $parent)
    {

        $request = Mage::app()->getRequest();

        if (Mage::helper('ajax')->shouldSkip()) {
            $ajax = false;
        } else {
            $ajax = ((string)$node['ajax'] == 'true') ? true : false;
        }

        //@todo check block id
        if ($ajax) {
            //register old type and template
            $backup = array(
                'type'       => (string)$node['type'],
                'template'   => (string)$node['template'],
                'name'       => (string)$node['name'],
                'id'         => (string)$node['name'],
                'params'     => $request->getParams(),
                'controller' => $request->getControllerName(),
                'module'     => $request->getModuleName(),
                'action'     => $request->getActionName(),
            );

        }

        parent::_generateBlock($node, $parent);

        if ($ajax) {
            $block = $this->getBlock((string)$node['name']);
            //Moved template replacing into core_block_abstract_to_html_after event process
            //$block->setTemplate('ajax/block.phtml');
            $block->setData('backup', $backup);
            $block->setData('ajax', 1);
            $block->setData('ajax_sort', intval($node['ajax_sort']));
            $block->setData('nodiv', intval($node['nodiv']));
        }
    }

    public function generateOriginBlock()
    {
        parent::_generateBlock();
    }
}