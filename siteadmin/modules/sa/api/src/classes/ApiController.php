<?php

namespace sa\api;

use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use sa\api\Responses\ApiJsonResponse;
use sacore\application\app;
use \sacore\application\controller;
use sacore\application\DefaultRepository;
use sacore\application\Exception;
use sacore\application\ioc;
use sacore\application\IocDuplicateClassException;
use sacore\application\IocException;
use sacore\application\modRequest;
use sacore\application\ModRequestAuthenticationException;
use sacore\application\Request;
use sacore\application\responses\ISaResponse;
use sa\member\saMember;
use sa\system\saAuth;
use sacore\utilities\arrayUtils;
use sacore\utilities\doctrineUtils;
use sacore\utilities\stringUtils;

/**
 * Class ApiController
 *
 * @package sa\api
 */
class ApiController extends controller
{
    // <editor-fold desc="Member Fields">

    /** @var ApiKey $apiKey */
    protected ApiKey $apiKey;
    protected string $entityName;
    /** @var DefaultRepository */
    protected DefaultRepository $repo;

    protected bool $enableDefaultApiEndpoints = true;
    protected bool $paginatedIndexResults = true;

    // </editor-fold>

    // <editor-fold desc="Constructor">

    /**
     * apiController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    // </editor-fold>

    // <editor-fold desc="Getters & Setters">

    /**
     * @return bool
     */
    public function isEnableDefaultsApiEndpoints(): bool
    {
        return $this->enableDefaultApiEndpoints;
    }

    /**
     * @param bool $enableDefaultApiEndpoints
     */
    public function setEnableDefaultApiEndpoints(bool $enableDefaultApiEndpoints)
    {
        $this->enableDefaultApiEndpoints = $enableDefaultApiEndpoints;
    }

    /**
     * @return bool
     */
    public function isPaginatedIndexResults(): bool
    {
        return $this->paginatedIndexResults;
    }

    /**
     * @param $paginatedIndexResults
     */
    public function setPaginatedIndexResults($paginatedIndexResults)
    {
        $this->paginatedIndexResults = $paginatedIndexResults;
    }

    /**
     * @param ApiKey $apiKey
     * @return apiController
     */
    public function setApiKey(ApiKey $apiKey) : ApiController
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return ApiKey
     */
    public function getApiKey() : ApiKey
    {
        return $this->apiKey;
    }

    /**
     * @param string $entity
     * @return apiController
     */
    public function setEntityName(string $entity) : ApiController
    {
        $this->entityName = $entity;

        return $this;
    }

    /**
     * @param DefaultRepository $repo
     * @return apiController
     */
    public function setRepo(DefaultRepository $repo) : ApiController
    {
        $this->repo = $repo;

        return $this;
    }

    // </editor-fold>

    // <editor-fold desc="Internal Functions">

    /**
     * @param QueryBuilder $qb
     * @return mixed
     * @throws ApiAuthException
     * @throws ModRequestAuthenticationException
     */
    protected function memberRestrictQuery(QueryBuilder $qb) : QueryBuilder
    {
        /** @var saMember $member */
        $member = modRequest::request('auth.member');

        $meta = app::$entityManager->getClassMetadata($this->entityName);
        $mappings = $meta->getAssociationMappings();

        if ($mappings['member']) {
            $field = 'c.'.$mappings['member']['fieldName'];
            $qb->leftJoin( $field, 'm' );

            if ($member) {
                $qb->andWhere($field.'=:member');
                $qb->setParameter(':member', $member);
            } else {
                throw new ApiAuthException('A member is required to use this api endpoint.', 403);
            }
        }

        return $qb;
    }

    /**
     * @param $entity
     * @return mixed
     * @throws ModRequestAuthenticationException
     */
    protected function memberRestrictAssign($entity) : object
    {
        /** @var saMember $member */
        $member = modRequest::request('auth.member');

        $meta = app::$entityManager->getClassMetadata($this->entityName);
        $mappings = $meta->getAssociationMappings();

        if ( $mappings['member'] ) {
            $field = 'set'.stringUtils::camelCasing($mappings['member']['fieldName']);
            $entity->$field($member);
        }

        return $entity;
    }

    /**
     * @param Request $request
     * @return QueryBuilder
     * @throws ApiException
     */
    protected function advancedQueryGetQueryBuilder(Request $request): QueryBuilder
    {
        $json = $this->getRequestJsonArrayBody($request);

        $max = !empty($json['max_result']) ? $json['max_result'] : 50;
        $offset = !empty($json['offset']) ? $json['offset'] : 0;

        $q = $this->repo->createQueryBuilder('q');

        $q->setMaxResults($max);
        $q->setFirstResult($offset);

        if (isset($json['join']) && is_array($json['join'])) {
            foreach ($json['join'] as $join) {

                if (empty($join['field']))
                    throw new ApiException("Missing the required param 'field' on the request join");
                if (empty($join['alias']))
                    throw new ApiException("Missing the required param 'alias' on the request join");

                $aliasedField = $join['field'];

                if (!str_contains($aliasedField, '.')) {
                    $aliasedField = 'q.'.$aliasedField;
                }

                switch(strtolower($join['type'])) {
                    case 'inner':
                        $q->innerJoin($aliasedField, $join['alias']);
                        break;
                    default:
                        $q->leftJoin($aliasedField, $join['alias']);
                }

            }
        }

        if (isset($json['where']) && is_array($json['where'])) {
            $previousOperation = 'AND';

            foreach ($json['where'] as $groupKey => $whereGroups) {
                $statement = '( ';

                foreach ($whereGroups['fields'] as $fieldKey => $field) {
                    $aliasedField = $field['field'];

                    if (!str_contains($aliasedField, '.')) {
                        $aliasedField = 'q.' . $aliasedField;
                    }

                    $value = $field['value'];

                    switch(strtoupper($field['comparison'])) {
                        case '!=':
                        case '<>':
                        case 'NOTEQUAL':
                            $comparison = ' <> ';
                            break;
                        case 'NULL':
                            $comparison = ' IS NULL ';
                            break;
                        case '<=':
                        case 'LTE':
                            $comparison = ' <= ';
                            break;
                        case '>=':
                        case 'GTE':
                            $comparison = ' >= ';
                            break;
                        case '<':
                        case 'LT':
                            $comparison = ' < ';
                            break;
                        case '>':
                        case 'GT':
                            $comparison = ' > ';
                            break;
                        case 'LIKE':
                            $comparison = ' LIKE ';
                            break;
                        case '=':
                        case 'EQUAL':
                        default:
                            $comparison = ' = ';
                    }

                    $valueParamName = preg_replace('/[^a-z0-9]/i', '', $field['field'] . $groupKey . $fieldKey);

                    if (count($whereGroups['fields']) == ($fieldKey + 1)) {
                        $statement .= ' '.$aliasedField . $comparison .' :' . $valueParamName .' ';
                    } else {
                        $statement .= ' '.$aliasedField . $comparison .' :' . $valueParamName .' '.$field['operation'];
                    }

                    $q->setParameter(':' . $valueParamName, $value);
                }

                $statement .= ' ) ';

                if ($previousOperation=='AND') {
                    $q->andWhere($statement);
                } else {
                    $q->orWhere($statement);
                }

                $previousOperation = strtoupper($whereGroups['operation']);
            }
        }

        if (isset($json['order_by']) && is_array($json['order_by'])) {
            foreach($json['order_by'] as $orderInfo) {
                $aliasedField = $orderInfo['field'];

                if (!str_contains($aliasedField, '.')) {
                    $aliasedField = 'q.' . $aliasedField;
                }

                $q->addOrderBy($aliasedField, $orderInfo['direction']);
            }
        }

        return $q;
    }

    // </editor-fold>

    // <editor-fold desc="Default Actions">

    /**
     * @param Request $request
     * @return ApiJsonResponse
     * @throws Exception
     * @api
     */
    public function index(Request $request) : ISaResponse
    {
        if (!$this->enableDefaultApiEndpoints) {
            $this->error501();
        }

        if ($this->paginatedIndexResults) {
            $max = !empty($_REQUEST['limit']) ? $_REQUEST['limit'] : 50;
            $offset = !empty($_REQUEST['offset']) ? $_REQUEST['offset'] : 0;

            $data = $this->repo->findBy([], null, $max, $offset );
        } else {
            $data = $this->repo->findAll();
        }

        if (isset($json['type']) && $json['type']=='simple') {
            $dataArray = doctrineUtils::getEntityCollectionArray($data, false);
        } elseif (isset($json['type']) && $json['type']=='complex') {
            $dataArray = doctrineUtils::getEntityCollectionArray($data, false, $json['associations']);
        } else {
            $dataArray = doctrineUtils::getEntityCollectionArray($data, true);
        }

        $resRef = ioc::staticGet('ApiJsonResponse');

        /** @var ApiJsonResponse $res */
        $res = new $resRef(200);

        $res->data['response'] = $dataArray;

        return $res;
    }

    /**
     * @return mixed
     * @throws ApiAuthException
     * @throws ModRequestAuthenticationException
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @api
     */
    public function count(Request $request) : ISaResponse
    {
        if(!$this->enableDefaultApiEndpoints) {
            return $this->error501();
        }

        $query = $this->repo->createQueryBuilder('c');
        $query->select('count(c)');

        if ($this->apiKey->getType() == ApiKey::TYPE_MEMBER)
            $query = static::memberRestrictQuery($query);

        $resRef = ioc::staticGet('ApiJsonResponse');

        $res = new $resRef(200);
        $res->data['response'] = $query->getQuery()->getSingleScalarResult();

        return $res;
    }

    /**
     * @param Request $request
     * @return ApiJsonResponse
     * @throws ApiAuthException
     * @throws Exception
     * @throws ModRequestAuthenticationException
     * @throws NonUniqueResultException
     * @api
     */
    public function get(Request $request) : ISaResponse
    {
        if(!$this->enableDefaultApiEndpoints) {
            return $this->error501();
        }

        $jsonData = $this->getRequestJsonArrayBody($request);
        $id = $jsonData['id'];

        if (empty($id)) {
            return $this->index($request);
        }

        $query = $this->repo->createQueryBuilder('c');
        $query->where('c.id=:id');
        $query->setParameter(':id', $id);

        if ($this->apiKey->getType()==ApiKey::TYPE_MEMBER)
            $query = static::memberRestrictQuery($query);

        $data = $query->getQuery()->getOneOrNullResult();

        if (empty($data)) {
            return $this->error400('Bad Request');
        }

        if (isset($json['type']) && $json['type'] == 'simple') {
            $data = doctrineUtils::getEntityArray($data, false);
        } elseif (isset($json['type']) && $json['type']=='complex') {
            $data = doctrineUtils::getEntityArray($data, false, false, $json['associations']);
        } else {
            $data = doctrineUtils::getEntityArray($data, true);
        }

        $resRef = ioc::staticResolve('ApiJsonResponse');

        /** @var ApiJsonResponse $res */
        $res = new $resRef(200);
        $res->data['response'] = $data;

        return $res;
    }

    /**
     * @param Request $request
     * @throws ApiAuthException
     * @throws InvalidDataException
     * @throws ModRequestAuthenticationException
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     * @api
     */
    public function delete(Request $request)
    {
        if (!$this->enableDefaultApiEndpoints) {
            $this->error501();
        }

        $id = $request->getRouteParams()->get('entityId');

        $query = $this->repo->createQueryBuilder('c');
        $query->where('c.id=:id');
        $query->setParameter(':id', $id);

        if ($this->apiKey->getType()==ApiKey::TYPE_MEMBER)
            $query = static::memberRestrictQuery($query);

        $data = $query->getQuery()->getOneOrNullResult();

        if (empty($data))
            throw new InvalidDataException("Invalid request", 400);

        app::$entityManager->remove($data);
        app::$entityManager->flush();
    }

    /**
     * @param Request $request
     * @return ISaResponse
     * @throws ApiAuthException
     * @throws Exception
     * @throws InvalidDataException
     * @throws MappingException
     * @throws ModRequestAuthenticationException
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     * @api
     */
    public function update(Request $request) : ISaResponse
    {
        if (!$this->enableDefaultApiEndpoints) {
            $this->error501();
        }

        $id = $request->getRouteParams()->get('entityId');
        $requestData = $this->getRequestJsonArrayBody($request);

        $query = $this->repo->createQueryBuilder('c');
        $query->where('c.id=:id');
        $query->setParameter(':id', $id);

        if ($this->apiKey->getType() == ApiKey::TYPE_MEMBER)
            $query = static::memberRestrictQuery($query);

        $entity = $query->getQuery()->getOneOrNullResult();

        if (empty($entity)) {
            return $this->error500('Invalid Request');
        }

        doctrineUtils::setEntityData($requestData, $entity);

        if ($this->apiKey->getType()==ApiKey::TYPE_MEMBER)
            $entity = $this->memberRestrictAssign($entity);

        app::$entityManager->flush($entity);

        $resRef = ioc::staticResolve('ApiJsonResponse');

        $res = new $resRef(200);
        $res->data['success'] = true;
        $res->data['response'] = doctrineUtils::getEntityArray($entity, true);

        return $res;
    }

    /**
     * @param Request $request
     * @return ApiJsonResponse
     * @throws Exception
     * @throws InvalidDataException
     * @throws IocDuplicateClassException
     * @throws IocException
     * @throws MappingException
     * @throws ModRequestAuthenticationException
     * @throws ORMException
     * @throws OptimisticLockException
     * @api
     */
    public function create(Request $request) : ISaResponse
    {
        if (!$this->enableDefaultApiEndpoints) {
            $this->error501();
        }

        $requestData = $this->getRequestJsonArrayBody($request);

        $entity = ioc::get($this->entityName);
        doctrineUtils::setEntityData($requestData, $entity);

        if ($this->apiKey->getType() == ApiKey::TYPE_MEMBER)
            $entity = $this->memberRestrictAssign($entity);

        app::$entityManager->persist($entity);
        app::$entityManager->flush($entity);

        $resRef = ioc::staticResolve('ApiJsonResponse');

        /** @var ApiJsonResponse $res */
        $res = new $resRef(200);
        $res->data['success'] = true;
        $res->data['response'] = doctrineUtils::getEntityArray($entity, true);

        return $res;
    }

    /**
     *
     * api/v1/system/saCity/advancedQuery
     *
     *   {
     *   "where": [{
     *              "fields": [
     *                          { "field": "name", "comparison": "LIKE", "operation": "OR", "value": "Corbin" },
     *                          {"field": "p.code","comparison": "equal", "operation": "OR", "value": "40475" }
     *                        ],
     *               "operation": "OR"
     *           },
     *           {
     *               "fields": [
     *                          { "field": "name", "comparison": "LIKE", "operation": "OR", "value": "Corbin" },
     *                           { "field": "p.code", "comparison": "equal", "operation": "OR", "value": "40475" }
     *                        ],
     *               "operation": "OR"
     *           }
     *       ],
     *       "join": [{
     *                  "field": "postal_codes",
     *                  "alias": "p",
     *                  "type": "LEFT"
     *       }],
     *      "order_by": [
     *                    {
     *                       "field": "name",
     *                       "direction": "ASC"
     *                     }
     *                   ],
     *       "offset":0,
     *       "max_result":50
     *   }
     *
     *
     *
     * @param Request $request
     * @return ISaResponse
     * @throws ApiException
     * @throws Exception
     * @api
     */
    public function advancedQuery(Request $request) : ISaResponse
    {
        if (!$this->enableDefaultApiEndpoints) {
            $this->error501();
        }

        $q = $this->advancedQueryGetQueryBuilder($request);

        $data = $q->getQuery()->getResult();

        if (isset($json['type']) && strtolower($json['type'])=='simple') {
            $dataArray = doctrineUtils::getEntityCollectionArray($data, false);
        } elseif (isset($json['type']) && strtolower($json['type'])=='complex') {
            $dataArray = doctrineUtils::getEntityCollectionArray($data, false, $json['associations']);
        } else {
            $dataArray = doctrineUtils::getEntityCollectionArray($data, true);
        }

        $resRef = ioc::staticResolve('ApiJsonResponse');

        /** @var ApiJsonResponse $res */
        $res = new $resRef(200);
        $res->data['response'] = $dataArray;

        return $res;
    }

    /**
     * @param Request $request
     * @return string
     * @throws ApiException
     * @api
     */
    public function advancedQuerySql(Request $request) : string
    {
        if (!$this->enableDefaultApiEndpoints) {
            $this->error501();
        }

        $q = $this->advancedQueryGetQueryBuilder($request);

        return $q->getQuery()->getSQL();
    }

    /**
     * Simple API Query
     *
     * has no support for OR operations or Where groupings, just a simple query
     *
     * Sample URL
     * api/v1/system/saCity/query?field[name]=corbin&comparison[name]=LIKE&order_by[name]=ASC
     *
     * @param Request $request
     * @return ISaResponse
     * @throws Exception
     * @api
     */
    public function query(Request $request) : ISaResponse
    {
        if (!$this->enableDefaultApiEndpoints) {
            $this->error501();
        }

        $max = !empty($_REQUEST['limit']) ? $_REQUEST['limit'] : 50;
        $offset = !empty($_REQUEST['offset']) ? $_REQUEST['offset'] : 0;

        $q = $this->repo->createQueryBuilder('q');

        if (isset($_REQUEST['field']) && is_array($_REQUEST['field'])) {
            foreach($_REQUEST['field'] as $field=>$value) {

                $aliasedField = 'q.'.$field;

                switch(strtoupper($_REQUEST['comparison'][$field])) {
                    case '!=':
                    case '<>':
                    case 'NOTEQUAL':
                        $q->andWhere($aliasedField.' <> :'.$field);
                        $q->setParameter(':'.$field, $value);
                        break;
                    case 'NULL':
                        $q->andWhere($aliasedField.' IS NULL');
                        break;
                    case '<=':
                    case 'LTE':
                        $q->andWhere($aliasedField.' <= :'.$field);
                        $q->setParameter(':'.$field, $value);
                        break;
                    case '>=':
                    case 'GTE':
                        $q->andWhere($aliasedField.' >= :'.$field);
                        $q->setParameter(':'.$field, $value);
                        break;
                    case 'LIKE':
                        $q->andWhere($aliasedField.' LIKE :'.$field);
                        $q->setParameter(':'.$field, $value);
                        break;
                    case '=':
                    case 'EQUAL':
                    default:
                        $q->andWhere($aliasedField.' = :'.$field);
                        $q->setParameter(':'.$field, $value);
                }
            }
        }

        if (isset($_REQUEST['order_by']) && is_array($_REQUEST['order_by'])) {
            foreach($_REQUEST['order_by'] as $field=>$direction) {
                $q->addOrderBy('q.'.$field, $direction);
            }
        }

        $q->setMaxResults($max);
        $q->setFirstResult($offset);

        $data = $q->getQuery()->getResult();

        if (isset($json['type']) && $json['type']=='simple') {
            $dataArray = doctrineUtils::getEntityCollectionArray($data, false);
        } elseif (isset($json['type']) && $json['type']=='complex') {
            $dataArray = doctrineUtils::getEntityCollectionArray($data, false, $json['associations']);
        } else {
            $dataArray = doctrineUtils::getEntityCollectionArray($data, true);
        }

        $resRef = ioc::staticResolve('ApiJsonResponse');

        /** @var ApiJsonResponse $res */
        $res = new $resRef(200);
        $res->data['response'] = $dataArray;

        return $res;
    }

    /**
     * @param Request $request
     * @return ISaResponse
     */
    public function getServerTime(Request $request) : ISaResponse
    {
        $apiJsonResponseRef = ioc::staticGet('ApiJsonResponse');

        /** @var ApiJsonResponse $return */
        $return = new $apiJsonResponseRef(200);
        $return->data['time'] = time();

        return $return;
    }

    // </editor-fold>

    // <editor-fold desc="API Error Responses">

    /**
     * @return ISaResponse
     */
    public function error401() : ISaResponse
    {
        $apiJsonResponseRef = ioc::staticGet('ApiJsonResponse');

        /** @var ApiJsonResponse $return */
        $return = new $apiJsonResponseRef(401);
        $return->data['success'] = false;

        return $return;
    }

    /**
     * @return ISaResponse
     */
    public function error403() : ISaResponse
    {
        $apiJsonResponseRef = ioc::staticGet('ApiJsonResponse');

        /** @var ApiJsonResponse $return */
        $return = new $apiJsonResponseRef(403);
        $return->data['success'] = false;

        return $return;
    }

    /**
     * @return ISaResponse
     */
    public function error404() : ISaResponse
    {
        $apiJsonResponseRef = ioc::staticGet('ApiJsonResponse');

        /** @var ApiJsonResponse $return */
        $return = new $apiJsonResponseRef(404);
        $return->data['success'] = false;

        return $return;
    }

    /**
     * @param string|null $msg
     * @return ISaResponse
     */
    public function error500($msg = '') : ISaResponse
    {
        $apiJsonResponseRef = ioc::staticGet('ApiJsonResponse');

        /** @var ApiJsonResponse $return */
        $return = new $apiJsonResponseRef(500);
        $return->data['message'] = $msg;
        $return->data['success'] = false;

        return $return;
    }

    /**
     * @param string|null $msg
     * @return ISaResponse
     */
    public function error501(string $msg = null) : ISaResponse
    {
        $apiJsonResponseRef = ioc::staticResolve('ApiJsonResponse');

        $return = new $apiJsonResponseRef(501);
        $return->data['message'] = $msg ?: '501 - Not Implemented';
        $return->data['success'] = false;

        return $return;
    }

    /**
     * @param string|null $msg
     * @return ISaResponse
     */
    public function error400(string $msg = null) : ISaResponse
    {
        $apiJsonResponseRef = ioc::staticGet('ApiJsonResponse');

        $return = new $apiJsonResponseRef(400);
        $return->data['message'] = $msg;
        $return->data['success'] = false;

        return $return;
    }

    /**
     * @return ApiJsonResponse
     */
    public function apiNotFound() : ApiJsonResponse
    {
        $resRef = ioc::staticGet('ApiResponseJson');

        /** @var ApiJsonResponse $res */
        $res = new $resRef(404);
        $res->data['success'] = false;
        $res->data['message'] = 'API endpoint not found.';

        return $res;
    }

    /**
     * @param bool $loginMsg
     * @return ApiJsonResponse
     */
    public function apiNotAllowed(bool $loginMsg = true) : ApiJsonResponse
    {
        $resRef = ioc::staticGet('ApiResponseJson');

        /** @var ApiJsonResponse $res */
        $res = new $resRef(401);
        $res->data['message'] = $loginMsg ? 'You Must login to use this API endpoint.' : 'Not Allowed';
        $res->data['success'] = false;

        return $res;
    }

    /**
     * @return ApiJsonResponse
     */
    public function invalidSession() : ApiJsonResponse
    {
        session_destroy();

        $resRef = ioc::staticGet('ApiResponseJson');

        /** @var ApiJsonResponse $res */
        $res = new $resRef(500);
        $res->data['success'] = false;
        $res->data['message'] = 'The session key provided is invalid.';

        return $res;
    }


    /**
     * @return ApiJsonResponse
     */
    public function apiLoginFailed() : ApiJsonResponse
    {
        $resRef = ioc::staticGet('ApiResponseJson');

        /** @var ApiJsonResponse $res */
        $res = new $resRef(401);
        $res->data['success'] = false;
        $res->data['message'] = 'The login key provided is invalid or expired.';

        return $res;
    }

    // </editor-fold>


    /**
     * TODO : REMOVE
     *
     * @param Event $event
     */
    public static function apiKeyLogin(Event $event)
    {
        $routeInfo = $event->getData('routeInfo');
        $allowed = $event->getData('allowed');

        $api = new api();
        $postData = $api->getJsonPostDataAsArray();

        $loginKey = !empty($_REQUEST['key']) ? $_REQUEST['key'] : ($postData['login_key'] ? $postData['login_key'] : null);

        if (!function_exists('\getallheaders')) {
            return;
        }

        if (empty($loginKey)) {
            $headers = getallheaders();
            $headers = arrayUtils::array_change_key_case_recursive($headers, CASE_LOWER);
            if (isset($headers['login-key'])) {
                $loginKey = $headers['login-key'];
            }
        }

        $parts = url::parts();
        if ($parts[1]=='api' || ($parts[1] == 'siteadmin' && $parts[2] == 'api')) {
            $app = app::getInstance();
            $app->registerWhoopsHandler( new \sacore\application\PlainTextErrorHandler(null, true), true );
        }

        if ($parts[1]=='api' && !empty($loginKey))
        {
            $authName = ioc::staticResolve('auth');
            $auth = $authName::getInstance();
            $result = $auth->logon(null, null, $loginKey, 'api');

            if (!$result) {
                $allowed = false;
                $routeInfo = app::getInstance()->findRouteById('api_login_failed');
            }
        } else if(($parts[1] == 'siteadmin' && $parts[2] == 'api') && !empty($loginKey)) {
            /** @var saAuth $authName */
            $authName = ioc::staticResolve('saAuth');
            /** @var saAuth $auth */
            $auth = $authName::getInstance();

            $result = $auth->login(null, null, $loginKey, 'api');

            if(!$result) {
                $allowed = false;
                $routeInfo = app::getInstance()->findRouteById('api_login_failed');
            }
        }

        $event->setData('routeInfo', $routeInfo);
        $event->setData('allowed', $allowed);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public static function getRequestJsonArrayBody(Request $request) : array
    {
        $content = $request->getContent();

        return json_decode(trim($content), true);
    }
}
