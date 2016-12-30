<?php

namespace MindArc\Inventory\Observer;

use Magento\Framework\Event\ObserverInterface;

class cataloginventorysavebefore implements ObserverInterface
{    
    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    protected $stockHelper;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\ItemFactory
     */
    protected $stockItemFactory;

    /**
     * @param \Magento\CatalogInventory\Helper\Stock $stockHelper
     */
    public function __construct(\MindArc\Inventory\Helper\Data $stockHelper, \Magento\CatalogInventory\Model\Stock\ItemFactory $stockItemFactory)
    {
        $this->stockHelper = $stockHelper;
        $this->stockItemFactory = $stockItemFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $stockItem = $observer->getEvent()->getItem();
        $stockTransaction = $this->stockHelper->getStockTransaction();
        $beginQty = $this->stockItemFactory->create()->load($stockItem->getId())->getQty();
        $stockTransaction->setItemId($stockItem->getId());
        $stockTransaction->setAdjustment(floatval($beginQty));
    }   
}