<?php
/**
 * Module: CCCC\TranslationManager\Model\Resource\Translation
 * Copyright: (c) 2020 cccc.de
 * Date: 19.04.20 09:31
 *
 *
 */

namespace CCCC\TranslationManager\Model\Resource\Translation;


use CCCC\TranslationManager\Helper\LanguageRetriever;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\DriverInterface;
use Psr\Log\LoggerInterface;

class FileHandler
{
    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var Csv
     */
    protected $csvParser;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var DriverInterface
     */
    protected $fileDriver;

    /** @var LanguageRetriever */
    protected $languageRetriever;

    /** @var array  */
    protected $translations = [];

    /** @var mixed|string  */
    protected $basePath = DirectoryList::MEDIA;

    /** @var mixed|string  */
    protected $csvSubdirectory = '4ctranslate/i18n';

    /** @var Manager  */
    protected $cacheManager;

    public function __construct(
        DirectoryList $directoryList,
        Filesystem $fileSystem,
        Csv $csvParser,
        LanguageRetriever $languageRetriever,
        LoggerInterface $logger,
        File $fileDriver,
        Manager $cacheManager,
        array $data = [])
    {
        $this->directoryList = $directoryList;
        $this->fileSystem = $fileSystem;
        $this->csvParser = $csvParser;
        $this->languageRetriever = $languageRetriever;
        $this->logger = $logger;
        $this->fileDriver = $fileDriver;
        $this->cacheManager = $cacheManager;

        if (array_key_exists('basepath', $data)) {
            $this->basePath = $data['basepath'];
        }

        if (array_key_exists('csvSubdirectory', $data)) {
            $this->csvSubdirectory = $data['csvSubdirectory'];
        }
    }

    public function setTranslation($area, $text, array $translationData) {
        if (empty($this->translations) || !array_key_exists($area, $this->translations)) {
            $this->load($area);
        }

        if (!array_key_exists($text, $this->translations[$area]) || $this->translations[$area][$text] === false) {
            $this->translations[$area][$text] = [];
        }

        foreach ($translationData as $lang => $translatedText) {
            $this->translations[$area][$text][$lang] = $translatedText;
        }
    }

    public function resetTranslation($area, $text) {
        if (empty($this->translations) || !array_key_exists($area, $this->translations)) {
            $this->load($area);
        }

        if (array_key_exists($text, $this->translations[$area])) {
            $this->translations[$area][$text] = false;
        }
    }

    public function load($area) {
        $path = $this->getI18nDirectoryPath();

        $areaData = [];
        foreach ($this->languageRetriever->getUniqueLanguages() as $lang) {
            $filePath = $this->getI18nFilePath($path, $lang);
            if ($this->fileDriver->isExists($filePath)) {
                $languageTexts = $this->csvParser->getDataPairs($filePath);

                foreach ($languageTexts as $baseText => $translatedText) {
                    if (!array_key_exists($baseText, $areaData)) {
                        $areaData[$baseText] = [$lang => $translatedText];
                    } else {
                        $areaData[$baseText][$lang] = $translatedText;
                    }
                }
            }
        }

        $this->translations[$area] = $areaData;
        return $areaData;
    }

    public function save($area) {
        $path = $this->getI18nDirectoryPath();

        $languageSpecificData = [];
        $validLanguages = $this->languageRetriever->getUniqueLanguages();

        foreach ($validLanguages as $lang) {
            $languageSpecificData[$lang] = [];
        }

        foreach ($this->translations[$area] as $baseText => $translationData) {
            foreach ($translationData as $lang => $translatedText) {
                if ($translatedText === false) {
                    continue;
                }

                $languageSpecificData[$lang][] = [$baseText, $translatedText];
            }
        }

        foreach ($languageSpecificData as $lang => $data) {
            $filePath = $this->getI18nFilePath($path, $lang);
            if (!empty($data)) {
                $this->csvParser->saveData($filePath, $data);
            } else if ($this->fileDriver->isExists($filePath)) {
                $this->fileDriver->deleteFile($filePath);
            }
        }

        $this->flushTranslationCache();
    }

    protected function flushTranslationCache() {
        $this->cacheManager->flush(['translate']);
    }

    protected function getI18nDirectoryPath()
    {
        $path = rtrim($this->fileDriver->getAbsolutePath(
            rtrim($this->directoryList->getPath($this->basePath), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, $this->csvSubdirectory), DIRECTORY_SEPARATOR);
        $this->fileDriver->createDirectory($path);
        return $path;
    }

    protected function getI18nFilePath(string $path, $lang)
    {
        return $path . DIRECTORY_SEPARATOR . sprintf("%s.csv", $lang);
    }
}