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

abstract class Receipt extends \Magento\Backend\App\Action
{
    /**
     * Receipt Factory
     * 
     * @var \MindArc\Inventory\Model\ReceiptFactory
     */
    protected $receiptFactory;

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
     * @param \MindArc\Inventory\Model\ReceiptFactory $receiptFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \MindArc\Inventory\Model\ReceiptFactory $receiptFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->receiptFactory        = $receiptFactory;
        $this->coreRegistry          = $coreRegistry;
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }

    /**
     * Init Receipt
     *
     * @return \MindArc\Inventory\Model\Receipt
     */
    protected function initReceipt()
    {
        $receiptId  = (int) $this->getRequest()->getParam('receipt_id');
        /** @var \MindArc\Inventory\Model\Receipt $receipt */
        $receipt    = $this->receiptFactory->create();
        if ($receiptId) {
            $receipt->load($receiptId);
        }
        $this->coreRegistry->register('mindarc_inventory_receipt', $receipt);
        return $receipt;
    }
}
