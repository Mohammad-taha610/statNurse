<?php
/**
 * Date: 10/25/2017
 *
 * File: AssetBuildManager.php
 */

namespace sa\system;
use sacore\application\app;
use sacore\application\ioc;
use sacore\application\modRequest;
use sacore\application\staticResourceRoute;
use sa\sa3ApiClient\ApiClientException;
use sa\sa3ApiClient\Sa3ApiClient;
use sacore\utilities\JSqueeze;
use sacore\utilities\url;

/**
 * Manages the Asset Build / Link system
 *
 * Class AssetBuildManager
 * @package sa\system
 */
class AssetBuildManager
{

    /** @var \Doctrine\Common\Cache\CacheProvider $cacheManager */
    protected $cacheManager = null;
    protected $domain;
    protected $links = array('/'=>false);
    protected $javascript = array();
    protected $stylesheets = array();
    protected $staticResourceRoutes = array();
    protected $buildFolder = 'build';

    /**
     * AssetBuildManager constructor.
     */
    public function __construct()
    {
        $this->buildFolder = static::getBuildFolder();
    }

    protected static function getBuildFolder() {
        $buildFolder = 'build';
        $config = app::get()->getConfiguration();
        $multi = $config->get('multi_site_instance', true)->getValue();
        if ($multi) {
            $environment = app::getEnvironment();
            $buildFolder = preg_replace('/[^a-z]/i', '', $environment['name']);
        }

        return $buildFolder;
    }


    /**
     * @param $cacheManager \Doctrine\Common\Cache\CacheProvider
     * @throws \sacore\application\ModRequestAuthenticationException
     */
    public function startBuild($cacheManager) {

        ini_set('memory_limit', '512M');
        set_time_limit('900');

        $this->domain = app::get()->getConfiguration()->get('require_ssl')->getValue()
            ? app::get()->getConfiguration()->get('secure_site_url')->getValue()
            : app::get()->getConfiguration()->get('site_url')->getValue();
        $this->cacheManager = $cacheManager;

        $this->cacheManager->delete('log');
        $this->writeToLog('Build Starting');
        $this->writeToLog('Removing Invalid Assets');
        $this->removeInvalidLinks();
        $this->getAssetsRoutes();
        $this->writeToLog('Caching Module Assets');
        $this->writeToLog('');
        $this->cacheModuleAssetsToBuildFolders();
        $this->writeToLog('Caching Theme Assets');
        $this->writeToLog('');
        $this->cacheThemeAssetsToBuildFolders();
        $this->writeToLog('Caching Vendor Assets');
        $this->writeToLog('');
        $this->cacheVendorAssetsToBuildFolders();
        
        if (app::get()->getConfiguration()->get('cache_assets_module_request')->getValue()) {
            $this->writeToLog('Caching Module Request Assets');
            $this->writeToLog('');
            modRequest::request('assets.additional_cache_request', null, $this);
        }

        $this->writeToLog('Build HTAccess file to force GZIP');
        $this->writeToLog('');
        $this->addBuildHTAccess();

        $this->writeToLog('Building Combined Files');
        $this->writeToLog('');
        $this->buildCombinedCSSFiles();
        $this->buildCombinedJSFiles();
        $this->writeToLog('Build Finished');

    }

    protected function addBuildHTAccess() {

        file_put_contents(app::get()->getConfiguration()->get('public_directory')->getValue() . DIRECTORY_SEPARATOR . $this->buildFolder.'/.htaccess', '<IfModule mod_deflate.c>
# Compress HTML, CSS, JavaScript, Text, XML and fonts
AddOutputFilterByType DEFLATE application/javascript
AddOutputFilterByType DEFLATE application/rss+xml
AddOutputFilterByType DEFLATE application/vnd.ms-fontobject
AddOutputFilterByType DEFLATE application/x-font
AddOutputFilterByType DEFLATE application/x-font-opentype
AddOutputFilterByType DEFLATE application/x-font-otf
AddOutputFilterByType DEFLATE application/x-font-truetype
AddOutputFilterByType DEFLATE application/x-font-ttf
AddOutputFilterByType DEFLATE application/x-javascript
AddOutputFilterByType DEFLATE application/xhtml+xml
AddOutputFilterByType DEFLATE application/xml
AddOutputFilterByType DEFLATE font/opentype
AddOutputFilterByType DEFLATE font/otf
AddOutputFilterByType DEFLATE font/ttf
AddOutputFilterByType DEFLATE image/svg+xml
AddOutputFilterByType DEFLATE image/x-icon
AddOutputFilterByType DEFLATE text/css
AddOutputFilterByType DEFLATE text/html
AddOutputFilterByType DEFLATE text/javascript
AddOutputFilterByType DEFLATE text/plain
AddOutputFilterByType DEFLATE text/xml
# Remove browser bugs (only needed for really old browsers)
BrowserMatch ^Mozilla/4 gzip-only-text/html
BrowserMatch ^Mozilla/4\.0[678] no-gzip
BrowserMatch \bMSIE !no-gzip !gzip-only-text/html
Header append Vary User-Agent
</IfModule>

# 1 WEEK
<FilesMatch "\.(flv|ico|pdf|avi|mov|ppt|doc|mp3|wmv|wav)$">
Header set Cache-Control "max-age=604800, public"
</FilesMatch>

# 1 WEEK
<FilesMatch "\.(jpg|jpeg|png|gif|swf)$">
Header set Cache-Control "max-age=604800, public"
</FilesMatch>

# 1/2 DAY
<FilesMatch "\.(txt|xml|js|css)$">
Header set Cache-Control "max-age=43200"
</FilesMatch>

# NEVER CACHE - notice the extra directives
<FilesMatch "\.(html|htm|php|cgi|pl)$">
Header set Cache-Control "max-age=0, private, no-store, no-cache, must-revalidate"
</FilesMatch>

<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType image/jpg "access plus 1 week"
    ExpiresByType image/jpeg "access plus 1 week"
    ExpiresByType image/gif "access plus 1 week"
    ExpiresByType image/png "access plus 1 week"
    
    ExpiresByType text/css "access plus 43200 seconds"
    ExpiresByType application/js "access plus 43200 seconds"
</IfModule>

Header add Access-Control-Allow-Origin '.( app::get()->getConfiguration()->get('require_ssl')->getValue() ? app::get()->getConfiguration()->get('secure_site_url')->getValue() : app::get()->getConfiguration()->get('site_url')->getValue()).'
');

    }

    protected function buildCombinedJSFiles() {

        $cacheManager = app::getInstance()->getCacheManager();
        $combinedCache = $cacheManager->getCache('asset_combining');

        $hashes = $combinedCache->fetch('js_hashes');

        $this->domain = app::get()->getConfiguration()->get('require_ssl')->getValue()
            ? app::get()->getConfiguration()->get('secure_site_url')->getValue()
            : app::get()->getConfiguration()->get('site_url')->getValue();
        
        $jz = new JSqueeze();

        foreach($hashes as $hash) {
            
            if(!$combinedCache->contains('js_'.$hash)) {
                continue; 
            }
            
            $fileinfo = $combinedCache->fetch('js_'.$hash);

            if ( $fileinfo['built'] || count($fileinfo['paths']) == 0) {
                continue;
            }

            $fileinfo['built'] = true;
            $combinedCache->save('js_'.$hash, $fileinfo, 0);

            foreach( $fileinfo['paths'] as $k=>$url ) {
                if ( substr($url, 0, 6)==='/'.$this->buildFolder ) {
                    $fileinfo['paths'][$k] = substr($url, 6);
                }
            }

            $content = "/** \nFILES INCLUDED IN THIS COMBINED JS FILE: \n\n";
            foreach( $fileinfo['paths'] as $url ) {
                $content .= $url."\n";
            }
            $content .= "*/\n\n";

            foreach( $fileinfo['paths'] as $url ) {

                $arrContextOptions=array(
                    "ssl"=>array(
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                    ),
                );
                $js_content = file_get_contents($domain.$url, false, stream_context_create($arrContextOptions));
                $headers = $this->parseHeaders($http_response_header);

                if ($headers['response_code']!=200) {
                    $content .= '/** UNABLE TO COMBINE FILE, RESPONDED WITH CODE '.$headers['response_code'].' **/';
                }
                elseif ( strpos($headers['Content-Type'], '/javascript')===false) {
                    $content .= '/** UNABLE TO COMBINE FILE, RESPONDED WITH TYPE '.$headers['Content-Type'].' **/';
                }
                else {

                    $content .= "\n//File ".$url." \n\n";
                    $content .= "try {\n";
                    if (strpos($url, '.min.')===false)
                    {
                        $content .= $jz->squeeze($js_content);
                    }
                    else
                    {
                        $content .= $js_content;
                    }
                    $content .= "\n} catch(err) { \nconsole.log('Error occurred inside ".$url."'); console.log(err)\n}\n";

                }

            }

            if (!file_exists(app::get()->getConfiguration()->get('public_directory')->getValue() . '/'.$this->buildFolder.'/combined/js')) {
                mkdir(app::get()->getConfiguration()->get('public_directory')->getValue() . '/'.$this->buildFolder.'/combined/js', 0777, true);
            }
            file_put_contents(app::get()->getConfiguration()->get('public_directory')->getValue() . '/'.$this->buildFolder.'/combined/js/' . $hash . '.js', $content);


            /**
             * Replicate to nodes
             */
            $nodes = ioc::getRepository('saClusterNode')->findAll();
            /** @var saClusterNode $node */
            foreach ($nodes as $node) {
                try {
                    $client = new Sa3ApiClient($node->getSaApiUrl(), $node->getClientId(), $node->getApiKey());
                    if (!$client->isConnected()) {
                        continue;
                    }

                    $result = $client->custom->sanode->saveCombinedJSFile(['hash'=>$hash, 'content'=>$content]);
                    if ($result['response']['error']) {
                        $this->writeToLog(' The node ' . $node->getSaApiUrl() . ' reported an error syncing combined file.');
                    }
                }
                catch(ApiClientException $e) {
                    $this->writeToLog(' The node '.$node->getSaApiUrl().' is not available. '.$e->getMessage());
                }
            }

        }
    }


    protected function buildCombinedCSSFiles() {

        $cacheManager = app::getInstance()->getCacheManager();
        $combinedCache = $cacheManager->getCache('asset_combining');

        $hashes = $combinedCache->fetch('css_hashes');

        $this->domain = app::get()->getConfiguration()->get('require_ssl')->getValue()
            ? app::get()->getConfiguration()->get('secure_site_url')->getValue()
            : app::get()->getConfiguration()->get('site_url')->getValue();

        foreach($hashes as $hash) {

            if(!$combinedCache->contains('css_'.$hash)) {
                continue;
            }
            
            $fileinfo = $combinedCache->fetch('css_'.$hash);

            if ( $fileinfo['built'] || count($fileinfo['paths']) == 0)
                continue;

            $fileinfo['built'] = true;
            $combinedCache->save('css_'.$hash, $fileinfo, 0);

            foreach( $fileinfo['paths'] as $k=>$url ) {
                if ( substr($url['path'], 0, 6)==='/'.$this->buildFolder ) {
                    $fileinfo['paths'][$k]['path'] = substr($url['path'], 6);
                }
            }

            $files_included_content = "/* \nFILES INCLUDED IN THIS COMBINED CSS FILE: \n\n";
            foreach( $fileinfo['paths'] as $url ) {
                $files_included_content .= $url['path']."\n";
            }
            $files_included_content .= "*/\n";

            $content = '';
            foreach( $fileinfo['paths'] as $css ) {
                $url = $css['path'];

                $arrContextOptions=array(
                    "ssl"=>array(
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                    ),
                );
                $css_content = file_get_contents($domain.$css['path'], false, stream_context_create($arrContextOptions));
                $headers = $this->parseHeaders($http_response_header);
                

                if ($headers['response_code']!=200) {
                    $css_content .= '/** UNABLE TO COMBINE FILE, RESPONDED WITH CODE '.$headers['response_code'].' **/';
                }
                elseif ( strpos($headers['Content-Type'], 'text/css')===false) {
                    $css_content .= '/** UNABLE TO COMBINE FILE, RESPONDED WITH TYPE '.$headers['Content-Type'].' **/';
                }
                else {
                    $directory = pathinfo($url, PATHINFO_DIRNAME);
                    $css_content = preg_replace_callback('/url\(\s*[\'"]?(.+?)[\'"]?\s*\)/i', function ($matches) use ($directory) {
                        if (substr($matches[1], 0, 1) == '/' || substr($matches[1], 0, 2) == '//' || substr($matches[1], 0, 7) == 'http://' || substr($matches[1], 0, 8) == 'https://') {
                            return $matches[0];
                        } else {
                            return 'url("' . $directory . '/' . $matches[1] . '")';
                        }

                    }, $css_content);
                    if ($css['media'] == 'print') {
                        $css_content = "@media print { \n" . $css_content . " \n}";
                    }
                }

                $css_content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css_content);
                $css_content = str_replace(': ', ':', $css_content);
                $css_content = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css_content);
                $content .= "\n/* " . $url . " */ \n\n" . $css_content . "\n/* **** END **** " . $url . " */ \n\n";
            }

            $content = preg_replace_callback('/@import(\s){1,}url\(\s*[\'"]?(.+?)[\'"]?\s*\)(;){0,1}/i', function($matches) use (&$imports) {
                $imports .= $matches[0].PHP_EOL;
                return '';

            }, $content);

            if (!file_exists(app::get()->getConfiguration()->get('public_directory')->getValue() . '/'.$this->buildFolder.'/combined/css')) {
                mkdir(app::get()->getConfiguration()->get('public_directory')->getValue() . '/'.$this->buildFolder.'/combined/css', 0777, true);
            }

            $css_content_to_write = $imports .PHP_EOL. $files_included_content .PHP_EOL. $content;
            file_put_contents(app::get()->getConfiguration()->get('public_directory')->getValue() . '/'.$this->buildFolder.'/combined/css/' . $hash . '.css', $css_content_to_write);


            /**
             * Replicate to nodes
             */
            $nodes = ioc::getRepository('saClusterNode')->findAll();
            /** @var saClusterNode $node */
            foreach ($nodes as $node) {
                try {
                    $client = new Sa3ApiClient($node->getSaApiUrl(), $node->getClientId(), $node->getApiKey());
                    if (!$client->isConnected()) {
                        continue;
                    }

                    $result = $client->custom->sanode->saveCombinedCSSFile(['hash'=>$hash, 'content'=>$css_content_to_write]);
                    if ($result['response']['error']) {
                        $this->writeToLog(' The node ' . $node->getSaApiUrl() . ' reported an error syncing combined file.');
                    }
                }
                catch(ApiClientException $e) {
                    $this->writeToLog(' The node '.$node->getSaApiUrl().' is not available. '.$e->getMessage());
                }
            }


        }
    }

    protected function parseHeaders( $headers )
    {
        $head = array();
        foreach( $headers as $k=>$v )
        {
            $t = explode( ':', $v, 2 );
            if( isset( $t[1] ) )
                $head[ trim($t[0]) ] = trim( $t[1] );
            else
            {
                $head[] = $v;
                if( preg_match( "#HTTP/[0-9\.]+\s+([0-9]+)#",$v, $out ) )
                    $head['response_code'] = intval($out[1]);
            }
        }
        return $head;
    }

    protected function removeInvalidLinks() {

        $build_dir = app::get()->getConfiguration()->get('public_directory')->getValue(). DIRECTORY_SEPARATOR . $this->buildFolder;

        $files = static::getDirContents($build_dir, array('..', '.'));


        foreach($files as $file) {

            if (is_dir($file))
                continue;

            if (strpos($file, '/'.$this->buildFolder.'/combined'))
                continue;

            if (app::get()->getConfiguration()->get('cache_assets_using_hard_link')->getValue()) {
                $finfo = stat($file);
                if ($finfo[3] <= 1) {
                    unlink($file);
                }
            }
            elseif (!is_link($file))
            {
                unlink($file);
            }
            elseif (is_link($file) && !file_exists($file))
            {
                unlink($file);
            }

        }

    }

    public function cacheFile($source, $url) {

        if (is_dir($source))
            return;

        $build_path = app::get()->getConfiguration()->get('public_directory')->getValue(). DIRECTORY_SEPARATOR . $this->buildFolder . DIRECTORY_SEPARATOR .$url;
        $build_dir =  pathinfo($build_path, PATHINFO_DIRNAME);

        $build_path = str_replace('//', DIRECTORY_SEPARATOR, $build_path);
        $source = str_replace('//', DIRECTORY_SEPARATOR, $source);


        if (!file_exists($source)) {
            $this->writeToLog('!', false);
            return;
        }

        if (!file_exists($build_dir)) {
            mkdir($build_dir, 0755, true);
        }

//        $this->writeToLog($source.' - '.$build_path, true);


        if (file_exists($build_path) && app::get()->getConfiguration()->get('cache_assets_using_hard_link')->getValue()) {

            $finfo = stat ( $build_path );
            if ($finfo[3]<=1) {
                unlink($build_path);
            }

        }
        elseif (file_exists($build_path) && !app::get()->getConfiguration()->get('cache_assets_using_hard_link')->getValue()) {

            if (!is_link ($build_path))
            {
                unlink($build_path);
            }

        }
        else {

            if (app::get()->getConfiguration()->get('cache_assets_using_hard_link')->getValue()) {
                link($source, $build_path);
                $this->writeToLog('.', false);
            }
            else
            {
                symlink($source, $build_path);
                $this->writeToLog('.', false);
            }

        }

    }

    protected function cacheVendorAssetsToBuildFolders() {

        $apppath = app::getAppPath();

        $files = static::getDirContents($apppath.DIRECTORY_SEPARATOR.'vendor', array('..', '.'));

        foreach($files as $file) {

            if (is_dir($file))
                continue;

            if (!preg_match('/(\.js|\.css|\.jpg|\.png|\.gif|\.otf|\.eot|\.svg|\.ttf|\.woff|\.woff2)$/', $file)) {
                continue;
            }

            $destination = str_replace($apppath.'/', '', $file);

            $this->cacheFile($file, $destination);

        }

    }


    protected function cacheThemeAssetsToBuildFolders() {

        $apppath = app::getAppPath();

        $files = static::getDirContents($apppath.DIRECTORY_SEPARATOR.'themes', array('..', '.', 'ViewHelper.php', 'composer.json', 'views'));
        
        foreach($files as $file) {

            if (is_dir($file))
                continue;

            if (!preg_match('/(\.js|\.css|\.jpg|\.png|\.gif|\.otf|\.eot|\.svg|\.ttf|\.woff|\.woff2)$/', $file)) {
                continue;
            }

            $destination = str_replace($apppath.'/', '', $file);

            $this->cacheFile($file, $destination);

        }

    }


    protected function cacheModuleAssetsToBuildFolders() {

        $modulesPath = app::getAppPath();
        $modulesPath .= '/modules';

        $moduleAssetFiles = array();

        $matched = array();

        /** @var staticResourceRoute $route */
        foreach($this->staticResourceRoutes as $route) {

            $module = $route->module;
            $moduleParts = explode('\\', $module);
            $modulePath = $modulesPath. DIRECTORY_SEPARATOR. implode(DIRECTORY_SEPARATOR, $moduleParts) . DIRECTORY_SEPARATOR . 'src';

            if (file_exists($modulePath) && !isset($moduleAssetFiles[$module])) {
                $moduleAssetFiles[$module] = static::getDirContents($modulePath, array('..', '.', 'views', 'classes'));
            }


            foreach($moduleAssetFiles[$module] as $asset) {
                if (is_dir($asset))
                    continue;
                try {
                    $testurl = url::make($route->id, pathinfo($asset, PATHINFO_BASENAME));

                    if ($route->testRoute($testurl)) {

                        $matched[] = array('url' => $testurl, 'source' => $asset);

                        $this->cacheFile($asset, $testurl);

                    }
                }
                catch(\Exception $e) {

                }
            }
        }

    }


    protected static function getDirContents($dir, $ignore=array(), &$results = array()){

        $files = scandir($dir);
        $files = array_values(array_diff($files, $ignore));


        foreach($files as $key => $value){
            $path = $dir.DIRECTORY_SEPARATOR.$value;
            $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
            $path = str_replace('//', DIRECTORY_SEPARATOR, $path);


            if(!is_dir($path)) {
                $results[] = $path;
            } else if($value != "." && $value != "..") {
                static::getDirContents($path, $ignore, $results);
                $results[] = $path;
            }
        }

        return $results;
    }


    protected function getAssetsRoutes() {
        
        $routes = app::get()->getRoutes();

        $this->staticResourceRoutes = array();
        foreach($routes['IDS'] as $priorityGroups)
        {
            foreach($priorityGroups as $route) {

                if ($route instanceof \sacore\application\staticResourceRoute) {
                    $this->staticResourceRoutes[] = $route;
                }
            }
        }

        return $this->staticResourceRoutes;
    }



    protected function writeToLog($message, $newline = true) {
        $log = $this->cacheManager->fetch('log');
        if ( substr($log, 0, -1)!=PHP_EOL && $newline ) {
            $log = $log .  PHP_EOL ;
        }
        $log .= $message;
        $this->cacheManager->save('log', $log, 3600);

        $cli_io = app::get()->getCliIO();
        if ($cli_io) {

            if ($newline) {
                $cli_io->newLine();
                $cli_io->write($message);
            }
            else
                $cli_io->write($message);

        }

    }



    /**
     * Deletes the public build directory
     */
    public static function flushBuildDirectory() {

        $buildFolder = static::getBuildFolder();

        modRequest::request('system.cache.flush', array('asset_combining', 'asset_cache_build'));

        AssetBuildManager::deleteDir(app::get()->getConfiguration()->get('public_directory')->getValue() . '/'.$buildFolder);

    }


    /**
     * Recursively deletes a directory and all children
     *
     * @param $dirPath
     */
    public static function deleteDir($dirPath) {

        $buildFolder = static::getBuildFolder();

        if (!is_dir($dirPath)) {
            //throw new InvalidArgumentException("$dirPath must be a directory");
            return;
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }

        $files = static::getDirContents($dirPath, array('..', '.'));

        foreach ($files as $file) {

            if (strpos($file, '/'.$buildFolder.'/combined/sprite.css'))
                continue;

            if (strpos($file, '/'.$buildFolder.'/combined/sprite.png'))
                continue;

            if (is_dir($file)) {

                if (substr($file, -8)=='combined')
                    continue;

                self::deleteDir($file);

            } else {

                unlink($file);

            }
        }

        rmdir($dirPath);



    }


}