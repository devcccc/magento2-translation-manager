<?php
/**
 * Module: CCCC\TranslationManager\Framework
 * Copyright: (c) 2020 cccc.de
 * Date: 19.03.20 22:07
 *
 *
 */

namespace CCCC\TranslationManager\Framework;


use CCCC\TranslationManager\Model\Resource\Translation\FileHandler;
use Magento\Framework\App\Language\Dictionary;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\State;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Translate\ResourceInterface;
use Magento\Framework\View\DesignInterface;
use Psr\Log\LoggerInterface;

class Translate extends \Magento\Framework\Translate
{
    /** @var LoggerInterface  */
    protected $logger;

    /** @var FileHandler  */
    protected $fileHandler;

    public function __construct(DesignInterface $viewDesign, FrontendInterface $cache,
                                \Magento\Framework\View\FileSystem $viewFileSystem, ModuleList $moduleList,
                                Reader $modulesReader, ScopeResolverInterface $scopeResolver,
                                ResourceInterface $translate, ResolverInterface $locale,
                                State $appState, Filesystem $filesystem,
                                RequestInterface $request, Csv $csvParser,
                                Dictionary $packDictionary,
                                LoggerInterface $logger,
                                FileHandler $fileHandler,
                                File $fileDriver = null)
    {
        $this->logger = $logger;
        $this->fileHandler = $fileHandler;
        parent::__construct($viewDesign, $cache, $viewFileSystem, $moduleList, $modulesReader, $scopeResolver, $translate, $locale, $appState, $filesystem, $request, $csvParser, $packDictionary, $fileDriver);
    }

    public function loadData($area = null, $forceReload = false)
    {
        $rtc = parent::loadData($area, $forceReload);

        if ($forceReload) {
            $this->mergeTranslations($area);
        }

        return $rtc;
    }

    protected function _saveCache()
    {
        $area = $this->getConfig(self::CONFIG_AREA_KEY);
        $this->mergeTranslations($area);
        return parent::_saveCache();
    }

    protected function mergeTranslations($area) {
        $translationLoaded = $this->fileHandler->load($area);
        $dataToMerge = [];

        foreach ($translationLoaded as $baseText => $translationData) {
            $translatedText = null;
            $baseTranslation = $baseText;
            foreach ($translationData as $lang => $translationText) {
                if ($lang == $this->getLocale()) {
                    $translatedText = $translationText;
                } else if (in_array($lang, ['en_US', 'en_GB', 'en_EN'])) {
                    $baseTranslation = $translationText;
                }
            }

            $dataToMerge[$baseText] = $translatedText ?? $baseTranslation ?? $baseText;
        }

        $this->_addData($dataToMerge);
    }
}