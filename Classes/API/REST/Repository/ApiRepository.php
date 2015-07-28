<?php

namespace Xima\XmTools\Classes\API\REST\Repository;

use Xima\XmTools\Classes\Helper\Helper;

/**
 * Abstract class for extbase repositories to retrieve data through a REST API.
 * The name of the inheriting repository class will be used for creating the API URL and instantiating model classes. This behaviour can be changed by overriding the function getApiTarget().
 *
 * @author Wolfram Eberius <woe@xima.de>
 */
abstract class ApiRepository extends \Xima\XmTools\Classes\Typo3\Domain\Repository\Repository
{
    const PLACEHOLDER_API_URL = '[Api-URL]';
    const PLACEHOLDER_API_ROUTE = '[Api-Route]';
    const PLACEHOLDER_API_KEY = '[Api-Key]';
    const PLACEHOLDER_TARGET = '[Target]';

    /**
     * The connector class.
     *
     * @var \Xima\XmTools\Classes\API\REST\Connector
     * @inject
     */
    protected $connector;

    /**
     * The xm_tools facade.
     *
     * @var \Xima\XmTools\Classes\Typo3\Services
     * @inject
     */
    protected $typo3Services;

    protected $apiKey;
    protected $apiUrl;
    protected $apiSchema;
    protected $apiRouteFindById;
    protected $apiRouteFindByQuery;

    protected $lastReponse = array();

    /**
     * Retrieves the calling extension through the package name of the inheriting repository class. Configures the repository with the extensions' settings.
     */
    public function initializeObject()
    {
        $extensionName = null;
        if ($this instanceof \Xima\XmTools\Classes\Typo3\Extension\ExtensionAwareInterface) {
            $extensionName = Helper::getClassPackageName($this);
        }

        $extension = $this->typo3Services->getExtensionManager()->getExtensionByName($extensionName);
        $settings = $extension->getSettings();

        $this->setApiKey($settings ['api']['key']);
        $this->setApiUrl($settings ['api']['url']);
        $this->setApiSchema($settings ['api']['schema']);
        $this->setApiRouteFindById($settings ['api']['routeFindById']);
        $this->setApiRouteFindByQuery($settings ['api']['routeFindByQuery']);

        $this->connector->setExtension($extension);
    }

    /**
     * Find an entity by id.
     *
     * @param $id
     *
     * @return Xima\XmTools\Classes\API\REST\Model\AbstractEntity|array The model class or array with the given id.
     */
    public function findByUid($id)
    {
        $target = $this->getApiTarget();
        $apiRoute = str_replace(self::PLACEHOLDER_TARGET, $target, $this->apiRouteFindById).'/'.$id;
        $apiUrl = $this->buildUrl($apiRoute);

        $this->lastReponse = $this->connector->get($apiUrl, $this);

        return array_pop($this->lastReponse['result']);
    }

    /**
     * Find all entities.
     *
     * @param params array An array of filters.
     *
     * @return Array of model objects (@see ApiRepository::findByUid())
     */
    public function findAll()
    {
        $target = $this->getApiTarget();
        $apiRoute = str_replace(self::PLACEHOLDER_TARGET, $target, $this->apiRouteFindByQuery);
        $apiUrl = $this->buildUrl($apiRoute, array('lang' => $this->typo3Services->getLang()));

        $this->lastReponse = $this->connector->get($apiUrl, $this);

        return $this->lastReponse['result'];
    }

    /**
     * Find all entities filtered by a \Xima\XmTools\Classes\Typo3\Query\QueryInterface.
     *
     * @param \Xima\XmTools\Classes\Typo3\Query\QueryInterface $query The filter object.
     *
     * @return Array of model objects (@see ApiRepository::findByUid())
     */
    public function findAllByQuery(\Xima\XmTools\Classes\Typo3\Query\QueryInterface $query)
    {
        $params = $query->getParams();

        $target = $this->getApiTarget();
        $apiRoute = str_replace(self::PLACEHOLDER_TARGET, $target, $this->apiRouteFindByQuery);
        $apiUrl = $this->buildUrl($apiRoute, $params);

        $this->lastReponse = $this->connector->get($apiUrl, $this);

        return $this->lastReponse['result'];
    }

    /**
     * Create a Xima\XmTools\Classes\API\REST\Query object. Overrides TYPO3 default behavivour.
     *
     * @return \Xima\XmTools\Classes\API\REST\Query
     */
    public function createQuery()
    {
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $query = $objectManager->get('Xima\XmTools\Classes\API\REST\Query', $this);

        return $query;
    }

    /**
     * Builds the URL to the API. The API schema must be definedin the TYPO3 constant editor to something like:
     * -[Api-URL]/[Api-Key][Api-Route]
     * -[Api-URL][Api-Route]?[Api-Key]
     * -...
     *
     * @param $route string
     * @param $params array
     *
     * @return string
     */
    private function buildUrl($route, $params = array())
    {
        $placeHolders = array(self::PLACEHOLDER_API_URL, self::PLACEHOLDER_API_ROUTE, self::PLACEHOLDER_API_KEY);
        $replace = array($this->apiUrl, $route, $this->apiKey);
        $url = str_replace($placeHolders, $replace, $this->apiSchema);

        if (!empty($params)) {
            // handle params
            $paramsAsString = array();
            $queryString = '';
            foreach ($params as $key => $value) {
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                $paramsAsString [] = $key.'='.urlencode($value);
            }
            if (!empty($paramsAsString)) {
                $queryString = implode('&', $paramsAsString);
            }

            $url .= (strstr($url, '?')) ? '&' : '?';
            $url .= $queryString;
        }

        return $url;
    }

    private function getApiTarget()
    {
        $reflect = new \ReflectionClass($this);
        $route = strtolower(str_replace('Repository', '', $reflect->getShortName()));

        return $route;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    public function getApiSchema()
    {
        return $this->apiSchema;
    }

    public function setApiSchema($apiSchema)
    {
        $this->apiSchema = $apiSchema;
    }

    public function getApiRouteFindById()
    {
        return $this->apiRouteFindById;
    }

    public function setApiRouteFindById($apiRouteFindById)
    {
        $this->apiRouteFindById = $apiRouteFindById;
    }

    public function getApiRouteFindByQuery()
    {
        return $this->apiRouteFindByQuery;
    }

    public function setApiRouteFindByQuery($apiRouteFindByQuery)
    {
        $this->apiRouteFindByQuery = $apiRouteFindByQuery;
    }

    /**
     * To make it compatible with Typo3.
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    public function getLastReponse()
    {
        return $this->lastReponse;
    }

    public function setLastReponse($lastReponse)
    {
        $this->lastReponse = $lastReponse;

        return $this;
    }
}
