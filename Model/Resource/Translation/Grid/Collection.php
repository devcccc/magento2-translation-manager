<?php
/**
 * Module: CCCC\TranslationManager\Model\Resource\Translation\Grid
 * Copyright: (c) 2020 cccc.de
 * Date: 15.04.20 13:27
 *
 *
 */

namespace CCCC\TranslationManager\Model\Resource\Translation\Grid;

use CCCC\TranslationManager\Helper\LanguageRetriever;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\Search\Document;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\AreaList;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\TranslateInterface;
use Psr\Log\LoggerInterface;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
// \Magento\Framework\Data\Collection implements SearchResultInterface
{
    /** @var int|null  */
    protected $_forcedTotalCount = null;

    protected $_items = null;

    /** @var TranslateInterface  */
    protected $_translate;

    /** @var LoggerInterface  */
    protected $_logger;

    /** @var AreaList */
    protected $_areaList;

    /** @var \Magento\Backend\Model\Session  */
    protected $backendSession;

    /** @var LanguageRetriever  */
    protected $languageRetriever;

    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        TranslateInterface $translate,
        AreaList $areaList,
        \Magento\Backend\Model\Session $backendSession,
        LanguageRetriever $languageRetriever,
        $resourceModel = null,
        $identifierName = null,
        $connectionName = null
    )
    {
        $mainTable = 'translation';
        $this->_translate = $translate;
        $this->_areaList = $areaList;
        $this->backendSession = $backendSession;
        $this->languageRetriever = $languageRetriever;
        $this->_logger = $logger;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel, $identifierName, $connectionName);
    }

    public function getItems()
    {
        $currentLang = $this->backendSession->getData('4c_translate_lang');
        $langToUse = $currentLang;
        if (is_null($this->_items)) {
            $dataToUse = [];
            $areas = $this->_areaList->getCodes();

            $allLanguages = $this->languageRetriever->getUniqueLanguages();

            usort(
                $allLanguages,
                function($valA, $valB) use ($currentLang) {
                    if ($valA == $currentLang) {
                        return 1;
                    }
                    if ($valB == $currentLang) {
                        return -1;
                    }

                    if ($valA == 'en_US' || $valA == 'en_EN' || $valA == 'en_GB') {
                        return -1;
                    }
                    if ($valB == 'en_US' || $valB == 'en_EN' || $valB == 'en_GB') {
                        return 1;
                    }

                    if ($valA == $valB) {
                        return 0;
                    } else if ($valA < $valB) {
                        return -1;
                    }
                    return 1;
                }
            );

            $baseLang = array_shift($allLanguages);
            $currentLang = array_pop($allLanguages);
            $allLanguages[] = $baseLang;
            $allLanguages[] = $currentLang;

            foreach ($allLanguages as $currentLang) {
                foreach ($areas as $area) {
                    $this->_translate->setLocale($currentLang);
                    $translationData = $this->_translate->loadData($area, true)->getData();

                    foreach ($translationData as $key => $value) {
                        $hash = md5($area . $key);

                        $translatedText = $value;
                        if ($currentLang != $langToUse) {
                            $translatedText = $key;
                        }

                        if (!$this->isDataFiltered($area, $key, $translatedText)) {
                           continue;
                        }

                        $dataToUse[$hash] = new Document(
                            [
                                DocumentInterface::CUSTOM_ATTRIBUTES => [
                                    'area' => new AttributeValue([AttributeValue::ATTRIBUTE_CODE => "area", AttributeValue::VALUE => $area]),
                                    'text' => new AttributeValue([AttributeValue::ATTRIBUTE_CODE => "text", AttributeValue::VALUE => $key]),
                                    'translated_text' => new AttributeValue([AttributeValue::ATTRIBUTE_CODE => "translated_text", AttributeValue::VALUE => $translatedText]),
                                    'override' => new AttributeValue([AttributeValue::ATTRIBUTE_CODE => "override", AttributeValue::VALUE => false]),
                                    'locale' => new AttributeValue([AttributeValue::ATTRIBUTE_CODE => "locale", AttributeValue::VALUE => false])
                                ]
                            ]
                        );
                    }
                }
            }

            if (!empty($this->_orders)) {
                foreach ($this->_orders as $sortField => $sortDirection) {
                    usort(
                        $dataToUse,
                        function (Document $valA, Document $valB) use ($sortField, $sortDirection) {
                            $fieldA = $valA->getCustomAttribute($sortField)->getValue();
                            $fieldB = $valB->getCustomAttribute($sortField)->getValue();

                            if ($sortDirection == 'ASC') {
                                return strcasecmp($fieldA, $fieldB);
                            }

                            return strcasecmp($fieldB, $fieldA);
                        }
                    );
                    // For now: Just sort for the first field
                    break;
                }
            }

            $this->setItems(
                $dataToUse
            );
        }

        $this->setTotalCount(count($this->_items));
        return array_slice($this->_items, ($this->_curPage-1) * $this->getPageSize(), $this->getPageSize());

    }

    protected function isDataFiltered($area, $text, $translatedText) {
        $result = true;
        foreach ($this->getFilter([]) as $filter) {
            $condition = $filter['condition'];

            switch ($filter['field']) {
                case 'area':
                    foreach ($condition as $key => $value) {
                        if ($key != 'eq') {
                            throw new \InvalidArgumentException("Unexpected condition type: $key for field " . $filter['field']);
                        }
                        $result = $result && $value == $area;
                    }
                    break;
                case 'text':
                case 'translated_text':
                    $textValue = $filter['field'] == 'text' ? $text : $translatedText;

                    foreach ($condition as $key => $value) {
                        if (!in_array($key, ['eq', 'like'])) {
                            throw new \InvalidArgumentException("Unexpected condition type: $key for field " . $filter['field']);
                        }
                        if ($key == 'eq') {
                            $result = $result &&  $value == $textValue;
                        }

                        $value = str_replace(['%', '?'], ['.*', '.'], $value);
                        $result = $result &&  preg_match('/' . $value . '/i', $textValue);
                    }
                    break;
            }
        }
        return $result;
    }

    public function setItems(array $items = null)
    {
        $this->_forcedTotalCount = null;
        $this->_items = $items;
    }

    public function getTotalCount()
    {
        if (is_null($this->_forcedTotalCount)) {
            return count($this->getItems());
        }
        return $this->_forcedTotalCount;
    }

    public function setTotalCount($totalCount)
    {
        $this->_forcedTotalCount = $totalCount;
    }

    /**
     * Add field filter to collection
     *
     * If $condition integer or string - exact value will be filtered ('eq' condition)
     *
     * If $condition is array - one of the following structures is expected:
     * <pre>
     * - ["from" => $fromValue, "to" => $toValue]
     * - ["eq" => $equalValue]
     * - ["neq" => $notEqualValue]
     * - ["like" => $likeValue]
     * - ["in" => [$inValues]]
     * - ["nin" => [$notInValues]]
     * - ["notnull" => $valueIsNotNull]
     * - ["null" => $valueIsNull]
     * - ["moreq" => $moreOrEqualValue]
     * - ["gt" => $greaterValue]
     * - ["lt" => $lessValue]
     * - ["gteq" => $greaterOrEqualValue]
     * - ["lteq" => $lessOrEqualValue]
     * - ["finset" => $valueInSet]
     * </pre>
     *
     * If non matched - sequential parallel arrays are expected and OR conditions
     * will be built using above mentioned structure.
     *
     * Example:
     * <pre>
     * $field = ['age', 'name'];
     * $condition = [42, ['like' => 'Mage']];
     * </pre>
     * The above would find where age equal to 42 OR name like %Mage%.
     *
     * @param string|array $field
     * @param string|int|array $condition
     * @throws \Magento\Framework\Exception\LocalizedException if some error in the input could be detected.
     * @return \Magento\Framework\Data\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (is_null($this->_filters)) {
            $this->_filters = [];
        }
        $this->_filters[] = new DataObject(['field' => $field, 'condition' => $condition]);
        return $this;
    }
}
