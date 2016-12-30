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
namespace MindArc\Inventory\Controller\Adminhtml\Item;

class Save extends \MindArc\Inventory\Controller\Adminhtml\Item
{
    /**
     * Backend session
     * 
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * Stock item factory
     *
     * @var \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory
     */
    protected $_stockItemFactory;

    /**
     * constructor
     * 
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\CatalogInventory\Model\Stock\ItemFactory $itemFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Magento\CatalogInventory\Model\Stock\ItemFactory $itemFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->backendSession = $backendSession;
        $this->_stockItemFactory = $stockItemFactory;
        parent::__construct($itemFactory, $registry, $resultRedirectFactory, $context);
    }

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('item');
        $resultRedirect = $this->resultRedirectFactory->create();
        $redirectBack = $this->getRequest()->getParam('back');
        if ($data) {
            $item = $this->initItem();
            $stockItem = $this->_stockItemFactory->create()->load($data['product_id'],'product_id');
            
            if (isset($data['qty_adjustment']))
            {
                if (substr($data['qty_adjustment'], 0, 1) == '+')
                {
                    $amount = intval(str_replace('+', '', $data['qty_adjustment']));
                    $data['qty'] = $stockItem->getQty() + $amount - $stockItem->getQtyToShip();
                } else if (substr($data['qty_adjustment'], 0, 1) == '-')
                {
                    $amount = intval(str_replace('-', '', $data['qty_adjustment']));
                    $data['qty'] = $stockItem->getQty() - $amount - $stockItem->getQtyToShip();

                } else {
                    $redirectBack = true;
                    $message = __('Incorrect Adjustment Made. Ex: +1 or -1.');
                    $this->messageManager->addError($message);
                }
            }
            else
            {
                $redirectBack = true;
                $message = __('The configurable product can not saved.');
                $this->messageManager->addError($message);                
            }

            $stockItem->setData($data);
            $this->_eventManager->dispatch(
                'mindarc_inventory_item_prepare_save',
                [
                    'item' => $item,
                    'request' => $this->getRequest()
                ]
            );
            try {
                $stockItem->save();
                $this->messageManager->addSuccess(__('The Item has been saved.'));
                $this->backendSession->setMindArcInventoryItemData(false);
                if ($redirectBack) {
                    $resultRedirect->setPath(
                        'mindarc_inventory/*/edit',
                        [
                            'item_id' => $stockItem->getItemId(),
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
                $this->messageManager->addException($e, __('Something went wrong while saving the Item.'));
            }
            $this->_getSession()->setMindArcInventoryItemData($data);
            $resultRedirect->setPath(
                'mindarc_inventory/*/edit',
                [
                    'item_id' => $stockItem->getId(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }
        $resultRedirect->setPath('mindarc_inventory/*/');
        return $resultRedirect;
    }
}
