<?php
/**
 * Module: CCCC\TranslationManager\UI\Component\Listing\Column
 * Copyright: (c) 2020 cccc.de
 * Date: 15.04.20 15:43
 *
 *
 */

namespace CCCC\TranslationManager\UI\Component\Listing\Column;


use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{
    /** Url path */
    const ROW_EDIT_URL = 'cccc_translate/index/edit';
    const ROW_NEW_URL = 'cccc_translate/index/create';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /** @var \Magento\Framework\Serialize\Serializer\Serialize  */
    protected $serializer;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serializer
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Magento\Framework\Serialize\Serializer\Serialize $serializer,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->serializer = $serializer;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(
                        self::ROW_EDIT_URL,
                        ['id_enc' => urlencode(base64_encode($this->serializer->serialize(['area' => $item['area'], 'text' => $item['text']])))]
                    ),
                    'label' => __('Edit'),
                    'hidden' => false,
                ];
            }
        }

        return $dataSource;
    }
}
