<?php

namespace MindArc\Inventory\Observer;

use Magento\Framework\Event\ObserverInterface;

class Cataloginventorysaveafter implements ObserverInterface
{    
    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    protected $stockHelper;

    protected $_authSession;

    /**
     * @param \Magento\CatalogInventory\Helper\Stock $stockHelper
     */
    public function __construct(\Magento\Backend\Model\Auth\Session $authSession, \MindArc\Inventory\Helper\Data $stockHelper)
    {
        $this->stockHelper = $stockHelper;
        $this->_authSession = $authSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $stockItem = $observer->getEvent()->getItem();
        $stockTransaction = $this->stockHelper->getStockTransaction();
        

        $adjustment = $stockItem->getQty() - $stockTransaction->getAdjustment();
        if (!$stockTransaction->getParentType())
        {
            $stockTransaction->setParentType('manual');
        }
        if ( $stockTransaction->getItemId() === null ) {
            $stockTransaction->setItemId($stockItem->getId());
        } else if ( $stockTransaction->getItemId() != $stockItem->getId() ) {
            $this->stockHelper->clearStockTransaction();
            return $this;
        }

        if ( $adjustment != 0 ) {
            $stockTransaction->setAdjustment($adjustment);
            $stockTransaction->setBalance($stockItem->getQty());
            if ($stockItem['comment']) {
                $stockTransaction->setExtra($this->getAdminUsername() . '|' .$stockItem['comment']);
            } else {
                $stockTransaction->setExtra($this->getAdminUsername());
            }
            $stockTransaction->save();
            $this->stockHelper->clearStockTransaction();
        }
    }   

    public function getAdminUsername()
    {
        $user = $this->_authSession->getUser();
        if ($user)
            return $user->getUsername();
        return null;
    }
}