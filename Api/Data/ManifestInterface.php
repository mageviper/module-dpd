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
 * Interface ManifestInterface
 */
interface ManifestInterface extends CustomAttributesDataInterface
{
    const ID         = 'id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const SEND_AT    = 'send_at';
    const STATUS     = 'status';

    /**
     * @return int|string|null
     */
    public function getId();

    /**
     * @return string
     */
    public function getCreatedAt(): ?string;

    /**
     * @param $timestamp
     * @return ManifestInterface
     */
    public function setCreatedAt($timestamp): ManifestInterface;

    /**
     * @return string
     */
    public function getUpdatedAt(): ?string;

    /**
     * @param $timestamp
     * @return ManifestInterface
     */
    public function setUpdatedAt($timestamp): ManifestInterface;

    /**
     * @return string|null
     */
    public function getSendAt(): ?string;

    /**
     * @param $timestamp
     * @return ManifestInterface
     */
    public function setSendAt($timestamp): ManifestInterface;

    /**
     * @return int|null
     */
    public function getStatus(): ?int;

    /**
     * @param int $status
     * @return ManifestInterface
     */
    public function setStatus(int $status): ManifestInterface;
}
