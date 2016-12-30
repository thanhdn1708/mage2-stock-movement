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
namespace MindArc\Inventory\Block\Adminhtml\Transaction\Grid\Renderer;

class Actions extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
   /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $_urlHelper;

    protected $_authSession;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Backend\Model\Auth\Session $authSession, 
        array $data = []
    ) {
        $this->_urlHelper = $urlHelper;
        $this->_authSession = $authSession;
        parent::__construct($context, $data);
    }

    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $actions = array();
        if ( $row->hasParent() ) {
            if (!$row->getParentType()) {
                    echo 'Return by User: ' . $row['extra'];
            } else {
                $actions[] = array(
                    '@' =>  array('href' => trim($row->getParentUrl(),'/'), 'target' => '_blank'),
                    '#' =>  __('View '.$this->getParentType($row->getParentType()))
                );
            }
        } else {
            if ($row['extra']){
                $strs = explode('|', $row['extra']);

                echo '<b>User</b>: ' . $strs[0];
                if (count($strs) > 1){
                   echo '</br>';
                    echo '<b>Reason</b>: ' . $strs[1];
                }
            } else {
                echo 'N/A';
            }
        }

        return $this->_actionsToHtml($actions);
    }

    protected function _actionsToHtml(array $actions)
    {
        $html = array();
        $attributesObject = new \Magento\Framework\DataObject();

        foreach ($actions as $action) {
            $attributesObject->setData($action['@']);
            $html[] = '<a ' . $attributesObject->serialize() . '>' . $action['#'] . '</a>';
        }
        return  implode('<br />',$html);
    }

    protected function _getEscapedValue($value)
    {
        return addcslashes(htmlspecialchars($value),'\\\'');
    }

    public function getParentType($type)
    {
        $_types = [
            'manual'                 => __('Manual'),
            'order'                  => __('Order'),
            'order_creditmemo' => __('Credit Memo'),
            'stock_receipt' => __('Receipt')
        ];

        return $_types[$type];
    }
}
