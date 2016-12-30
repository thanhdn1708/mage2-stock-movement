<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MindArc\Inventory\Helper;

/**
 * Catalog Inventory default helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	const REGISTRY_KEY_TRANSACTION = 'current_stock_transaction';
    /**
     * Retrieves the current stock transaction from the registry, or creates a new one.
     *
     * @return Metrik_Inventory_Model_Stock_Transaction
     */
    protected $_registry;

    /**
     * Transaction Factory
     * 
     * @var \MindArc\Inventory\Model\TransactionFactory
     */
    protected $transactionFactory;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \MindArc\Inventory\Model\TransactionFactory $transactionFactory
    )
    {
    	$this->transactionFactory    = $transactionFactory;
        $this->_registry = $registry;
    }

    public function getStockTransaction($stockItem = null) {
        $transaction = $this->_registry->registry(self::REGISTRY_KEY_TRANSACTION);
        if ( !$transaction ) {
            $transaction    = $this->transactionFactory->create();
            $this->_registry->register(self::REGISTRY_KEY_TRANSACTION,$transaction);
        }

        return $transaction;
    }

    /**
     * Clears the stock transaction from the registry
     *
     * @return Metrik_Inventory_Model_Stock_Transaction
     */
    public function clearStockTransaction() {
        $this->_registry->unregister(self::REGISTRY_KEY_TRANSACTION);
    }
}
