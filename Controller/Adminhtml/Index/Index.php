<?php
/**
 * Module: CCCC\TranslationManager\Controller\Adminhtml
 * Copyright: (c) 2020 cccc.de
 * Date: 15.04.20 10:29
 *
 *
 */

namespace CCCC\TranslationManager\Controller\Adminhtml\Index;


use CCCC\TranslationManager\Helper\LanguageRetriever;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index  extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /** @var \Magento\Backend\Model\Session  */
    protected $backendSession;

    /** @var \Magento\Backend\Model\Auth\Session  */
    protected $backendAuthSession;

    /** @var LanguageRetriever  */
    protected $languageRetriever;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Backend\Model\Session $backendSession,
        LanguageRetriever $languageRetriever
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->backendAuthSession = $backendAuthSession;
        $this->backendSession = $backendSession;
        $this->languageRetriever = $languageRetriever;
    }

    /**
     *
     * @return Page
     */
    public function execute()
    {
        $selectedLang = $this->getRequest()->getParam('lang', null);
        $currentLang = $this->backendSession->getData('4c_translate_lang');

        if (empty($currentLang) || $currentLang != $selectedLang) {
            $locales = $this->languageRetriever->getUniqueLanguages();
            if (empty($selectedLang)) {
                $selectedLang = $this->backendAuthSession->getUser()->getInterfaceLocale();
                if (!in_array($selectedLang, $locales)) {
                    $selectedLang = reset($locales);
                }
            }

            $this->backendSession->setData('4c_translate_lang', $selectedLang);
        }


        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}
