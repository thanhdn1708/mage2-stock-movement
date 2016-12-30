<?php
/**
 * MindArc_Inventory extension
 *                     NOTICE OF LICENSE
 * 
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 * 
 *                     @category  MindArc
 *                     @package   MindArc_Inventory
 *                     @copyright Copyright (c) 2016
 *                     @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace MindArc\Inventory\Block\Adminhtml\Item\Edit;

/**
 * @method Tabs setTitle(\string $title)
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('item_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Item Information'));
    }

    protected function _beforeToHtml()
    {
        // $this->addTab('information', array(
        //     'label'     => Mage::helper('adminhtml')->__('Information'),
        //     'title'     => Mage::helper('adminhtml')->__('Information'),
        //     'content'   => $this->getLayout()->createBlock('metrik_inventory/adminhtml_inventory_edit_tab_information')->toHtml(),
        // ));

        if ($this->canViewTransactions())
        $this->addTab('transactions', array(
            'label'     => __('Transactions'),
            'title'     => __('Transactions'),
            'url'       => $this->getUrl('mindarc_inventory/item/grid', array('_current'=>true)),
            'class'     => 'ajax'
        ));


        $activeTab = str_replace("{$this->getId()}_",'',$this->getRequest()->getParam('tab'));
        if ($activeTab) $this->setActiveTab($activeTab);

        return parent::_beforeToHtml();
    }

    public function canViewTransactions()
    {
        return $this->_authorization->isAllowed('MindArc_Inventory::item_view');
    }
}
