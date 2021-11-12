<?php
/**
 * Module: CCCC\TranslationManager\Model\Translation
 * Copyright: (c) 2020 cccc.de
 * Date: 16.04.20 11:54
 *
 *
 */

namespace CCCC\TranslationManager\Model\Translation;


use CCCC\TranslationManager\Helper\LanguageRetriever;
use CCCC\TranslationManager\Model\Resource\Translation\Grid\CollectionFactory;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\Search\Document;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\TranslateInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Psr\Log\LoggerInterface;

class DataProvider extends AbstractDataProvider
{
    /** @var LanguageRetriever  */
    protected $languageRetriever;

    /** @var TranslateInterface */
    protected $translate;

    /** @var LoggerInterface  */
    protected $logger;

    /** @var array  */
    protected $filters = [];

    protected $idEnc = null;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        LanguageRetriever $languageRetriever,
        TranslateInterface $translate,
        LoggerInterface $logger,
        array $meta = [],
        array $data = []
    )
    {
        $this->logger = $logger;
        $this->languageRetriever = $languageRetriever;
        $this->translate = $translate;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        $this->logger->info($filter->getField(). ' ' . $filter->getConditionType() . ' '.$filter->getValue());

        if ($filter->getConditionType() == "eq" && $filter->getField() == 'id_enc') {
            $this->idEnc = urldecode($filter->getValue());
            $encodedValue = urldecode($filter->getValue());
            $decodedValue = base64_decode($encodedValue);
            $fieldData = unserialize($decodedValue);

            if (!empty($fieldData)) {
                foreach ($fieldData as $key => $val) {
                    $this->logger->info("Filter data: $key eq $val");
                    $this->filters[$key] = $val;
                }
            }
        }
    }

    public function getData()
    {
        $translations = [];

        $allLanguages = $this->languageRetriever->getUniqueLanguages();
        sort($allLanguages);

        $textFilter = array_key_exists('text', $this->filters) ? $this->filters['text'] : 'n/a'.uniqid();
        $areaFilter = array_key_exists('area', $this->filters) ? $this->filters['area'] : 'n/a'.uniqid();

        if (empty($textFilter) || empty($areaFilter)) {
            throw new \InvalidArgumentException("CCCC Translate: Filters not set");
        }

        $this->logger->info("Loading $areaFilter => $textFilter - languages: ".implode(', ', $allLanguages));

        foreach ($allLanguages as $currentLang) {
                $this->translate->setLocale($currentLang);
                $translationData = $this->translate->loadData($areaFilter, true)->getData();

                if (empty($this->filters) || !array_key_exists('text', $this->filters)) {
                    $translations[] = [
                        "language" => $currentLang,
                        "text" => "",
                        "record_id" => $currentLang
                    ];
                } elseif (array_key_exists($this->filters['text'], $translationData)) {
                    $translations[] = [
                        "language" => $currentLang,
                        "text" => $translationData[$this->filters['text']],
                        "record_id" => $currentLang
                    ];
                } else {
                    $translations[] = [
                        "language" => $currentLang,
                        "text" => $this->filters['text'],
                        "record_id" => $currentLang
                    ];
                }
        }

        $result = [
            $this->idEnc => [
                "text" => empty($this->filters) || !array_key_exists('text', $this->filters) ? "" : $this->filters['text'],
                "area" => "frontend",
                "translation" => $translations
            ],
            urlencode($this->idEnc) => [
                "text" => empty($this->filters) || !array_key_exists('text', $this->filters) ? "" : $this->filters['text'],
                "area" => "frontend",
                "translation" => $translations
            ]
        ];

        $this->logger->info("RESULT :". print_r($result, true));

        return $result;
    }


}
