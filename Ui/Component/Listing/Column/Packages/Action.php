<?php
/**
 * @category     Mageviper
 * @package      Mageviper_Dpd
 * @subpackage   Ui
 * @author       Mageviper <admin@mageviper.com>
 * @copyright    2019 Mageviper
 * @since        1.0.0
 */
declare(strict_types=1);

namespace Mageviper\Dpd\Ui\Component\Listing\Column\Packages;

use Magento\Ui\Component\Listing\Columns\Column;
use Mageviper\Dpd\Model\Manifest;

/**
 * Class Action
 */
class Action extends Column
{
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')]['view']              = [
                    'href'   => $this->context->getUrl(
                        'sales/order/view',
                        [
                            'order_id' => $item['order_id']
                        ]
                    ),
                    'label'  => __('View Order'),
                    'hidden' => true
                ];
                $item[$this->getData('name')]['renew']             = [
                    'href'   => $this->context->getUrl(
                        'mageviper_dpd/package/action_renew',
                        [
                            'order_id' => $item['order_id']
                        ]
                    ),
                    'label'  => __('Renew parcel'),
                    'hidden' => false
                ];
                $item[$this->getData('name')]['download_label']    = [
                    'href'   => $this->context->getUrl(
                        'mageviper_dpd/manifest/download_label',
                        [
                            'package_id'  => $item['package_id'],
                            'flag' => 'simple'

                        ]
                    ),
                    'label'  => __('Download Label'),
                    'hidden' => empty($item['package_id']) ? true : false
                ];
                $item[$this->getData('name')]['download_protocol'] = [
                    'href'   => $this->context->getUrl(
                        'mageviper_dpd/manifest/download_protocol',
                        [
                            'package_id'  => $item['package_id'],
                            'flag' => 'simple'
                        ]
                    ),
                    'label'  => __('Download Protocol'),
                    'hidden' => empty($item['package_id']) ? true : false
                ];
            }
        }

        return $dataSource;
    }
}
