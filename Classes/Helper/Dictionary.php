<?php

namespace Xima\XmTools\Classes\Helper;

/**
 * @author Wolfram Eberius <woe@xima.de>
 */
class Dictionary
{
    /**
     * translations.
     *
     * @var array
     */
    protected $translations = array();

    public function __call($method, $args)
    {
        //make a property out of the getter
        $translationKey = lcfirst(str_replace('get', '', $method));
        if (isset($this->translations[$translationKey])) {
            return $this->translations[$translationKey];
        }

        return 'Missing translation: '.$translationKey;
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    public function setTranslations(array $translations)
    {
        $this->translations = $translations;

        return $this;
    }
}