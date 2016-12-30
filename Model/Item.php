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

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface as StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface as StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\AbstractExtensibleModel;
/**
 * @method Receipt setIncrementId($incrementId)
 * @method Receipt setName($name)
 * @method Receipt setComment($comment)
 * @method Receipt setReferenceType($referenceType)
 * @method Receipt setReferenceId($referenceId)
 * @method Receipt setExtra($extra)
 * @method mixed getIncrementId()
 * @method mixed getName()
 * @method mixed getComment()
 * @method mixed getReferenceType()
 * @method mixed getReferenceId()
 * @method mixed getExtra()
 * @method Receipt setCreatedAt(\string $createdAt)
 * @method string getCreatedAt()
 * @method Receipt setUpdatedAt(\string $updatedAt)
 * @method string getUpdatedAt()
 */
class Item extends \Magento\CatalogInventory\Model\Stock\Item
{
    protected $_eventPrefix = 'cataloginventory_stock_item';
    protected $_eventObject = 'item';
    protected $_qtyToShip;
    protected $_qtyOnHand;

    public function getQtyToShip()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if ( $this->_qtyToShip === null ) {
            $alias = array(
                'qty_ordered'  => 'qty_ordered',
                'qty_canceled' => 'qty_canceled',
                'qty_refunded' => 'qty_refunded',
                'qty_shipped'  => 'qty_shipped'
            );

            // Calculate qty_to_ship from order_items
            /* @var $items Mage_Sales_Model_Mysql4_Order_Item_Collection */
            $items = $objectManager->get('Magento\Sales\Model\ResourceModel\Order\Item\Collection')
                ->addAttributeToSelect('qty_ordered')
                ->addAttributeToSelect('qty_canceled')
                ->addAttributeToSelect('qty_refunded')
                ->addAttributeToSelect('qty_shipped')
                ->addAttributeToSelect('order_id')
                ->addFieldToFilter('product_id', $this->getProductId())
                ->addExpressionFieldToSelect('qty_to_ship','(IF( {{qty_ordered}} - {{qty_canceled}} - {{qty_refunded}} - {{qty_shipped}} > 0, {{qty_ordered}} - {{qty_canceled}} - {{qty_refunded}} - {{qty_shipped}}, 0))',$alias);

            // Don't count canceled orders
            $canceledQuery = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection()->select()
                ->from('sales_order', 'entity_id')
                ->where('status = ?', 'canceled');
            $items->addFieldToFilter('order_id', array('nin' => $canceledQuery));

            $this->_qtyToShip = array_sum($items->getColumnValues('qty_to_ship'));
        }

        return $this->_qtyToShip;
    }

    public function getData($key='',$index=null)
    {
        switch($key) {
            case 'qty_on_sale':
                return $this->getQty();
                break;
            case 'qty_to_ship':
                return $this->getQtyToShip();
                break;
        }
        return parent::getData($key,$index);
    }

    public function getQtyAtDate($date)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $transaction = $objectManager->get('MindArc\Inventory\Model\ResourceModel\Transaction');
        $select = $this->_getResource()->getReadConnection()->select()
            ->from($transaction->getMainTable(), array("balance"))
            ->where('item_id = ?', $this->getId())
            ->where('created_at <= ?', $date)
            ->order('created_at DESC');
        $qty = $this->_getResource()->getReadConnection()->fetchOne($select);

        // Get the qty from a newer balance
        if ( !is_numeric($qty) ) {
            $select = $this->_getResource()->getReadConnection()->select()
                ->from($transaction->getMainTable(), array("balance","adjustment"))
                ->where('item_id = ?', $this->getId())
                ->where('created_at >= ?', $date)
                ->order('created_at ASC');
            $row = $this->_getResource()->getReadConnection()->fetchRow($select);
            $qty = empty($row) ? null : $row['balance'] - $row['adjustment'];
        }

        // Get the current qty if there are no transactions for this item
        if ( !is_numeric($qty) ) {
            $qty = $this->getQty();
        }

        return $qty;
    }
}
