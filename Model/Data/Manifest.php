<?php
/**
 * @category     Mageviper
 * @package      Mageviper_Dpd
 * @subpackage   Model
 * @author       Mageviper <admin@mageviper.com>
 * @copyright    2019 Mageviper
 * @since        1.0.0
 */
declare(strict_types=1);

namespace Mageviper\Dpd\Model\Data;

use Magento\Framework\Model\AbstractExtensibleModel;
use Mageviper\Dpd\Api\Data\ManifestInterface;

/**
 * Class Manifest
 */
class Manifest extends AbstractExtensibleModel implements ManifestInterface
{

    /**
     * @return int|null|string
     */
    public function getId()
    {
        return $this->getData(static::ID);
    }

    /**
     * @return string
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(static::CREATED_AT);
    }

    /**
     * @param $timestamp
     * @return ManifestInterface
     */
    public function setCreatedAt($timestamp): ManifestInterface
    {
        return $this->setData(static::CREATED_AT, $timestamp);
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): ?string
    {
        return (string)$this->getData(static::UPDATED_AT);
    }

    /**
     * @param $timestamp
     * @return ManifestInterface
     */
    public function setUpdatedAt($timestamp): ManifestInterface
    {
        return $this->setData(static::UPDATED_AT, $timestamp);
    }

    /**
     * @return string|null
     */
    public function getSendAt(): ?string
    {
        return (string)$this->getData(static::SEND_AT);
    }

    /**
     * @param $timestamp
     * @return ManifestInterface
     */
    public function setSendAt($timestamp): ManifestInterface
    {
        return $this->setData(static::SEND_AT, $timestamp);
    }

    /**
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return (int)$this->getData(static::STATUS);
    }

    /**
     * @param int $status
     * @return ManifestInterface
     */
    public function setStatus(int $status): ManifestInterface
    {
        return $this->setData(static::STATUS, $status);
    }

}
