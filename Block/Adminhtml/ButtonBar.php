<?php
/**
 * Module: CCCC\TranslationManager\Block\Adminhtml
 * Copyright: (c) 2020 cccc.de
 * Date: 15.04.20 23:24
 *
 *
 */

namespace CCCC\TranslationManager\Block\Adminhtml;


use CCCC\TranslationManager\Helper\LanguageRetriever;
use CCCC\TranslationManager\UI\Component\Listing\Column\Actions;

class ButtonBar extends \Magento\Backend\Block\Widget\Container
{
    /** @var LanguageRetriever  */
    protected $languageRetriever;

    /** @var \Magento\Backend\Model\Session  */
    protected $backendSession;

    /** @var \Magento\Backend\Model\UrlInterface  */
    protected $backendUrl;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        LanguageRetriever $languageRetriever,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        array $data = []
    )
    {
        $this->backendUrl = $backendUrl;
        $this->backendSession = $backendSession;
        $this->languageRetriever = $languageRetriever;
        parent::__construct($context, $data);
    }


    /**
     * {@inheritDoc}
     */
    protected function _prepareLayout()
    {
        $restoreDefautsButtonProps = [
            'id' => 'add_translate',
            'label' => __('Add'),
            'class' => 'add primary',
            'button_class' => '',
            'onclick' => "window.location.href ='" . $this->backendUrl->getUrl(Actions::ROW_NEW_URL) . "'",
            'class_name' => 'Magento\Backend\Block\Widget\Button'
        ];
        $this->buttonList->add('add_translate', $restoreDefautsButtonProps);

        $currentLang = $this->backendSession->getData('4c_translate_lang');

        foreach ($this->languageRetriever->getUniqueLanguages() as $lang) {
            $isActive = $currentLang == $lang;
            $restoreDefautsButtonProps = [
                'id' => 'set_translate_lang_'.$lang,
                'label' => $isActive?__('Current language: %1', $lang):__('Change language to %1', $lang),
                'class' => 'add '.($isActive?'primary disabled':'secondary'),
                'button_class' => '',
                'onclick' => "window.location.href ='" . $this->getLangUrl($lang) . "'",
                'class_name' => 'Magento\Backend\Block\Widget\Button'
            ];
            $this->buttonList->add('set_translate_lang_'.$lang, $restoreDefautsButtonProps);
        }

        return parent::_prepareLayout();
    }

    protected function getLangUrl($lang)
    {
        return $this->backendUrl->getUrl(
            'cccc_translate',
            [
                'lang' => $lang
            ]
        );
    }
}
