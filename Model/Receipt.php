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
class Receipt extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Cache tag
     * 
     * @var string
     */
    const CACHE_TAG = 'mindarc_inventory_receipt';

    /**
     * Cache tag
     * 
     * @var string
     */
    protected $_cacheTag = 'mindarc_inventory_receipt';

    /**
     * Event prefix
     * 
     * @var string
     */
    protected $_eventPrefix = 'mindarc_inventory_receipt';

    protected $_extraInfo;


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MindArc\Inventory\Model\ResourceModel\Receipt');
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

   public function getExtraInfo($key = '', $index = null )
    {
        if ( !$this->_extraInfo ) {
            $extra = $this->getExtra();
            $extra = empty($extra) ? array() : unserialize($extra);
            $this->_extraInfo = new \Magento\Framework\DataObject($extra);
        }

        return $this->_extraInfo->getData($key, $index);
    }

    public function addExtraInfo(array $extra)
    {
        $this->getExtraInfo();

        $this->_extraInfo->addData($extra);
    }

    public function beforeSave()
    {
        // if ( !$this->getIncrementId() ) {
        //    $this->setIncrementId();
        // }
        if ( $this->_extraInfo ) {
            $this->setExtra(serialize($this->getExtraInfo()));
        }

        return parent::beforeSave();
    }
}
