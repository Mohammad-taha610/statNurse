<?php

namespace sa\api;

use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use \sacore\application\app;
use sacore\application\Exception;
use \sacore\application\ioc;
use sacore\application\IocDuplicateClassException;
use sacore\application\IocException;
use sacore\application\Request;
use sacore\application\responses\ISaResponse;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use \sacore\application\saController;
use \sacore\utilities\doctrineUtils;
use \sacore\application\ValidateException;
use \sacore\utilities\notification;
use \Doctrine\DBAL\Exception\NotNullConstraintViolationException;

/**
 * Class SaManageApiKeysController
 * @package sa\api
 */
class SaManageApiKeysController extends saController
{
    /**
     * @throws Exception
     */
    public function apiKeyIndex() : ISaResponse
    {
        /** @var apiKeyRepository $apiKeyRepo */
        $apiKeyRepo = ioc::getRepository('ApiKey');

        $view = new View('table', static::viewLocation());

        $perPage = $_REQUEST['limit'] ?? 20;
        $fieldsToSearch= [];

        foreach($_GET as $field=>$value) {
            if (strpos($field, 'q_')===0 && !empty($value)) {
                $fieldsToSearch[ str_replace("q_", "", $field) ] = $value;
            }
        }

        $currentPage = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        $sort = !empty($_REQUEST['sort']) ? $_REQUEST['sort'] : false;
        $sortDir = !empty($_REQUEST['sortDir']) ? $_REQUEST['sortDir'] : false;
        $orderBy = null;

        if ($sort) {
            $orderBy = array($sort => $sortDir);
        }

        $totalRecords = $apiKeyRepo->search($fieldsToSearch, false, false, false, true);
        $data = $apiKeyRepo->search($fieldsToSearch, $orderBy, $perPage, (($currentPage-1)*$perPage));
        $totalPages = ceil($totalRecords / $perPage);

        $view->data['table'][] = [
            /* SET THE HEADER OF THE TABLE UP */
            'header' => [
                ['name' => 'Client Identifier', 'class' => ''],
                ['name' => 'Api Key', 'class' => ''],
                ['name' => 'Platform', 'class' => ''],
                ['name' => 'Is Active', 'class' => '']
            ],
            /* SET ACTIONS ON EVERY ROW */
            'actions' => [
                'edit' => ['name' => 'Edit', 'routeid' => 'api_v1_key_mgmt_edit', 'params' => ['id']],
                'delete' => ['name' => 'Delete', 'routeid' => 'api_v1_key_mgmt_delete', 'params' => ['id']]
            ],
            /* SET THE NO DATA MESSAGE */
            'noDataMessage' => 'There are no API keys available for this site',
            /* SET THE DATA MAP */
            'map' => [
                'client_id',
                'api_key',
                'platform',
                'is_active'
            ],
            /* SET THE ACTION FOR THE HEADER CREATE BUTTON */
            'tableCreateRoute' => 'api_v1_key_mgmt_add',
            /* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
            'data' => doctrineUtils::getEntityCollectionArray($data),
            /* SET THE TOTAL RECORDS, ETC... FOR PAGINATION */
            'totalRecords' => $totalRecords,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
            'searchable' => false,
            'dataRowCallback' => function($data) {
                return $data;
            }
        ];

        return $view;
    }


    /*
    * apiKeyShow
    *
    * Added using SA3 Code gen Restful routes
    */
    public function apiKeyShow($id)
    {

    }

    /**
     * @throws IocDuplicateClassException
     * @throws Exception
     * @throws IocException
     * @param Request $request
     */
    public function apiKeyShowAdd($request) : ISaResponse
    {
        $request->getRouteParams()->set('id', 0);
        return $this->apiKeyShowEdit($request);
    }

    /**
     * apiKeyShowEdit
     * Show ApiKey
     *
     * @param Request $request
     * @return ISaResponse
     * @throws Exception
     * @throws IocDuplicateClassException
     * @throws IocException
     */
    public function apiKeyShowEdit(Request $request) : ISaResponse
    {
        $apiKeyId = $request->getRouteParams()->get('id');

        /** @var apiKeyRepository $apiKeyRepo */
        $apiKeyRepo = ioc::getRepository('ApiKey');
        /** @var ApiKey $apiKey */
        $apiKey = $apiKeyRepo->findOneBy(['id' => $apiKeyId]);
        $view = new View('api_key_edit', static::viewLocation());

        if ($apiKey) {
            $mData = doctrineUtils::getEntityArray($apiKey);

            if ( method_exists($apiKey, "getImage") && $apiKey->getImage()) {
                $mData['image_id'] = $apiKey->getImage()->getId();
                $mData['image_path'] = url::make('files_browser_view_file', $apiKey->getImage()->getFolder(), $apiKey->getImage()->getFilename());
            }

            $view->data = array_merge($view->data, $mData);
        } else {
            $apiKey = ioc::resolve('ApiKey');
        }

        // TODO : FIX THIS
        // if ($passData) {
        //     $view->data = array_merge($view->data, $passData);
        // }

        $view->data['entity_scope_options'] = self::getDoctrineManagedEntities();

        $view->data['apiKey'] = doctrineUtils::getEntityArray($apiKey);
        $view->data['id'] = $apiKeyId;
        $view->data['apiKey']['entity_scope'] = $apiKey->getEntityScope();

        // Add entity scope to view data
        return $view;
    }

    /**
     * Simple function, just returns the IoC registered
     * classes as an array
     *
     * @return array
     */
	private function getDoctrineManagedEntities() : array
    {
        $entities = array();
        $meta = app::$entityManager->getMetadataFactory()->getAllMetadata();

        foreach ($meta as $m) {
            $entityName = '\\' . $m->getName();
            $entities[] = $entityName;
            unset($class);
            unset($reflectionClass);
        }

        return $entities;
    }

    /**
     * apiKeySaveEdit
     * Save ApiKey
     *
     * @param Request $request
     * @return ISaResponse
     * @throws Exception
     * @throws IocDuplicateClassException
     * @throws IocException
     * @throws MappingException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function apiKeySaveEdit(Request $request) : ISaResponse
    {
        /** @var apiKeyRepository $apiKeyRepo */
        $apiKeyRepo = ioc::getRepository('ApiKey');

        $id = $request->get('id');

        $apikey = $apiKeyRepo->findOneBy(array('id' => $id));

        /** @var ApiKey $group */
        if (!$apikey) {
            $apikey = ioc::resolve('ApiKey');
        } else {
            if($apikey->getClientId() != $_POST['client_id']) {
                $apikey->setApiKey($apiKeyRepo->generateApiKey($apikey->getClientId()));
            }
        }

        $notify = new notification();

        /** @var ApiKey $apikey */
        $apikey = doctrineUtils::setEntityData($_POST, $apikey);
        try {
            if(!empty($_POST['entityScope'])) {
                $apikey->setEntityScope($_POST['entityScope']);
            }

            if(!$apikey->getApiKey()) {
                $apikey->setApiKey($apiKeyRepo->generateApiKey($apikey->getClientId()));
            }

            app::$entityManager->persist($apikey);
            app::$entityManager->flush();

            $notify->addNotification('success', 'Success', 'Record saved successfully.');

            return new Redirect(app::get()->getRouter()->generate('api_v1_key_mgmt_index'));
        } catch (ValidateException $e) {
            $notify->addNotification("danger", "Error", "An error occurred while saving your changes. <br />". $e->getMessage());

            return $this->apiKeyShowEdit($apikey, $_POST);
        } catch (NotNullConstraintViolationException $e) {
            $notify->addNotification("danger", "Error", "An error occurred while saving your changes. Please contact your web administrator.<br />");

            return $this->apiKeyShowEdit($apikey, $_POST);
        }
    }

    /**
     * @param Request $request
     * @return ISaResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function apiKeyDelete(Request $request) : ISaResponse
    {
        /** @var apiKeyRepository $apiKeyRepo */
        $apiKeyRepo = ioc::getRepository('ApiKey');

        /** @var ApiKey $apiKey */
        $apiKey = $apiKeyRepo->find($request->getRouteParams()->get('id'));

        $notify = new notification();

        try {
            app::$entityManager->remove($apiKey);
            app::$entityManager->flush();

            $notify->addNotification("success", "Success", "Record deleted successfully.");

            return new Redirect(app::get()->getRouter()->generate('api_v1_key_mgmt_index'));
        } catch (ValidateException $e) {
            $notify->addNotification('danger', 'Error', 'An error occurred while saving your changes. <br />'. $e->getMessage());

            return new Redirect(app::get()->getRouter()->generate('api_v1_key_mgmt_index'));
        }
    }
}