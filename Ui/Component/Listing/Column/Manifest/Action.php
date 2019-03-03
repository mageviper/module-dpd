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

namespace Mageviper\Dpd\Ui\Component\Listing\Column\Manifest;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mageviper\Dpd\Model\Manifest;
use Mageviper\Dpd\Helper\Data;

/**
 * Class Action
 */
class Action extends Column
{
    /**
     * @var Data
     */
    protected $config;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Data $config,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->config = $config;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (in_array((int)$item['status_int'], [
                    Manifest::PROCESSING,
                    Manifest::QUEUE
                ], true)) {
                    $item[$this->getData('name')]['waiting'] = [
                        'label' => __('Waiting'),
                        'href'  => '#'
                    ];
                    $item[$this->getData('name')]['renew']   = [
                        'href'   => $this->context->getUrl(
                            'mageviper_dpd/manifest/action_queue',
                            [
                                'manifest_id' => $item['id']
                            ]
                        ),
                        'label'  => __('Renew'),
                        'hidden' => false,
                    ];
                } else {
                    if ((int)$item['status_int'] === Manifest::APPLIED) {
                        $item[$this->getData('name')]['download_labels']   = [
                            'href'   => $this->context->getUrl(
                                'mageviper_dpd/manifest/download_label',
                                [
                                    'manifest_id' => $item['id'],
                                ]
                            ),
                            'label'  => __('Download Labels'),
                            'hidden' => false
                        ];
                        $item[$this->getData('name')]['download_protocol'] = [
                            'href'   => $this->context->getUrl(
                                'mageviper_dpd/manifest/download_protocol',
                                [
                                    'manifest_id' => $item['id'],
                                ]
                            ),
                            'label'  => __('Download Protocol'),
                            'hidden' => false
                        ];
                    } else {
                        $item[$this->getData('name')]['apply'] = [
                            'href'   => $this->context->getUrl(
                                'mageviper_dpd/manifest/action_apply',
                                [
                                    'manifest_id' => $item['id']
                                ]
                            ),
                            'label'  => __('Apply'),
                            'hidden' => !$this->config->isTestMode(),
                        ];
                        $item[$this->getData('name')]['queue'] = [
                            'href'   => $this->context->getUrl(
                                'mageviper_dpd/manifest/action_queue',
                                [
                                    'manifest_id' => $item['id']
                                ]
                            ),
                            'label'  => __('Queue'),
                            'hidden' => false,
                        ];
                        $item[$this->getData('name')]['sync']  = [
                            'href'   => $this->context->getUrl(
                                'mageviper_dpd/manifest/action_sync',
                                ['manifest_id' => $item['id']]
                            ),
                            'label'  => __('Sync'),
                            'hidden' => false,
                        ];
                    }
                    $item[$this->getData('name')]['view'] = [
                        'href'   => $this->context->getUrl(
                            'mageviper_dpd/package/index',
                            [
                                'manifest_id' => $item['id']
                            ]
                        ),
                        'label'  => __('View'),
                        'hidden' => true,
                    ];
                }
            }

            return $dataSource;
        }
    }
}