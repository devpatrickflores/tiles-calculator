<?php

namespace PF\TilesCalculator\Model\Attribute\Backend;

/**
 * Class YourAttribute
 */
class Coverage extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{

    /**
     * @var int $minimumValueLength
     */
    protected $minimumValueLength = 0;

    /**
     * @param \Magento\Framework\DataObject $object
     *
     * @return $this
     */
    public function afterLoad($object)
    {
        // your after load logic

        return parent::afterLoad($object);
    }

    /**
     * @param \Magento\Framework\DataObject $object
     *
     * @return $this
     */
    public function beforeSave($object)
    {
        $this->validateLength($object);

        return parent::beforeSave($object);
    }

    /**
     * Validate length
     *
     * @param \Magento\Framework\DataObject $object
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateLength($object)
    {
        /** @var string $attributeCode */
        $attributeCode = $this->getAttribute()->getAttributeCode();
        /** @var int $value */
        $value = (int)$object->getData($attributeCode);
        /** @var int $minimumValueLength */
        $minimumValueLength = $this->getMinimumValueLength();

        if ($this->getAttribute()->getIsRequired() && $value <= $minimumValueLength) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The value of attribute "%1" must be greater than %2', $attributeCode, $minimumValueLength)
            );
        }

        return true;
    }

    /**
     * Get minimum attribute value length
     * 
     * @return int
     */
    public function getMinimumValueLength()
    {
        return $this->minimumValueLength;
    }
}