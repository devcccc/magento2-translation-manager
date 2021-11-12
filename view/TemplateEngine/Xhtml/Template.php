<?php
/**
 * Module: CCCC\TranslationManager\view\TemplateEngine\Xhtml
 * Copyright: (c) 2020 cccc.de
 * Date: 15.04.20 17:13
 *
 *
 */

namespace CCCC\TranslationManager\view\TemplateEngine\Xhtml;

class Template extends \Magento\Framework\View\TemplateEngine\Xhtml\Template
{
    public function append($content)
    {
        /**
         * Hotfix according to https://github.com/magento/magento2/commit/2324d99cd740fd969413aa50096b24c054ecf653
         * and https://github.com/magento/magento2/issues/7658
         * Prevents a
         *  DOMDocumentFragment::appendXML(): Entity: line 1: parser error : CData section too big
         */
        $ownerDocument= $this->templateNode->ownerDocument;
        $document = new \DOMDocument();
        $document->loadXml($content, LIBXML_PARSEHUGE);
        $this->templateNode->appendChild(
            $ownerDocument->importNode($document->documentElement, true)
        );
    }

}