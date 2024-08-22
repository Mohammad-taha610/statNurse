<?php
namespace sa\system;
use Doctrine\Common\Collections\ArrayCollection;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use \sacore\application\app;
use \sacore\application\assetView;
use \sacore\application\controller;
use sacore\application\Exception;
use sacore\application\ioc;
use sacore\application\IocDuplicateClassException;
use sacore\application\IocException;
use sacore\application\jsonView;
use \sacore\application\model;
use sacore\application\modRequest;
use sacore\application\Request;
use sacore\application\responses\Json;
use sacore\application\responses\View;
use sacore\application\route;
use sacore\application\Thread;
use sacore\application\ThreadConfig;
//use \sacore\application\view;
use sacore\application\xmlView;
use sa\messages\saEmail;
use sacore\utilities\doctrineUtils;
use Doctrine\ORM\Events;
use sacore\utilities\url;
use sacore\application\DateTime;
use sacore\application\ViewException;

class systemController extends controller
{

    public static function checkSSLDomainRedirects(\sacore\application\Event $event) {

        $config = app::get()->getConfiguration();

        $routeInfo = $event->getData('routeInfo');

        $needsRedirect = false;
        $redirectLocation = null;

        $host = url::host();
        $uri = url::uri();
        $protocol = app::get()->getConfiguration()->get('require_ssl')->getValue() ? 'https://' : 'http://';

        $isSSL = false;
        if ( $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || app::getInstance()->isCommandLineStarted() || $_SERVER['SERVER_PORT'] == '443' || !empty($_SERVER['UNITTESTING']) || $routeInfo->bypass_ssl || app::getInstance()->isCommandLineStarted()) {
            $isSSL = true;
        }

        if (!$isSSL && app::get()->getConfiguration()->get('require_ssl')->getValue())
            $needsRedirect = true;


        $excludedDomainRedirects = array();
        if ($config->get('sharding_domains_enabled')->getValue()) {
            $excludedDomainRedirects = explode(',', $config->get('sharding_domains', true)->getValue() );
        }

        if (!in_array($host, $excludedDomainRedirects)) {

            $redirect_domain_parts = app::get()->getConfiguration()->get('require_ssl')->getValue() ? parse_url(app::get()->getConfiguration()->get('secure_site_url')->getValue()) : parse_url(app::get()->getConfiguration()->get('site_url')->getValue());
            $redirect_domain = $redirect_domain_parts['host'];
            if (app::get()->getConfiguration()->get('force_main_domain_redirect')->getValue() && $redirect_domain != $host && !app::getInstance()->isCommandLineStarted()) {
                $host = $redirect_domain;
                $needsRedirect = true;
            }

            if (app::get()->getConfiguration()->get('force_www_redirect')->getValue() && strpos($host, 'www.') === false && app::getInstance()->isCommandLineStarted() === false) {
                $host = 'www.' . $host;
                $needsRedirect = true;
            }

        }


        $redirectLocation = $protocol.$host.$uri;

        if ($needsRedirect) {
            $routeInfo = new route(array('forward_to_route' => $redirectLocation, 'permanent_forward' => true));
            $event->setData('routeInfo', $routeInfo);
        }

        $event->setData('hasRedirect', $needsRedirect);
    }

    /**
     * @param $event \sacore\application\Event
     */
    public static function checkBruteForceIP($event) {
        

        if ( !BruteForceManager::isTrusted() )
        { 
            $routeInfo = new route( array('controller' => 'systemController@error403') );
            $event->setData('routeInfo', $routeInfo);
        }


    }

    public static function checkWWWRedirect(\sacore\application\Event $event) {

        if (!app::get()->getConfiguration()->get('force_www_redirect')->getValue())
            return;

        $parse = parse_url(app::get()->getConfiguration()->get('site_url')->getValue());
        $redirect_host = $parse['host'];
        $redirect_host = str_replace('www.', '', $redirect_host);

        $host = url::host();
        $protocol = url::protocol();
        $uri = url::uri();

        if ( strpos($host, 'www.')===false && app::getInstance()->isCommandLineStarted() === false ) {

            $routeInfo = new route(array('forward_to_route'=>$protocol."www.".$redirect_host.$uri, 'permanent_forward'=>true) );
            $event->setData('routeInfo', $routeInfo);

        }

    }


    public function getSpriteBuildCSS() {

//        if (!file_exists(\config::public_directory . '/build/combined/sprite.css')) {
//            static::buildSpriteCSS();
//        }

        $view = new assetView(false, '/build/combined/sprite.css');
        return $view;

    }



    /**
     * Displays the requested resource
     *
     * @param   string file
     * @param   string folder, if not otherwise in top level
     * @return  Json
     */
    public static function ping($request)
    {
        $data = $request->request;
        if (!in_array($data->get('client_timezone'), timezone_identifiers_list())) {
            $data->set('client_timezone','America/New_York');
        }

    	try {
        	$timezone = new \DateTimeZone( $data->get('client_timezone') );
    	}
    	catch(\Exception $e) {
			$timezone = new \DateTimeZone( 'America/New_York' );
            $data->set('client_timezone','America/New_York');
    	}


        //I don't know where offset would be coming from so it commented out for now, if it wrong uncomment this
//        $users = ioc::getRepository('OnlineUser')->findBy(array('ip_country'=>'US', 'ip_state'=>''), null, 200, $offset);
        $users = ioc::getRepository('OnlineUser')->findBy(array('ip_country'=>'US', 'ip_state'=>''), null, 200);


        app::getInstance()->setAppTimeZone($timezone);

        $dataToSend = $data;
        $dataToSend->set('ip',static::get_client_ip());
        $dataToSend->set('machine_id',saAuth::getMachineUUID());

        modRequest::request('auth.ping', true, $dataToSend);

        static::recordCurrentOnlineUser(
            $dataToSend->get('first_ping'),
            $dataToSend->get('mouse_activity'),
            $dataToSend->get('user_agent'),
            $dataToSend->get('url'),
            static::get_client_ip()
        );

        $view = new Json();
        $view->data = array('result'=>true, 'local_timezone'=>$dataToSend->get('client_timezone') );
        return $view;
    }

    // Function to get the client IP address
    public static function get_client_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';

        return $ipaddress;
    }

    public static function recordCurrentOnlineUser($was_first_ping, $had_mouse_activity, $user_agent, $url, $ip)
    {
        $was_first_ping = $was_first_ping=='true' ? true : false;
        $had_mouse_activity = $had_mouse_activity=='true' ? true : false;

        $visitDate = new DateTime();
        $minVisitDate = new DateTime();
        $minVisitDate->setTime(0, 0, 0);
        $maxVisitDate2 = new DateTime();
        $maxVisitDate2->setTime(23, 59, 59);

        try {
            /** @var OnlineUser $online_user */
            $qb = ioc::getRepository('OnlineUser')->createQueryBuilder('o');
            $qb->where('o.machineId=:id AND o.last_visit_date>=:date1 and o.last_visit_date<=:date2');
            $qb->setParameter(':id', saAuth::getMachineUUID());
            $qb->setParameter(':date1', $minVisitDate);
            $qb->setParameter(':date2', $maxVisitDate2);
            $qb->setMaxResults(1);
            $online_user = $qb->getQuery()->getOneOrNullResult();

            if(!$online_user) {
                $online_user = ioc::resolve('OnlineUser');
                $online_user->setMachineId(saAuth::getMachineUUID());
                $online_user->setViewCount(0);
                $online_user->setFirstPage($url);
                try {
                    $reader = new Reader(app::get()->getConfiguration()->get('geo_ip_database_path')->getValue());

                    $record = $reader->city(static::get_client_ip());
                    $online_user->setIpCountry($record->country->isoCode);
                    $online_user->setIpState($record->mostSpecificSubdivision->isoCode);
                    $online_user->setIpCity($record->city->name);
                    $online_user->setIpLatitude($record->location->latitude);
                    $online_user->setIpLongitude($record->location->longitude);
                    $online_user->setIpCode($record->postal->code);
                }
                catch(\Exception $e) {

                }
            }


            $online_user->setLastVisitDate($visitDate);
            $online_user->setLastPage($url);
            $online_user->setIpAddress($ip);
            $online_user->setWasIdle(!$had_mouse_activity);
            $online_user->setWasPageLoad($was_first_ping);
            $online_user->setUserAgent($user_agent);

            if ($was_first_ping) {
                $online_user->setViewCount($online_user->getViewCount() + 1);
            }

            app::$entityManager->persist($online_user);
            app::$entityManager->flush($online_user);
        }
        catch(\Exception $e) {
            //echo $e->getMessage();
            // Continue without issue
        }
    }

    /**
     * @return array|void
     */
    public static function getCountryInfo() : ?array
    {
        $countries = ioc::getRepository('saCountry')->findAll();
        
        if($countries) {
            return doctrineUtils::getEntityCollectionArray($countries);
        }
    }

    /**
     * @param $data
     * @return array|null
     * @throws IocDuplicateClassException
     * @throws IocException
     */
    public static function getStatesInfo($data) : ?array
    {
        /** @var saPostalCode $postal_code */
        if($data['country']) {
            $country = ioc::get('saCountry', array('id'=>$data['country']) );
            $postal_codes = app::$entityManager->getRepository(ioc::staticResolve('saState'))->findBy(array('country' => $country ));

            if($postal_codes) {
                return doctrineUtils::getEntityCollectionArray($postal_codes);
            }
        }

        return null;
    }

    /**
     * Register for Revision Updates
     * on all entities in the system
     * @param array $data - 
     *     Accepted keys: 
     *         'trackedEntities': array of class names
     */
    public static function registerRevisionUpdates($data = array()) {
        $revisionManager = ioc::staticGet('DoctrineRevisionManager');
        $doctrineRevisionManager = new $revisionManager($data);
        app::getInstance()->registerDoctrineEventListener(self::getRevisionEvents(), $doctrineRevisionManager);
    }

    /**
     * De-Register for Revision updates
     *
     * Likely necessary for large persistence events
     * such as data imports, etc.
     */
    public static function cancelRevisionUpdates() {
        app::getInstance()->removeDoctrineEventListener(self::getRevisionEvents(), new DoctrineRevisionManager());
    }

    /**
     * Displays the requested resource
     *
     * @param Request $request
     * @return \sacore\application\responses\File|\sacore\application\responses\View|string
     */
    public function themeResource($request)
    {

        $path = $request->getPathInfo();
        $resources = explode('/', $path);

        unset($resources[0]);
        if ($resources[1]=='build') {
            unset($resources[1]);
        }

        $folder = '';
        $file = array_pop($resources);
        $folder = implode('/', $resources);

        try {
            $view = new \sacore\application\responses\File(app::getAppPath() . '/' . $folder . '/' . $file);
        } catch(ViewException $e) {
            $view = $this->error404(true);
        }
        
        return $view;
    }

    public function componentResource($request)
    {
        $path = $request->getPathInfo();
        $resources = explode('/', $path);

        unset($resources[0]);
        if ($resources[1]=='build') {
            unset($resources[1]);
        }

        $folder = '';
        $file = array_pop($resources);
        $folder = implode('/', $resources);

        try {
            $view = new \sacore\application\responses\File(app::getAppPath() . '/' . $folder . '/' . $file);
        } catch(ViewException $e) {
            $view = $this->error404(true);
        }
        
        return $view;
    }

    public function systemResource($request)
    {
        $path = $request->getPathInfo();
        $resources = explode('/', $path);

        $folder = '';
        $file = array_pop($resources);
        $folder = implode('/', $resources);

        try {
            $view = new \sacore\application\responses\File(self::moduleLocation().'/components/' . $file);
        } catch(ViewException $e) {
            $view = $this->error404(true);
        }
        
        return $view;
    }

    public function vendorResource($request)
    {
        $path = $request->getPathInfo();
        $resources = explode('/', $path);

        unset($resources[0]);
        if ($resources[1]=='build') {
            unset($resources[1]);
        }

        $folder = '';
        $file = array_pop($resources);
        $folder = implode('/', $resources);

        try {
            $view = new \sacore\application\responses\File(app::getAppPath() . '/' . $folder . '/' . $file);
        } catch(ViewException $e) {
            $view = $this->error404(true);
        }
        
        return $view;
    }


    /**
     * import location data
     *
     * Describe your function here
     */
    public function importLocationData($request) {
        modrequest::request('system.cancelRevisionUpdates');
    
        if(empty($request->get('countries'))) {
            echo 'Please Specify countries to import, Example below:<br>';
            echo '/siteadmin/system/import_location_data?countries[]=us&countries[]=ca';
            exit;
        }

        $countriesToImport = array_map('strtolower', $request->get('countries'));

        set_time_limit(1200);

        $location = self::moduleLocation();

        $pattern = $location.'/other/location_data_{';

        foreach($countriesToImport as $country) {
            $pattern .= $country . ',';
        }

        $pattern .= '}.txt';
        $files_to_import = glob($pattern, GLOB_BRACE);


        app::$entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

        foreach($files_to_import as $file) {

            $this->importCountryStateCounty($file);
            app::$entityManager->flush();
            app::$entityManager->clear();
            $this->importCityPostalCode($file);
            app::$entityManager->flush();
            app::$entityManager->clear();
        }


    }

    private function importCityPostalCode($file)
    {
        /** @var ArrayCollection $counties */
        $counties = app::$entityManager->getRepository(ioc::staticResolve('saCounty'))->findAll();

        $fp = fopen($file, 'r');
        $cache = array();
        $postalcodes = array();
        $loop = 0;
        while (!feof($fp)) {
            $line = fgets($fp);
            $data = str_getcsv($line, "\t");
            $data = array_map('trim', $data);

            echo '<pre>' . print_r($data, true) . '</pre>';

            if (empty($data[0]))
                continue;


            $county_name = $data[5];
            $state_name = $data[3];
            $county = null;
            foreach($counties as $countyTest) {
                if ($countyTest->getName()==$county_name && $countyTest->getState()->getName()==$state_name) {
                    $county = $countyTest;
                    break;
                }
            }

            if (empty( $cache[$data[5]][ $data[2] ])) {
                $city = app::$entityManager->getRepository(ioc::staticResolve('saCity'))->findOneBy(array('name' => $data[2], 'county'=>$county));
                if (!$city) {
                    /** @var saCity $city */
                    $city = ioc::resolve('saCity');
                    $city->setCounty($county);
                    $city->setName($data[2]);

                    $state = app::$entityManager->getRepository( ioc::staticResolve('saState') )->findOneBy(array( 'name'=> $data[3]));

                    if($state) {
                        $city->setState($state);
                    }

                    app::$entityManager->persist($city);
                }
                $cache[$data[5]][ $data[2] ] = $city;
            }
            else
            {
                $city = $cache[$data[5]][ $data[2] ];
            }

            $code = app::$entityManager->getRepository(ioc::staticResolve('saPostalCode'))->findOneBy(array('code' => $data[1] ));
            if (!$code) {
                /** @var saPostalCode $code */
                $code = ioc::resolve('saPostalCode');
                app::$entityManager->persist($code);
                $code->setCity($city);
                $code->setCode($data[1]);
                $code->setLatitude($data[9]);
                $code->setLongitude($data[10]);

                $state = app::$entityManager->getRepository( ioc::staticResolve('saState') )->findOneBy( array('name' => $data[3]) );

                if($state) {
                    $code->setState($state);
                }
            }


            $postalcodes[] = $code;

            $loop++;

            if ($loop > 2000) {
                app::$entityManager->flush();

                foreach($cache as $county)
                {
                    foreach($county as $city)
                    {
                        app::$entityManager->detach($city);
                    }
                }

                foreach($postalcodes as $pc) {
                    app::$entityManager->detach($pc);
                }

                $cache = array();
                $loop = 0;
            }
        }
    }

    private function importCountryStateCounty($file) {
        $fp = fopen($file, 'r');

        $cache = array( 'countries'=>array(), 'states'=>array(), 'counties'=>array() );

        $loop = 0;
        while (!feof($fp)) {
            $line = fgets($fp);
            $data = str_getcsv($line, "\t");
            $data = array_map('trim', $data);

            $data[5] = preg_replace('/\sCounty$/i','', $data[5]);

            echo '<pre>'.print_r($data, true).'</pre>';

            if (empty($data[0]))
                continue;

            if (empty($cache['countries'][ $data[0] ])) {

                $country = app::$entityManager->getRepository(ioc::staticResolve('saCountry'))->findOneBy(array('abbreviation' => $data[0]));
                if (!$country) {
                    /** @var saCountry $country */
                    $country = ioc::resolve('saCountry');
                    $country->setAbbreviation($data[0]);
                    app::$entityManager->persist($country);
                }
                $cache['countries'][ $data[0] ] = $country;

            }
            else
            {
                $country = $cache['countries'][ $data[0] ];
            }

            if (empty($cache['states'][ $data[0] ][ $data[3] ])) {

                $state = app::$entityManager->getRepository( ioc::staticResolve('saState') )->findOneBy( array('name'=>$data[3], 'country'=>$country ) );
                if (!$state)
                {
                    /** @var saState $state */
                    $state = ioc::resolve('saState');
                    $state->setAbbreviation($data[4]);
                    $state->setName($data[3]);
                    $state->setCountry($country);
                    $country->addState($state);
                    app::$entityManager->persist($state);
                }
                $cache['states'][ $data[0] ][ $data[3] ] = $state;

            }
            else
            {
                $state = $cache['states'][ $data[0] ][ $data[3] ];
            }


            if (empty($cache['counties'][ $data[0] ][ $data[3] ][ $data[5] ])) {

                $county = app::$entityManager->getRepository( ioc::staticResolve('saCounty') )->findOneBy( array('name'=>$data[5], 'state'=>$state ) );
                if (!$county)
                {
                    /** @var saCounty $county */
                    $county = ioc::resolve('saCounty');
                    $county->setName($data[5]);
                    $county->setState($state);
                    app::$entityManager->persist($county);
                }
                $cache['counties'][ $data[0] ][ $data[3] ][ $data[5] ] = $county;

            }
            else
            {
                $county = $cache['counties'][ $data[0] ][ $data[3] ][ $data[5] ];
            }

            $loop++;

            if ($loop>1000)
            {
                app::$entityManager->flush();
                app::$entityManager->clear();
                $cache = array( 'countries'=>array(), 'states'=>array(), 'counties'=>array() );
                $loop = 0;
            }
        }

        fclose($fp);
    }

    public function robots() {

        $view = new \sacore\application\responses\Raw(200, 'text/plain');
        $is_indexable = app::get()->getConfiguration()->get('site_robot_indexable')->getValue();
        if ($is_indexable) {
            $view->setResponse( 'User-agent: *
Allow: /
Disallow: /cgi-bin/
SITEMAP: ' . app::get()->getConfiguration()->get('site_url')->getValue() . '/sitemap.xml');

        }
        else
        {
            $view->setResponse(  'User-agent: *
Disallow: /' );
        }

        return $view;
    }

    public function sitemapXML()
    {
        $view = new xmlView('urlset', 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"');
        $view->data = array();
        $sitemap = modRequest::request('system.sitemap');


        $xmldata = array();

        $count = 0;
        foreach($sitemap as $i=>$module) {

			foreach($module as $l=>$link) {

                $domain = (app::get()->getConfiguration()->get('require_ssl')->getValue() ? app::get()->getConfiguration()->get('secure_site_url')->getValue() : app::get()->getConfiguration()->get('site_url')->getValue());

                if(substr($domain,-1) != '/' && substr($link['loc'],0,1) != '/') {
                    $url = $domain . '/' . $link['loc'];
                }
                else {
                    $url = $domain . $link['loc'];
                }

                $xmldata['url#'.$count] = array(
                    'loc'=>$url
                );

                if ($link['lastmod']) {
                    $xmldata['url#'.$count]['lastmod'] = $link['lastmod'];
                }

                if ($link['changefreq']) {
                    $xmldata['url#'.$count]['changefreq'] = $link['changefreq'];
                }

                $count++;

            }
        }


		$view->data = $xmldata;


        $view->display();
    }

    public function sitemapJSON()
    {
        $view = new Json();
        $sitemap = modRequest::request('system.sitemap');

        foreach($sitemap as $i=>$module) {
            foreach($module as $l=>$link) {
                $domain = (app::get()->getConfiguration()->get('require_ssl')->getValue() ? app::get()->getConfiguration()->get('secure_site_url')->getValue() : app::get()->getConfiguration()->get('site_url')->getValue());

                if(substr($domain,-1) != '/' && substr($sitemap[ $i ][ $l ]['loc'],0,1) != '/') {
                    $url = $domain . '/' . $sitemap[ $i ][ $l ]['loc'];
                }
                else {
                    $url = $domain . $sitemap[ $i ][ $l ]['loc'];
                }
                
                $sitemap[ $i ][ $l ]['loc'] = $url;
            }
        }

        $view->data = $sitemap;
        return $view;
    }

    public function sitemapHTML()
    {
        $view = new View('sitemap_html');
        $data = modRequest::request('system.sitemap', array ( 'nested' => true ));
        $total_items = 0;
        $items_array = array();
        foreach($data as $module) {
            foreach($module as $item) {
                $total_items++;
                $items_array[] = $item;
            }
        }

        $keyed_items = array();

        foreach($data as $name=>$module) {

            foreach($module as $item) {
                $keyed_items[$name][] = $item;
            }

        }


        $view->data['items'] = $items_array;
        $view->data['keyed_items'] = $keyed_items;
        $view->setXSSSanitation(false);
        return $view;
    }

    public function getZipInfo($data)
    {
        /** @var saPostalCode $postal_code */
        if($data['zip']) {
            $postal_code = app::$entityManager->getRepository(ioc::staticResolve('saPostalCode'))->findOneBy(array('code' => $data['zip']));
            if($postal_code && $postal_code->getState()) {
                $data['state'] = $postal_code->getState()->getAbbreviation();
                $data['stateid'] = $postal_code->getState()->getId();
                if($postal_code->getState()->getCountry()) {
                    $data['country'] = $postal_code->getState()->getCountry()->getAbbreviation();
                    $data['countryid'] = $postal_code->getState()->getCountry()->getId();
                }
            }
            if($postal_code && $postal_code->getCity()) {
                $data['city'] = $postal_code->getCity()->getName();
            }
        }
        return $data;
    }

    public function repairOnlineUsersGeo() {

        set_time_limit(600);

        // This creates the Reader object, which should be reused across
        $reader = new Reader($this->moduleLocation().'/other/GeoLite2-City.mmdb');


        $offset = 0;

        $users = ioc::getRepository('OnlineUser')->findBy(array('ip_country'=>'US', 'ip_state'=>''), null, 200, $offset);

        do {

            /** @var OnlineUser $user */
            foreach ($users as $user) {

                try {
                    $record = $reader->city($user->getIpAddress());
                } catch (\Exception $e) {
                    continue;
                }

                $user->setIpCountry($record->country->isoCode);
                $user->setIpState($record->mostSpecificSubdivision->isoCode);
                $user->setIpCity($record->city->name);
                $user->setIpLatitude($record->location->latitude);
                $user->setIpLongitude($record->location->longitude);
                $user->setIpCode($record->postal->code);

            }

            app::$entityManager->flush();

            app::$entityManager->clear();

            $offset += 2000;

            $users = ioc::getRepository('OnlineUser')->findBy(array('ip_country'=>null), null, 2000, $offset);


        }
        while( count($users)>0 );

    }
    
    public function getCountryDataByCode($data)
    {
        $country = ioc::getRepository('saCountry')->findOneBy(array('abbreviation' => $data['country_code']));
        if($country)
            $data['country'] = doctrineUtils::getEntityArray($country);
        else
            $data['country'] = null;
        return $data;
    }

    public function generateURL($json){
        $routeId = $json['id'];
        $url = app::get()->getRouter()->generate($routeId, $json['parameters']);

        $json = new Json();
        $json->data['success'] = true;
        $json->data['url'] = $url;
        return $json;
    }

    /**
     * ModRequest listener: app.route.can_access
     * @param array $data
     * @return array
     */
    public static function canAccessRoute($data = array()) {
        if (empty($data['route_id'])) {
            return $data;
        }

        $canAccess = (isset($data['can_access'])) ? $data['can_access'] : true;
        $data['can_access'] = $canAccess && app::getInstance()->canAccessRoute($data['route_id']);

        return $data;
    }

    public static function getRevisionEvents() {
        return array(
            Events::onFlush,
            Events::postPersist,
            Events::postUpdate,
            Events::preRemove,
            Events::postFlush
        );
    }

    public function reportToCentral() {

        $license = json_decode( file_get_contents(app::getAppPath().'/config/license.json' ), true);

        $monitor = app::get()->getConfiguration()->get('sa_central_enabled' )->getValue();
        $monitor_host = app::get()->getConfiguration()->get('sa_central_host' )->getValue();
        $monitor_client_id = app::get()->getConfiguration()->get('sa_central_client_id')->getValue();
        $monitor_client_key = app::get()->getConfiguration()->get('sa_central_client_key')->getValue();
        $monitor_instance= app::get()->getConfiguration()->get('sa_central_instance')->getValue();
        $monitor_email_monitoring_to = app::get()->getConfiguration()->get('sa_central_email_monitoring_to')->getValue();

        $email_monitor_hash = md5($monitor_email_monitoring_to.rand(0,9999999).time());

        $site_url = app::get()->getConfiguration()->get('site_url', true)->getValue();


        // DONT USE A THREAD,  WE ARE ALREADY IN ONE.
        // PLUS WE DONT WONT TO FILL THE SA EMAIL LOG UP.
        //modRequest::request('messages.sendEmail', array('to' => $monitor_email_monitoring_to, 'body' => $email_monitor_hash, 'subject' => $email_monitor_hash));

        /** @var saEmail $email */
        $email = ioc::get('saEmail');
        $email->setToAddress($monitor_email_monitoring_to);
        $email->setBody($email_monitor_hash.' '.$site_url);
        $email->setSubject($email_monitor_hash);
        $email->sendNow();

        app::$entityManager->remove($email);
        app::$entityManager->flush($email);

        $client = new \sa\sa3ApiClient\Sa3ApiClient($monitor_host, $monitor_client_id, $monitor_client_key);
        if ($monitor) {

            $modules = [];
            try {
                $store = ioc::get('\sa\store\Store');
                $modules = $store->getInstalledModules();
            }
            catch(Exception $e) {

            }

            $unsent_notattempted = 0;
            try {
                $emailRepo = ioc::getRepository('saEmail');
                if (method_exists($emailRepo, 'getNotAttemptedCount')) {
                    $unsent_notattempted = $emailRepo->getNotAttemptedCount();
                }
            }
            catch(Exception $e) {

            }

            $unsent_failed = 0;
            try {
                $emailRepo = ioc::getRepository('saEmail');
                if (method_exists($emailRepo, 'getFailedDeliveryCount')) {
                    $unsent_failed = $emailRepo->getFailedDeliveryCount();
                }
            }
            catch(Exception $e) {

            }

            if ($client->isConnected()) {
                $data = array(
                    'domain' => preg_replace('/http:\/\/|https:\/\/|\//', '', $site_url),
                    'instance' => $monitor_instance,
                    'ip' => $_SERVER['SERVER_ADDR'],
                    'license' => $license['sa_key'],
                    'modules' => $modules,
                    'emails_not_attempted' => $unsent_notattempted,
                    'emails_not_delivered' => $unsent_failed,
                    'email_monitor_hash' => $email_monitor_hash
                );

                $result = $client->custom->central->ping($data);

            }


        }
    }

    public function verifyLicense($data) {

//        if (!file_exists( app::getAppPath().'/config/license.json' )) {
//            $auth = saAuth::getInstance();
//            $auth->getNewLicense();
//        }
//
//        $monitor = app::get()->getConfiguration()->get('sa_central_enabled', true)->getValue();
//        $monitoring_reporting_interval = app::get()->getConfiguration()->get('sa_central_monitoring_interval', true)->getValue();
//
//        if ($monitor) {
//
//            app::get()->getCacheManager()->addPersistentNamespace('monitoring');
//            $cacheDriver = app::get()->getCacheManager()->getCache('monitoring');
//
//            if ($cacheDriver->contains('monitoring_last_report')) {
//                $last_report = $cacheDriver->fetch('monitoring_last_report');
//            }
//
//            if (time() - $last_report > $monitoring_reporting_interval) {
//
//                $cacheDriver->save('monitoring_last_report', time(), 86400);
//
//                //systemController::reportToCentral();
//
//                $thread = new Thread('executeController', 'systemController', 'reportToCentral');
//                $thread->run();
//            }
//
//        }
//
//        return $data;
    }
}
