<?php

namespace Xima\XmTools\Classes\ViewHelpers\Object;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Offers different php-known array check operations.
 *
 * @author Wolfram Eberius <woe@xima.de>
 *
 * @return bool
 */
class ArrayCheckViewHelper extends AbstractViewHelper
{
    const CONDITION_IN = 'IN';
    const CONDITION_NOT_IN = 'NOT_IN';
    const CONDITION_NOT_FIRST = 'NOT_FIRST';
    const CONDITION_NOT_LAST = 'NOT_LAST';
    const CONDITION_EMPTY = 'EMPTY';
    const CONDITION_NOT_EMPTY = 'NOT_EMPTY';
    const CONDITION_IS_ARRAY = 'IS_ARRAY';
    const CONDITION_IN_KEYS = 'IN_KEYS';
    const CONDITION_NOT_IN_KEYS = 'NOT_IN_KEYS';

    /**
     * @param $array Array
     * @param $needle Object
     * @param $check string
     */
    public function render($array, $needle = '', $check = '')
    {
        switch ($check) {
            case self::CONDITION_IN :
                {
                    return in_array($needle, $array);
                    break;
                }
            case self::CONDITION_NOT_IN :
                {
                    return !in_array($needle, $array);
                    break;
                }
            case self::CONDITION_NOT_FIRST :
                {
                    return (array_shift($array) != $needle);
                    break;
                }
            case self::CONDITION_NOT_LAST :
                {
                    return (array_pop($array) != $needle);
                    break;
                }
            case self::CONDITION_EMPTY :
                {
                    return empty($array);
                    break;
                }
            case self::CONDITION_NOT_EMPTY :
                {
                    return !empty($array);
                    break;
                }
            case self::CONDITION_IS_ARRAY :
                {
                    return is_array($array);
                    break;
                }
            case self::CONDITION_IN_KEYS :
                {
                    return in_array($needle, array_keys($array));
                    break;
                }
            case self::CONDITION_NOT_IN_KEYS :
                {
                    return !in_array($needle, array_keys($array));
                    break;
                }
            default :

                return false;
        }
    }
}