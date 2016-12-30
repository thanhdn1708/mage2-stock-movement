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

/**
 * @method Transaction setItemId($itemId)
 * @method Transaction setParentType($parentType)
 * @method Transaction setParentId($parentId)
 * @method Transaction setAdjustment($adjustment)
 * @method Transaction setBalance($balance)
 * @method Transaction setExtra($extra)
 * @method mixed getItemId()
 * @method mixed getParentType()
 * @method mixed getParentId()
 * @method mixed getAdjustment()
 * @method mixed getBalance()
 * @method mixed getExtra()
 * @method Transaction setCreatedAt(\string $createdAt)
 * @method string getCreatedAt()
 * @method Transaction setUpdatedAt(\string $updatedAt)
 * @method string getUpdatedAt()
 */
class Transaction extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Cache tag
     * 
     * @var string
     */
    const CACHE_TAG = 'mindarc_inventory_transaction';

    /**
     * Cache tag
     * 
     * @var string
     */
    protected $_cacheTag = 'mindarc_inventory_transaction';

    /**
     * Event prefix
     * 
     * @var string
     */
    protected $_eventPrefix = 'mindarc_inventory_transaction';

    protected $_parent;

    protected $_objectManager;

    protected $_helperBackend;


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_helperBackend = $this->_objectManager->get('Magento\Backend\Helper\Data');
        $this->_init('MindArc\Inventory\Model\ResourceModel\Transaction');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * get entity default values
     *
     * @return array
     */
    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }

   protected function _getParent()
    {
        if (!$this->_parent) {
            $parentId = $this->getParentId();
            $parentType = $this->getParentType();

            if ( $parentId && $parentType ) {
                switch ($parentType) {
                    case 'order':
                        $this->_parent = $this->_objectManager->get('Magento\Sales\Model\Order')->loadByAttribute('quote_id', $parentId);
                        break;
                    case 'order_creditmemo':
                        $this->_parent = $this->_objectManager->get('Magento\Sales\Model\Order\Creditmemo')->load($parentId);
                        break;
                    case 'stock_receipt':
                        $this->_parent = $this->_objectManager->get('MindArc\Inventory\Model\Receipt')->load($parentId);
                        break;                                            
                    default:
                        # code...
                        break;
                }
            }
        }
        return $this->_parent;
    }

    public function hasParent()
    {
        $order      = $this->getOrder();
        $creditmemo = $this->getCreditmemo();
        $receipt    = $this->getReceipt();

        return ( $order && $order->getId() ) ||
            ( $creditmemo && $creditmemo->getId() ) ||
            ( $receipt && $receipt->getId() );
    }

    /**
     * Retrieve the sales order that is related to this transaction
     *
     * @return Mage_Sales_Model_Order|NULL
     */
    public function getOrder()
    {
        $parent = $this->_getParent();

        if ( $parent instanceof \Magento\Sales\Model\Order ) {
            return $parent;
        } 

        return null;
    }

    /**
     * Retrieve the credit memo related to this transaction
     *
     * @return Mage_Sales_Model_Order_Creditmemo|NULL
     */
    public function getCreditmemo()
    {
        $parent = $this->_getParent();

        if ( $parent instanceof \Magento\Sales\Model\Order\Creditmemo ) {
            return $parent;
        }

        return null;
    }

    /**
     * Retrieve the receipt related to this transaction
     *
     * @return Metrik_Inventory_Model_Stock_Receipt|NULL
     */
    public function getReceipt()
    {
        $parent = $this->_getParent();

        if ( $parent instanceof \MindArc\Inventory\Model\Receipt ) {
            return $parent;
        }

        return null;
    }

    /**
     * Retrieve the url for the parent object
     */
    public function getParentUrl()
    {
        $url = '#';

        if ( $order = $this->getOrder() ) {
            $url = $this->_helperBackend->getUrl('sales/order/view',array('order_id' => $order->getId()));
        } else if ( $creditmemo = $this->getCreditmemo() ) {
            $url = $this->_helperBackend->getUrl('sales/creditmemo/view',array('creditmemo_id' => $creditmemo->getId()));
        } else if ( $receipt = $this->getReceipt() ) {
            $url = $this->_helperBackend->getUrl('*/receipt/edit',array('receipt_id' => $receipt->getId()));
        }

        return $url;
    }
}
