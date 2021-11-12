<?php
/**
 * Module: CCCC\TranslationManager\Helper
 * Copyright: (c) 2020 cccc.de
 * Date: 16.04.20 08:01
 *
 *
 */

namespace CCCC\TranslationManager\Helper;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

class LanguageRetriever extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Context $context
    )
    {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function getUniqueLanguages() {
        /** @var  $entries */
        $locale = [];
        $stores = $this->storeManager->getStores($withDefault = false);

        foreach($stores as $store) {
            $locale[] = $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getStoreId());;
        }
        sort($locale);
        $locale = array_unique($locale);
        return $locale;
    }

}