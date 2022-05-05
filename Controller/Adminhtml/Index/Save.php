<?php
/**
 * Module: CCCC\TranslationManager\Controller\Adminhtml\Index
 * Copyright: (c) 2020 cccc.de
 * Date: 19.04.20 09:27
 *
 *
 */

namespace CCCC\TranslationManager\Controller\Adminhtml\Index;

use CCCC\TranslationManager\Model\ResourceModel\Translation\FileHandler;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Save extends Action
{
    /** @var FileHandler  */
    protected $i18nFileHandler;

    public function __construct(FileHandler $i18nFileHandler, Context $context)
    {
        $this->i18nFileHandler = $i18nFileHandler;
        parent::__construct($context);
    }

    public function execute()
    {
        $request = $this->getRequest();

        $area = $request->getParam('area');
        $text = $request->getParam('text');

        $submittedTranslations = [];

        foreach ($request->getParam('translation') as $translation) {
            $submittedTranslations[$translation['language']] = $translation['text'];
        }

        $this->i18nFileHandler->setTranslation($area, $text, $submittedTranslations);
        $this->i18nFileHandler->save($area);

        $this->_redirect('cccc_translate/index');
    }

}