<?php
/**
 * @category     Mageviper
 * @package      Mageviper_Dpd
 * @subpackage   Helper
 * @author       Mageviper <admin@mageviper.com>
 * @copyright    2019 Mageviper
 * @since        1.0.0
 */
declare(strict_types=1);

namespace Mageviper\Dpd\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 */
class Data extends AbstractHelper
{
    const XML_PATH_TEST_ENV               = 'mageviper_shipping_integration/mageviper_dpd/integration/test';
    const XML_PATH_API_LOGIN              = 'mageviper_shipping_integration/mageviper_dpd/login';
    const XML_PATH_API_PASSWORD           = 'mageviper_shipping_integration/mageviper_dpd/password';
    const XML_PATH_API_MASTER_FID         = 'mageviper_shipping_integration/mageviper_dpd/masterfid';
    const XML_PATH_API_ADDRESS_FID        = 'mageviper_shipping_integration/mageviper_dpd/addressfid';
    const XML_PATH_API_TEST_LOGIN         = 'mageviper_shipping_integration/mageviper_dpd/integration/test_data/login';
    const XML_PATH_API_TEST_PASSWORD      = 'mageviper_shipping_integration/mageviper_dpd/integration/test_data/password';
    const XML_PATH_API_TEST_MASTER_FID    = 'mageviper_shipping_integration/mageviper_dpd/integration/test_data/masterfid';
    const XML_PATH_API_TEST_ADDRESS_FID   = 'mageviper_shipping_integration/mageviper_dpd/integration/test_data/addressfid';
    const XML_CONFIG_ORDER_STATUS_TO_SEND = 'mageviper_shipping_integration/mageviper_dpd/integration/order_status_to_send';
    const XML_PATH_MANIFEST_FILE          = 'mageviper_shipping_integration/mageviper_dpd/dpd_mainfest/file_type';
    const XML_PATH_MANIFEST_LABEL         = 'mageviper_shipping_integration/mageviper_dpd/dpd_mainfest/label_type';
    const XML_PATH_CRON_QUEUE             = 'mageviper_shipping_integration/dpd_cron/prepare';
    const XML_PATH_CRON_MANIFEST          = 'mageviper_shipping_integration/dpd_cron/queue';
    const XML_PATH_CARRIER_ORDER_STATUS   = 'carriers/dpd/order_status';
    const XML_PATH_CARRIER_TITLE          = 'carriers/dpd/title';
    const XML_PATH_CARRIER_MAX_WEIGHT     = 'carriers/dpd/max_weight';

    /**
     * @return bool
     */
    public function isTestMode(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_TEST_ENV);
    }

    /**
     * @return string|null
     */
    public function getLogin(): ?string
    {
        if (!$this->isTestMode()) {
            return $this->scopeConfig->getValue(self::XML_PATH_API_LOGIN);
        }

        return $this->scopeConfig->getValue(self::XML_PATH_API_TEST_LOGIN);
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        if (!$this->isTestMode()) {
            return $this->scopeConfig->getValue(self::XML_PATH_API_PASSWORD);
        }

        return $this->scopeConfig->getValue(self::XML_PATH_API_TEST_PASSWORD);
    }

    /**
     * @return int|null
     */
    public function getManifestFid(): ?int
    {
        if (!$this->isTestMode()) {
            return (int)$this->scopeConfig->getValue(self::XML_PATH_API_MASTER_FID);
        }

        return (int)$this->scopeConfig->getValue(self::XML_PATH_API_TEST_MASTER_FID);
    }

    /**
     * @return int|null
     */
    public function getAddressFid(): ?int
    {
        if (!$this->isTestMode()) {
            return (int)$this->scopeConfig->getValue(self::XML_PATH_API_ADDRESS_FID);
        }

        return (int)$this->scopeConfig->getValue(self::XML_PATH_API_TEST_ADDRESS_FID);
    }

    /**
     * @return string|null
     */
    public function getOrderStatus(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CARRIER_ORDER_STATUS);
    }

    /**
     * @return string|null
     */
    public function getFileType(): ?string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_MANIFEST_FILE);
    }

    /**
     * @return string|null
     */
    public function getLabelType(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MANIFEST_LABEL);
    }

    /**
     * @return bool
     */
    public function getCronQueueStatus(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_CRON_QUEUE);
    }

    /**
     * @return bool
     */
    public function getCronManifestStatus(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_CRON_MANIFEST);
    }

    /**
     * @return float|null
     */
    public function getMaxParcelWeight()
    {
        return (float)$this->scopeConfig->getValue(self::XML_PATH_CARRIER_MAX_WEIGHT);
    }

    public function getCarrierTitle()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CARRIER_TITLE);
    }

    /**
     * @param string $value
     * @return string
     */
    public function getShopData(string $value): string
    {
        $data = [
            'fid'         => $this->getAddressFid(),
            'name'        => $this->scopeConfig->getValue('general/store_information/name'),
            'company'     => $this->scopeConfig->getValue('general/store_information/name'),
            'address'     => $this->scopeConfig->getValue('general/store_information/street_line1'),
            'city'        => $this->scopeConfig->getValue('general/store_information/city'),
            'postalCode'  => $this->sanitizePostCode($this->scopeConfig->getValue('general/store_information/postcode')),
            'countryCode' => $this->scopeConfig->getValue('general/store_information/country_id'),
            'email'       => $this->scopeConfig->getValue('trans_email/ident_general/email'),
            'phone'       => $this->sanitizePhoneNumber($this->scopeConfig->getValue('general/store_information/phone')),
        ];

        return (string)$data[$value];
    }

    public function getPickupData(): array
    {
        return ['fid' => $this->getAddressFid()];
    }

    /**
     * @param $code
     * @return string
     */
    public function sanitizePostCode($code): string
    {
        return trim(str_replace('-', '', $code));
    }

    /**
     * @param $number
     * @return string
     */
    public function sanitizePhoneNumber($number): string
    {
        return trim(preg_replace('/[^0-9]/', '', $number));
    }

    /**
     * @return null|array
     */
    public function getOrderStatusesToSend()
    {
        $configValue = $this->scopeConfig->getValue(self::XML_CONFIG_ORDER_STATUS_TO_SEND);

        if (is_string($configValue)) {
            $configValue = explode(',', $configValue);
        }

        return $configValue;
    }
}
