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
use Mageviper\Dpd\Api\Data\PackageInterface;

/**
 * Class Packages
 */
class Package extends AbstractExtensibleModel implements PackageInterface
{

    /**
     * @return int|null|string
     */
    public function getId()
    {
        return (int)$this->getData(static::ID);
    }

    /**
     * @return int|null
     */
    public function getOrderId(): ?int
    {
        return $this->getData(static::ORDER_ID);
    }

    /**
     * @param int $orderId
     * @return PackageInterface
     */
    public function setOrderId(int $orderId): PackageInterface
    {
        return $this->setData(static::ORDER_ID, $orderId);
    }

    /**
     * @return int|null
     */
    public function getManifestId(): ?int
    {
        return $this->getData(static::MANIFEST_ID);
    }

    /**
     * @param int $manifestId
     * @return PackageInterface
     */
    public function setManifestId(int $manifestId): PackageInterface
    {
        return $this->setData(static::MANIFEST_ID, $manifestId);
    }

    /**
     * @return int|null
     */
    public function getPackageId(): ?int
    {
        return $this->getData(static::PACKAGE_ID);
    }

    /**
     * @param $packageId
     * @return PackageInterface
     */
    public function setPackageId($packageId): PackageInterface
    {
        return $this->setData(static::PACKAGE_ID, (int)$packageId);
    }

    /**
     * @return string|null
     */
    public function getTrackingNumber(): ?string
    {
        return $this->getData(static::TRACKING_NUMBER);
    }

    /**
     * @param string|null $waybill
     * @return PackageInterface
     */
    public function setTrackingNumber($waybill): PackageInterface
    {
        return $this->setData(static::TRACKING_NUMBER, $waybill);
    }
}
