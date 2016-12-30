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
namespace MindArc\Inventory\Controller\Adminhtml\Receipt;

class Save extends \MindArc\Inventory\Controller\Adminhtml\Receipt
{
    /**
     * Backend session
     * 
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     *
     * @var Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * Stock item factory
     *
     * @var \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory
     */
    protected $_stockItemFactory;

    protected $stockHelper;

    protected $jsHelper;

    /**
     * constructor
     * 
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \MindArc\Inventory\Model\ReceiptFactory $receiptFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \MindArc\Inventory\Model\ReceiptFactory $receiptFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \MindArc\Inventory\Helper\Data $stockHelper,
        \Magento\Backend\Helper\Js $jsHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->backendSession = $backendSession;
        $this->stockHelper = $stockHelper;
        $this->jsHelper = $jsHelper;
        $this->_orderFactory = $orderFactory;
        $this->_stockItemFactory = $stockItemFactory;
        parent::__construct($receiptFactory, $registry, $resultRedirectFactory, $context);
    }

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('receipt');
        $dataProducts = $this->getRequest()->getPost('products');
        $resultRedirect = $this->resultRedirectFactory->create();
        $isEdit = false;
        $redirectBack = false;
        if ($data) {
            $receipt = $this->initReceipt();
            $receipt->setData($data);
            $this->_eventManager->dispatch(
                'mindarc_inventory_receipt_prepare_save',
                [
                    'receipt' => $receipt,
                    'request' => $this->getRequest()
                ]
            );
                            

            try {

                if ( !$isEdit && ( empty($dataProducts) ||
                        ! ($products = $this->jsHelper->decodeGridSerializedInput($dataProducts)))) {
                    $redirectBack = true;
                    $message = __('You must select at least one product');
                    $this->messageManager->addError($message);
                    $this->_getSession()->setMindArcInventoryReceiptData($data);
                } else {
                    $reference_id = $data['reference_id'];
                    $order = $this->_orderFactory->create()->loadByIncrementId($reference_id);

                    if($data['reference_type'] == 'returned_item' && !$order->getId()){
                        $redirectBack = true;

                        $message = __('Each "Returned Item" must have a valid order number in the "Reference Number" field. No order found for the order number entered');
                        $this->messageManager->addError($message);
                        $this->_getSession()->setMindArcInventoryReceiptData($data);
                    } else {

                        // Check that there is at least something with a quantity
                        if ( $isEdit ) {
                            $proceed = true;
                        } else {
                            $proceed = false;
                            foreach ( $products as $product ) {
                                if ( $product['qty_received'] != 0 ) {
                                    $proceed = true;
                                }
                            }
                        }

                        if ( $proceed ) {
                            if ( $isEdit ) $receipt->load($receiptId);

                            if ( !empty($data['shipment']) ) {
                                $receipt->addExtraInfo($data['shipment']);
                            }

                            if ( !empty($data['production']) ) {
                                $receipt->addExtraInfo($data['production']);
                            }

                            //if returned_item, save the order_number for the increment_id entered
                            if ( $data['reference_type'] == 'returned_item' ) {
                                $data['reference_id'] = $order->getId();
                            }

                            $receipt->addData($data);
                            $receipt->save();

                            // Only process products for a new receipt
                            if ( !$isEdit ) {
                                foreach ( $products as $productId => $info ) {
                                    if ( $info['qty_received'] == 0 ) continue;
                                    $item = $this->_stockItemFactory->create()->load($productId,'product_id');
                                    if ( $item->getId() ) {
                                        $this->stockHelper->getStockTransaction()
                                            ->setParentType('stock_receipt')
                                            ->setParentId($receipt->getId());

                                        $item->setQty($item->getQty() + $info['qty_received'])->save();
                                    }
                                }

                                // Update the receipt id (for redirects)
                                $receiptId = $receipt->getId();
                            }

                            $this->messageManager->addSuccess(__('The Receipt has been saved.'));
                        } else {
                            $redirectBack = true;
                            $message = __('You must select at least one product');
                            $this->messageManager->addError($message);
                            $this->_getSession()->setMindArcInventoryReceiptData($data);
                        }
                    }
                }
                $this->backendSession->setMindArcInventoryReceiptData(false);
                if ($this->getRequest()->getParam('back') || $redirectBack) {
                    $resultRedirect->setPath(
                        'mindarc_inventory/*/edit',
                        [
                            'receipt_id' => $receipt->getId(),
                            '_current' => true
                        ]
                    );
                    return $resultRedirect;
                }
                $resultRedirect->setPath('mindarc_inventory/*/');
                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addException($e, __('Something went wrong while saving the Receipt.'));
            }
            $this->_getSession()->setMindArcInventoryReceiptData($data);
            $resultRedirect->setPath(
                'mindarc_inventory/*/edit',
                [
                    'receipt_id' => $receipt->getId(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }
        $resultRedirect->setPath('mindarc_inventory/*/');
        return $resultRedirect;
    }
}
