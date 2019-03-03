<?php
/**
 * @category     Mageviper
 * @package      Mageviper_Dpd
 * @subpackage   Api
 * @author       Mageviper <admin@mageviper.com>
 * @copyright    2019 Mageviper
 * @since        1.0.0
 */

namespace Mageviper\Dpd\Api\Data;

use Magento\Framework\Api\CustomAttributesDataInterface;

/**
 * Interface PackageInterface
 */
interface PackageInterface extends CustomAttributesDataInterface
{
    const ID              = 'id';
    const ORDER_ID        = 'order_id';
    const MANIFEST_ID     = 'manifest_id';
    const PACKAGE_ID      = 'package_id';
    const TRACKING_NUMBER = 'tracking_number';


    /**
     * @return int|null|string
     */
    public function getId();

    /**
     * @return int|null
     */
    public function getOrderId(): ?int;

    /**
     * @param int $orderId
     * @return PackageInterface
     */
    public function setOrderId(int $orderId): PackageInterface;

    /**
     * @return int|null
     */
    public function getManifestId(): ?int;

    /**
     * @param int $manifestId
     * @return PackageInterface
     */
    public function setManifestId(int $manifestId): PackageInterface;

    /**
     * @return int|null
     */
    public function getPackageId(): ?int;

    /**
     * @param int $packageId
     * @return PackageInterface
     */
    public function setPackageId($packageId): PackageInterface;

    /**
     * @return string|null
     */
    public function getTrackingNumber(): ?string;

    /**
     * @param string|null $waybill
     * @return PackageInterface
     */
    public function setTrackingNumber($waybill): PackageInterface;
}
