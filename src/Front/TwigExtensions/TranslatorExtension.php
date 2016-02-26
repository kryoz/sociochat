<?php

namespace Front\TwigExtensions;

use SocioChat\DI;
use SocioChat\Message\Lang;

class TranslatorExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('trans', [$this, 'translate']),
        );
    }

    public function translate($token, $langCode)
    {
        /** @var Lang $lang */
        $lang = DI::get()->container()->get('lang');
        $lang->setLanguage($langCode);

        return $lang->getPhrase($token);
    }

    public function getName()
    {
        return 'my_translator';
    }
}