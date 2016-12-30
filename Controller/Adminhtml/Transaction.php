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
namespace MindArc\Inventory\Controller\Adminhtml;

abstract class Transaction extends \Magento\Backend\App\Action
{
    /**
     * Transaction Factory
     * 
     * @var \MindArc\Inventory\Model\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * Core registry
     * 
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Result redirect factory
     * 
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * constructor
     * 
     * @param \MindArc\Inventory\Model\TransactionFactory $transactionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \MindArc\Inventory\Model\TransactionFactory $transactionFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->transactionFactory    = $transactionFactory;
        $this->coreRegistry          = $coreRegistry;
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }

    /**
     * Init Transaction
     *
     * @return \MindArc\Inventory\Model\Transaction
     */
    protected function initTransaction()
    {
        $transactionId  = (int) $this->getRequest()->getParam('transaction_id');
        /** @var \MindArc\Inventory\Model\Transaction $transaction */
        $transaction    = $this->transactionFactory->create();
        if ($transactionId) {
            $transaction->load($transactionId);
        }
        $this->coreRegistry->register('mindarc_inventory_transaction', $transaction);
        return $transaction;
    }
}
