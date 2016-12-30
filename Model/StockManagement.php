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
namespace MindArc\Inventory\Model;

use \Magento\Checkout\Model\Session as CheckoutSession;

class StockManagement
{
    /**
     * Subtract product qtys from stock.
     * Return array of items that require full save
     *
     * @param string[] $items
     * @param int $websiteId
     * @return StockItemInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */

    protected $_registry;

    protected $_items;

    protected $_itemsCredimemo;

    protected $_checkoutSession;

    protected $_stockConfiguration;

    protected $_stockResource;

    protected $_stockRegistry;

    protected $_transactionFactory;

    protected $_backendSession;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\CatalogInventory\Model\ResourceModel\Stock $stockResource,
        \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface $stockRegistry,
        \MindArc\Inventory\Model\TransactionFactory $transactionFactory,
        \Magento\Backend\Model\Session\Quote $backendSession,
        CheckoutSession $checkoutSession
    )
    {
        $this->_checkoutSession = $checkoutSession;
        $this->_registry = $registry;
        $this->_stockConfiguration = $stockConfiguration;
        $this->_stockResource = $stockResource;
        $this->_stockRegistry = $stockRegistry;
        $this->_transactionFactory = $transactionFactory;
        $this->_backendSession = $backendSession;
    }

    public function beforeRegisterProductsSale($subject, $items)
    {
        $this->_items = $items;
    }

    public function afterRegisterProductsSale($subject, $result)
    {
        $websiteId = $this->_stockConfiguration->getDefaultScopeId();
        $items = $this->_items;
        $resource = $this->_stockResource;
        $resource->beginTransaction();
        $lockedItems = $resource->lockProductsStock(array_keys($items), $websiteId);
        // added to transaction
        foreach ($lockedItems as $lockedItemRecord) {
            $productId = $lockedItemRecord['product_id'];
            /** @var StockItemInterface $stockItem */
            $orderedQty = $items[$productId];
            $stockItem = $this->_stockRegistry->getStockItem($productId, $websiteId);
            $stockTransaction = $this->_transactionFactory->create();
            $stockTransaction->setItemId($stockItem->getId())
                            ->setBalance($stockItem->getQty())
                            ->setQty($stockItem->getQty());
            $stockTransaction->setAdjustment(0-$orderedQty);
            if ($quoteId = $this->_backendSession->getQuoteId()) {
                $stockTransaction->setParentType('order');
                $stockTransaction->setParentId($quoteId);
            } elseif ($quoteId = $this->_checkoutSession->getQuoteId()) {
                $stockTransaction->setParentType('order');
                $stockTransaction->setParentId($quoteId);
            } elseif ($quote = $this->_registry->registry('current_order')) {
                $stockTransaction->setParentType('order');
                $stockTransaction->setParentId($quote->getId());
            }
            $stockTransaction->save();
        }
        $resource->commit();

        return $result;
    }

    /**
     * @param string[] $items
     * @param int $websiteId
     * @return bool
     */
    public function beforeRevertProductsSale($subject, $items)
    {
        $this->_itemsCredimemo = $items;
    }

    /**
     * @param string[] $items
     * @param int $websiteId
     * @return bool
     */
    public function afterRevertProductsSale($subject, $result)
    {
        $websiteId = $this->_stockConfiguration->getDefaultScopeId();
        $items = $this->_itemsCredimemo;

        $resource = $this->_stockResource;
        $resource->beginTransaction();
        $lockedItems = $resource->lockProductsStock(array_keys($items), $websiteId);

        // added to transaction
        foreach ($lockedItems as $lockedItemRecord) {
            $productId = $lockedItemRecord['product_id'];
            /** @var StockItemInterface $stockItem */
            $orderedQty = $items[$productId];
            $stockItem = $this->_stockRegistry->getStockItem($productId, $websiteId);
            $stockTransaction = $this->_transactionFactory->create();
            $stockTransaction->setItemId($stockItem->getId())
                            ->setBalance($stockItem->getQty());
                            // ->setQty($stockItem->getQty());
            $stockTransaction->setAdjustment($orderedQty);
            $deleted = false; // Tracks if our transaction has been deleted
            if ($creditmemo = $this->_registry->registry('current_creditmemo')) {
                $stockTransaction->setParentType('order_creditmemo');
                $stockTransaction->setParentId($creditmemo->getId());
            } 
            if (!$deleted) $stockTransaction->save();
        }        
        $resource->commit();

        return $result;
    }
}
