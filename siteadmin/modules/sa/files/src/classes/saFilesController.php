<?php

namespace sa\files;

use \sacore\application\app;
use sacore\application\ioc;
use sacore\application\responses\Redirect;
use \sacore\application\saController;
use \sacore\application\saRoute;
use \sacore\application\navItem;
use \sacore\application\modelResult;
use sacore\application\ValidateException;
use \sacore\application\responses\View;
use sa\system\AssetBuildManager;
use \sacore\utilities\url;
use \sacore\utilities\notification;

class saFilesController extends saController {

    /**
     * @param AssetBuildManager $buildManager
     * @return mixed
     */
    public static function modRequestAdditionalCache($buildManager) {

        $qb = ioc::getRepository('saFile')->createQueryBuilder('f');
        $iterableResult = $qb->getQuery()->iterate();

        /** @var saFile $file */
        foreach ($iterableResult as $file) {

            $file = $file[0];

            $buildManager->cacheFile($file->getPath(), '/assets/files/'.$file->getFolder().'/'.$file->getFilename());
            $buildManager->cacheFile($file->getPath(), '/assets/files/id/'.$file->getId());
            
            // CLEAR FROM ENTITY MANAGER TO HELP CONSERVE MEMORY
            app::$entityManager->detach($file);
        }

        return $buildManager;

    }

	/**
	 * @param static saFile $saFile
     */
	public function manageFiles($request) {

		$view = new View('table', $this->viewLocation(), false );

		$perPage = 20;
		$fieldsToSearch=array();
		foreach($request->query->all() as $field=>$value)
		{
			if (strpos($field, 'q_')===0 && !empty($value))
			{
				$fieldsToSearch[ str_replace('q_', '', $field) ] = $value;
			}
		}
		$currentPage = !empty($request->get('page')) ? $request->get('page') : 1;
		$sort = !empty($request->get('sort')) ? $request->get('sort') : false;
		$sortDir = !empty($request->get('sortDir')) ? $request->get('sortDir') : false;
		//$totalRecords = saFile::findByFields($fieldsToSearch, false, false, false, $sort, $sortDir)->length;
		//$data = saFile::findByFields($fieldsToSearch, false, $perPage, (($currentPage-1)*$perPage), $sort, $sortDir)->toArray();
        $orderBy = null;
        if ($sort) {
            $orderBy = array($sort => $sortDir);
        }

		/** @var saFileRepository $fileRepo */
//        This was the way it was when I found it, not sure if I am missing anything but that variable is the entire request
//		$fileRepo = ioc::getRepository($saFile);
		$fileRepo = ioc::getRepository('saFile');

        $totalRecords = $fileRepo->search($fieldsToSearch, null, null, null, true);
        $data = $fileRepo->search($fieldsToSearch, $orderBy, $perPage, (($currentPage-1)*$perPage));

		$totalPages = ceil($totalRecords / $perPage);

		$view->data['table'][] = array( 

			/* SET THE HEADER OF THE TABLE UP */
			'header'=>array( array('name'=>'Folder', 'class'=>''),array('name'=>'Filename', 'class'=>''),array('name'=>'Date Created', 'class'=>''), ),
			/* SET ACTIONS ON EVERY ROW */
			'actions'=>array( 'show'=>array( 'name'=>'Show', 'icon'=>'search', 'routeid'=>'files_browser_view_file_by_id', 'params'=>array('id') ), 'delete'=>array( 'name'=>'Delete', 'routeid'=>'sa_files_delete', 'params'=>array('id') ), ),
			/* SET THE NO DATA MESSAGE */
			'noDataMessage'=>'No Uploads Available',
			/* SET THE DATA MAP */
			'map'=>array( 'folder','filename','date_created', ),
			/* SET THE ACTION FOR THE HEADER CREATE BUTTON */
			//'tableCreateRoute'=>'sa_uploads_create',
			/* IS THE TABLE SORTABLE? */
			'sortable'=> true,
			/* IS THE TABLE SEARCHABLE? */
			'searchable'=> true,
			/* SET THE TOTAL RECORDS, ETC... FOR PAGINATION */
			'totalRecords'=> $totalRecords,
			'totalPages'=> $totalPages,
			'currentPage'=> $currentPage,
			'perPage'=> $perPage,
			/* SET THE DATA FOR THE ROWS, THIS CAN BE AN ARRAY OR AN ASSOCIATIVE ROW OR OBJECT (ASSCO. ROWS/OBJECT REQUIRE A DATA MAP) */
			'data'=> $data
		);

		return $view;
	}


	/**
	 * @param saFile $file
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 * @throws \Exception
     */
	public function deleteFiles($request) {

        $notify = new notification();
        $file = ioc::getRepository('saFile')->find($request->getRouteParams()->get('id'));

        try {
            app::$entityManager->remove($file);
            app::$entityManager->flush();
            $notify->addNotification('success', 'Success', 'File deleted successfully.');
            return new Redirect( app::get()->getRouter()->generate('sa_files') );
        }
		catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException $e) {

			$notify->addNotification('danger', 'Error', 'An error occured while deleting that file. <br /> The file is in use.');
			return new Redirect( app::get()->getRouter()->generate('sa_files') );
		}
        catch (ValidateException $e) {

            $notify->addNotification('danger', 'Error', 'An error occured while saving your changes. <br />'. $e->getMessage());
            return new Redirect( app::get()->getRouter()->generate('sa_files'));
        }
	}

	public function CMSHeaderWidget($html = '') 
    {
        $view = new View( 'cms_header_widget', static::viewLocation());
        
        return $html . $view->getHTML();
    }
}