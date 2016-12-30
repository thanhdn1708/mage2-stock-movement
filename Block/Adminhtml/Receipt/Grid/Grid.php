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
namespace MindArc\Inventory\Block\Adminhtml\Receipt\Grid;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
  /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_objectManager = $objectManager;
        $this->_authSession = $authSession;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }


    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('real_order_id', array(
            'header'=> __('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
        ));

        $this->addColumn('created_at', array(
            'header' => __('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));

        $this->addColumn('base_grand_total', array(
            'header' => __('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
            'header' => __('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        //if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
        //   return $this->getUrl('*/sales_order/view', array('order_id' => $row->getId()));
       //   }
        //return $this->getUrl('*/inventory_receipt/view', array('receipt_id' => $row->getId()));
        return $row->getIncrementId();
        //return false;
    }

    public function getRowClickCallback()
    {
        // TODO preload order items into the chooser, and swith to that tab
        return "function openGridRow(grid, event){
            var element = Event.findElement(event, 'tr');
            if(['a', 'input', 'select', 'option'].indexOf(Event.element(event).tagName.toLowerCase())!=-1) {
                return;
            }
            if(element.title){
                opener.document.getElementById('receipt_reference_id').value = element.title;
                if (window.opener) {
                    window.opener.focus();
                }
                window.close();
            }
        }";
    }

    public function getGridUrl()
    {
        return $this->getUrl('mindarc_inventory/receipt/select', array('_current'=>true));
    }
}
