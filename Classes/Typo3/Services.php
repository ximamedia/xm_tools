<?php

namespace Xima\XmTools\Classes\Typo3;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use Xima\XmTools\Classes\Typo3\Model\Extension;
use Symfony\Component\Yaml\Yaml;
use Xima\XmTools\Classes\Typo3\Extension\ExtensionManager;

/**
 * Static and non static helper functions for TYPO3. Context of current extension used.
 * Include it by dependency injection, the rest is done for you.
 *
 * @author Steve Lenz <sle@xima.de>
 * @author Wolfram Eberius <woe@xima.de>
 */
class Services implements \TYPO3\CMS\Core\SingletonInterface
{
    const DEFAULT_LANG_STRING = 'default';

    /**
     * @var \Xima\XmTools\Classes\Typo3\Extension\ExtensionManager
     * @inject
     */
    protected $extensionManager;

    /**
     * The current extension.
     *
     * @var \Xima\XmTools\Classes\Typo3\Model\Extension
     */
    protected $extension;

    protected $langId = null;
    protected $lang = null;

    /**
     * The site parameters from parameters.yml.
     *
     * @var array
     */
    protected $parameters = array();

    /**
     * The settings of the xm_tools extension.
     *
     * @var array
     */
    protected $settings = array();

    public function initializeObject()
    {
        if (TYPO3_MODE === 'FE') {
            $this->langId = $GLOBALS ['TSFE']->sys_language_uid;
            $this->lang = $GLOBALS ['TSFE']->lang;
        } elseif (TYPO3_MODE === 'BE') {
            $this->lang = $GLOBALS['BE_USER']->user['lang'];
        }

        $this->extension = $this->extensionManager->getCurrentExtension();
        $this->settings = $this->extensionManager->getXmTools()->getSettings();

        //get parameters

        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

        $cacheManager = $objectManager->get('Xima\XmTools\Classes\Typo3\Cache\ExtensionCacheManager');
        /* @var $cacheManager \Xima\XmTools\Classes\Typo3\Cache\ExtensionCacheManager */

        $cacheManager->setPath($this->extensionManager->getXmTools()->getKey());

        $cacheFileName = 'parameters.php';
        $parametersSerialized = $cacheManager->get($cacheFileName);

        if ($parametersSerialized && !$this->extensionManager->getXmTools()->getSettings()['devModeIsEnabled']) {
            $this->parameters = unserialize($parametersSerialized);
        } else {
            //get site parameters
            $ymlFile = PATH_site.$this->extensionManager->getXmTools()->getRelPath().'/parameters.yml';

            if (is_readable($ymlFile)) {
                $this->parameters = Yaml::parse(file_get_contents($ymlFile));
                $cacheManager->write($cacheFileName, serialize($this->parameters));
            }
        }

        //offer as js
        if ($this->settings['jsSupportIsEnabled']) {
            $fileName = 'parameters.js';
            $jsFilePath = $cacheManager->getFilePath($fileName);

            if (!is_readable($jsFilePath) || $this->extensionManager->getXmTools()->getSettings()['devModeIsEnabled']) {
                $content = "if (typeof xmTools != \"undefined\")\n";
                $content .= "{\n";
                $content .= '  parameters='.json_encode($this->parameters).";\n";
                $content .= "  xmTools.setParameters(parameters);\n";
                $content .= "  delete parameters;\n";
                $content .= '};';

                    //open and write to file
                    $cacheManager->write($fileName, $content);
            }

            $this->includeJavaScript(array('EXT:xm_tools.js'), $this->extensionManager->getXmTools());
            $this->includeJavaScript(array($jsFilePath));
        }
    }

    /**
     * Fügt HTML-Code zum &lt;head&gt; hinzu.
     *
     * @param string $html
     */
    public function addToHead($html)
    {
        $GLOBALS['TSFE']->additionalHeaderData[$this->extension->getKey()] .= $html;
    }

    /**
     * Binds JavaScript files in the HTML head of the page (TYPO3).
     *
     * @param array $files file names, starting with http or relative
     * @param \Xima\XmTools\Classes\Typo3\Model\Extension from which extension
     */
    public function includeJavaScript(array $files, Extension $extension = null)
    {
        $extension = (is_null($extension)) ? $this->extension : $extension;
        foreach ($files as $file) {

            //support typo3 notation (Ext:Resources/Public/js/xyz.js) and short notation (Ext:xyz.js)
            if (strstr($file, 'EXT:')) {
                $parts = explode(':', $file);
                $file = $extension->getRelPath().$extension->getJsRelPath().array_pop(explode('/', $parts[1]));
            }

            if ($this->getPageRenderer()) {
                $this->getPageRenderer()->addJsFile($file);
            }
        }
    }

    /**
     * Binds JavaScript files by Typoscript config in the HTML head of the page (TYPO3).
     *
     * @param array $config
     *                      Key value array with path to file
     * @param array $keys
     *                      Array of file keys
     */
    public function includeJavaScriptByTypoScript(array $config, array $keys)
    {
        $files = array();

        foreach ($keys as $key) {
            if (array_key_exists($key, $config)) {
                $files[] = $config [$key];
            }
        }

        $this->includeJavaScript($files);
    }

    /**
     * Binds CSS files in the HTML head of the page (TYPO3).
     *
     * @param array $files file names, starting with http or relative
     * @param \Xima\XmTools\Classes\Typo3\Model\Extension from which extension
     */
    public function includeCss(array $files, Extension $extension = null)
    {
        $extension = (is_null($extension)) ? $this->extension : $extension;
        foreach ($files as $file) {

            //support typo3 notation (Ext:Resources/Public/css/xyz.css) and short notation (Ext:xyz.css)
            if (strstr($file, 'EXT:')) {
                $parts = explode(':', $file);
                $file = $extension->getRelPath().$extension->getCssRelPath().array_pop(explode('/', $parts[1]));
            }

            if ($this->getPageRenderer()) {
                $this->getPageRenderer()->addCssFile($file);
            }
        }
    }

    /**
     * Binds CSS files by Typoscript config in the HTML head of the page (TYPO3).
     *
     * @param array $config
     *                      Key value array with path to file
     * @param array $keys
     *                      Array of file keys
     */
    public function includeCssByTypoScript(array $config, array $keys)
    {
        $files = array();

        foreach ($keys as $key) {
            if (array_key_exists($key, $config)) {
                $files[] = $config [$key];
            }
        }

        $this->includeCss($files);
    }

    /**
     * Gibt die Real-Url oder die PageID (?id=[PID]) zurück.
     *
     * @param int  $pageId
     * @param bool $idAsGet
     *
     * @return string
     */
    public function getUrlByPid($pageId, $idAsGet = false)
    {
        $res = null;
        if ($idAsGet) {
            return $_SERVER ['PHP_SELF'].'?id='.$pageId;
        }

        $sql = 'SELECT page_id, pagepath, language_id'.' FROM tx_realurl_pathcache WHERE page_id='.intval($pageId).' AND language_id='.intval($this->langId).' LIMIT 1';
        if (!($res = $GLOBALS ['TYPO3_DB']->sql_query($sql))) {
            return;
        }

        $results = array();
        while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc($res)) {
            $results [] = $row;
        }

        if (!empty($results) && isset($results [0]) && isset($results [0] ['pagepath'])) {
            return $results [0] ['pagepath'];
        } else {
            return $_SERVER ['PHP_SELF'].'?id='.$pageId;
        }
    }

    /**
     * Returns the base URL for GET-Request with ending ? od &.
     *
     * @param int  $pageId
     * @param bool $idAsGet
     *
     * @return string
     */
    public function getBaseUrlForGetRequestByPid($pageId, $idAsGet = false)
    {
        if ($pageId) {
            $url = $this->getUrlByPid($pageId, $idAsGet);
            $url .= preg_match('~\?~', $url) ? '&' : '?';
        } else {
            $url = false;
        }

        return $url;
    }

    /**
     * Registriert Flexforms.<br />
     * Benutzbar in <i>ext_tables.php</i>.
     */
    public static function addFlexforms($extensionKey, $pluginName, $flexformName)
    {
        $extensionName = GeneralUtility::underscoredToUpperCamelCase($extensionKey);
        $pluginSignature = strtolower($extensionName);

        $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature.'_'.strtolower($pluginName)] = 'pi_flexform';
        ExtensionManagementUtility::addPiFlexFormValue($pluginSignature.'_'.strtolower($pluginName), 'FILE:EXT:'.$extensionKey.'/Configuration/FlexForms/'.$flexformName);
    }

    /**
     * Set the title of the single view page to a custom defined title.
     *
     * @param string $title
     */
    public function setPageTitle($title)
    {
        //Todo: test and replace by $GLOBALS['TSFE']->getPageRenderer()->setTitle($titleTagContent);
        //both don't work with extbase
        //$GLOBALS['TSFE']->getPageRenderer()->setTitle($title);
        $GLOBALS['TSFE']->content = preg_replace('/<title>.+<\/title>/U', '<title>'.$title.'</title>', $GLOBALS['TSFE']->content);
    }

    /**
     * Set the title of the single view page to a custom defined title.
     *
     * @param string $title
     */
    public function prependPageTitle($title)
    {
        //Todo: test and replace by $GLOBALS['TSFE']->getPageRenderer()->setTitle($titleTagContent);
        preg_match('/<title>.+<\/title>/U', $GLOBALS['TSFE']->content, $matches, PREG_OFFSET_CAPTURE);

        $hit = $matches[0][0];
        if ($hit != '') {
            $hit = str_replace(array('<title>', '</title>'), '', $hit);
            $this->substitutePageTitle($title.$hit);
        }
    }

    public function getIsoLang()
    {
        switch ($this->lang) {
            case 'cs' :
                return 'cz';
                break;
            default:
                return $this->lang;
                break;
        }
    }

    public function getLangId()
    {
        return $this->langId;
    }

    public function setLangId($langId)
    {
        $this->langId = $langId;
    }

    public function getLang()
    {
        return $this->lang;
    }

    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    public function getExtension()
    {
        return $this->extension;
    }

    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    public function getExtensionManager()
    {
        return $this->extensionManager;
    }

    public function setExtensionManager($extensionManager)
    {
        $this->extensionManager = $extensionManager;

        return $this;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function setSettings(array $settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * @return \TYPO3\CMS\Core\Page\PageRenderer
     */
    public function getPageRenderer()
    {
        $pageRenderer = null;

        if (TYPO3_MODE === 'FE' && isset($GLOBALS ['TSFE'])) {
            $pageRenderer = $GLOBALS['TSFE']->getPageRenderer();
        } elseif (TYPO3_MODE === 'BE' && isset($GLOBALS['TBE_TEMPLATE'])) {
            $pageRenderer = $GLOBALS['TBE_TEMPLATE']->getPageRenderer();
        }

        return $pageRenderer;
    }
}
