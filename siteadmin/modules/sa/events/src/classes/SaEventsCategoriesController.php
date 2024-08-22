<?php

namespace sa\events;

use sacore\application\app;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\responses\Redirect;
use sacore\application\responses\View;
use sacore\application\saController;
use sacore\application\ValidateException;
use sacore\utilities\doctrineUtils;
use sacore\utilities\notification;

class SaEventsCategoriesController extends saController
{
    /**
     * @throws \sacore\application\Exception
     */
    public function index($request)
    {
        $view = new View('table', $this->viewLocation(), false);
        $perPage = 20;
        $fieldsToSearch = [];

        foreach ($request->query->all() as $field => $value) {
            if (strpos($field, 'q_') === 0 && ! empty($value)) {
                $fieldsToSearch[str_replace('q_', '', $field)] = $value;
            }
        }

        $currentPage = ! empty($request->request->get('page')) ? $request->request->get('page') : 1;
        $sort = ! empty($request->request->get('sort')) ? $request->request->get('sort') : false;
        $sortDir = ! empty($request->request->get('sortDir')) ? $request->request->get('sortDir') : false;

        /** @var CategoryRepository $repo */
        $repo = app::$entityManager->getRepository(ioc::staticResolve('EventsCategory'));
        $orderBy = ($sort) ? [$sort => $sortDir] : null;
        $data = $repo->search($fieldsToSearch, $orderBy, $perPage, (($currentPage - 1) * $perPage));
        $totalRecords = count($repo->findAll());
        $totalPages = ceil($totalRecords / $perPage);
        $view->data['table'][] = [
            'header' => [
                ['name' => 'Name', 'class' => '', 'sort' => 'name'],
                ['name' => 'Description', 'class' => '', 'sort' => 'description'],
            ],
            'actions' => [
                'edit' => [
                    'name' => 'Edit',
                    'routeid' => 'sa_events_category_edit',
                    'params' => ['id'],
                ],
                'delete' => [
                    'name' => 'Delete',
                    'routeid' => 'sa_events_category_delete',
                    'params' => ['id'],
                ],
            ],
            'noDataMessage' => 'No Categories Available',
            'map' => ['name', 'description'],
            'tableCreateRoute' => 'sa_events_category_create',
            'data' => doctrineUtils::convertEntityToArray($data),
            'totalRecords' => $totalRecords,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
        ];

        return $view;
    }

    /**
     * Displays the create/edit page for a Category.
     *
     * @param  int  $id - The ID of a EventsCategory object.
     * @param     new Category $category
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function edit($request)
    {
        $id = $request->getRouteParams()->get('id');
        $category = $request->getRouteParams()->get('category');
        if (empty($category)) {
            if ($id == 0) {
                $category = ioc::resolve('EventsCategory');
            } else {
                $category = app::$entityManager->find(ioc::staticResolve('EventsCategory'), $id);
            }
        }

        $view = new View('sa_category_edit_view', $this->viewLocation(), false);
        $view->data['category'] = $category;
        $view->data['access_groups'] = $category->getAccessGroups();
        $view->data['groups'] = modRequest::request('sa.member.get_all_groups', []);

        return $view;
    }

    /**
     * Saves new Category object.
     *
     * @throws \Exception
     */
    public function save($request)
    {
        /** @var Category $EventsCategory */
        $category = null;
        $notify = new notification();

        // The name ALL is used to get all categories in the EventsElementController
        if (strtoupper($request->request->get('name')) == 'ALL') {
            $notify->addNotification('danger', 'Error', 'That name is reserved.');

            if (! empty($request->request->get('id'))) {
                return new Redirect(app::get()->getRouter()->generate('sa_events_category_edit', ['id' => $request->request->get('id')]));
            } else {
                return new Redirect(app::get()->getRouter()->generate('sa_events_category_create'));
            }
        }

        if (! empty($request->request->get('id'))) {
            $category = app::$entityManager->getRepository(ioc::staticGet('EventsCategory'))
                ->findOneBy(['id' => $request->request->get('id')]);
        }

        if (empty($category)) {
            $category = ioc::resolve('EventsCategory');
        }

        $category->setName($request->request->get('name'));
        $category->setDescription($request->request->get('description'));
        $category->setAccessGroups($request->request->get('access_groups'));
        $notify = new notification();

        try {
            app::$entityManager->persist($category);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Category saved successfully.');

            return new Redirect(app::get()->getRouter()->generate('sa_events_categories'));
        } catch(ValidateException $e) {
            $notify->addNotification('danger', 'Error', $e->getMessage());

            return $this->edit($category->getId(), $category);
        }
    }

    /**
     * Deletes a Category object.
     *
     * @param $id - ID of a EventsCategory.
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     */
    public function delete($request)
    {
        $id = $request->getRouteParams()->get('id');
        $category = app::$entityManager->find(ioc::staticResolve('EventsCategory'), $id);
        $notify = new notification();

        try {
            app::$entityManager->remove($category);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'Category deleted successfully.');

            return new Redirect(app::get()->getRouter()->get('sa_events_categories'));
        } catch(ValidateException $e) {
            $notify->addNotification('danger', 'Error', $e->getMessage());

            return new Redirect(app::get()->getRouter()->get('member_sa_group'));
        }
    }
}
