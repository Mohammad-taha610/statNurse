<?php
namespace sa\system;
use sacore\application\app;
use sacore\application\assetView;
use sacore\application\DependencyInjection;
use sacore\application\DependencyInjectionException;
use sacore\application\DependencyInjectionPreloadException;
use sacore\application\ioc;
use sacore\application\responses\Css;
use sacore\utilities\url;
use sacore\application\responses\ISaResponse;

class SpriteFactory
{
    use \sacore\application\responses\THTMLOptimization;

    public static function buildSpriteCSS() {

        $json = json_decode( file_get_contents( app::get()->getConfiguration()->get('tempDir').'/build/images/sprite.json' ), true );

//        assetView::$systemErrorType = assetView::ERROR_TYPE_NONE;

        ini_set('memory_limit','500M');

        $content = '.sa-sprite { background-image: url("/build/combined/sprite.png");
        max-width: 100%;
        background-size: 100%;
        margin: auto;
        display: inline-block;
    }
    .sa-sprite {
        width: 100%;
    }

    .sa-sprite:after {
        content: "";
        display: table;
        clear: both;
    }
    '.PHP_EOL;


        $spriteImg = imagecreatetruecolor(1000, 25000);

        imagealphablending($spriteImg, true);
        imagesavealpha($spriteImg, true);
        $alpha = imagecolorallocatealpha($spriteImg, 0, 0, 0, 127);
        imagefill($spriteImg,0,0,$alpha);


        $sprite_width = imagesx($spriteImg);
        $sprite_height = imagesy($spriteImg);


        $yOffset = 0;
        $xOffset = 0;

		if (!file_exists(app::get()->getConfiguration()->get('public_directory'). '/build/combined')) {
            mkdir(app::get()->getConfiguration()->get('public_directory') . '/build/combined', 0777, true);
        }

        // BUILD IMAGE
        foreach( $json as $k=>$image ) {
            $path = $image['path'];
            $requestedWidth = $image['width'];

            $class = preg_replace('/[^a-z0-9]/i', '-', ltrim($path, '/') );

            url::setURI($path);

            $routeInfo = app::get()->findRoute($path);

            if (!$routeInfo)
                continue;

            $arguments = $routeInfo->getRouteVariables($path);

            if (strpos($routeInfo->controller, '\\') === false) {
                $controller = ioc::get($routeInfo->controller);
            } else {
                $controller = new $routeInfo->controller();
            }

            try {

                try {
                    $arguments = DependencyInjection::fillMethodParams($controller, $routeInfo->function, $arguments);
                    $object = call_user_func_array(array($controller, $routeInfo->function), $arguments);       
                }
                catch(\Exception $e) {
                    continue;
                }

                if($object instanceof \sacore\application\responses\File == false) {
                    continue; 
                }

                ob_start();
                $object->getResponse();
                $image = ob_get_clean();

                $src = imagecreatefromstring($image);
                $width = imagesx($src);

                // echo $width.':'.$requestedWidth.'<br />';
                
                if ($requestedWidth && $width > $requestedWidth) {
                    $src = static::resize($src, $requestedWidth);
                }

                $width = imagesx($src);
                $height = imagesy($src);

                imagecopy($spriteImg, $src, 0, $yOffset, 0, 0, $width, $height);

                $xOffset = $width > $xOffset ? $width : $xOffset;

                $json[$k]['yOffset'] = $yOffset;
                $json[$k]['yOffsetAfter'] = $yOffset + $height;
                $json[$k]['width'] = $width;
                $json[$k]['height'] = $height;
                $json[$k]['class'] = $class;

                $yOffset += $height + 5;

            } catch (\Exception $e) {
                

                
            }
        }
        
//        $spriteImg = imagecrop($spriteImg, ['x' => 0, 'y' => 0, 'width' => $xOffset, 'height' => $yOffset]);
        $newSpriteImg = imagecreatetruecolor($xOffset, $yOffset);
        imagesavealpha($newSpriteImg, true);
        imagealphablending($newSpriteImg, false);
        imagecopyresampled($newSpriteImg, $spriteImg, 0, 0, 0, 0, $xOffset, $yOffset, $xOffset, $yOffset);
        imagepng($newSpriteImg, app::get()->getConfiguration()->get('public_directory') . '/build/combined/sprite.png', 9);
        
//        url::setURI( url::uri(true) );

        // echo 'finished'; exit;

        $sprite_width = imagesx($newSpriteImg);
        $sprite_height = imagesy($newSpriteImg);

        // Compress using API
        try {
            $spriteSize = array(array('name_prefix'=>'', 'max_width'=>$sprite_width, 'max_height'=>$sprite_height));
            $results = \sa\saFiles\saImage::resizeViaAPI(app::get()->getConfiguration()->get('public_directory'). '/build/combined/sprite.png',$spriteSize);
        }
        catch( \sa\sa3ApiClient\ApiClientException $e ) {

        }

        if($results) {
            $zip_path = app::get()->getConfiguration()->get('tempDir') . '/sprite' . time() . rand(0, 9999) . '.zip';
            file_put_contents($zip_path, base64_decode($results['response']['zip']));
            $zip = new \ZipArchive();
            if ($zip->open($zip_path)) {
                $image = $zip->getFromName('sprite.png');
                file_put_contents(app::get()->getConfiguration()->get('public_directory') . '/build/combined/sprite.png', $image);
                $zip->close();
                unlink($zip_path);
            }
        }

        $heightOffset = 0;
        // BUILD CSS
        foreach( $json as $k=>&$image ) {

            $width = $image['width'];
            $height = $image['height'];
            $class = $image['class'];

            $scale = ($sprite_width / $width);
            $bgsize_x = $scale * 100;
            $image['height_offset'] = $scale * $heightOffset;
            $image['scaled_width'] = $width * $scale;
            $image['scaled_height'] = $height * $scale;

            $image['scaled_sprite_width'] = $sprite_width * $scale;
            $image['scaled_sprite_height'] = $sprite_height * $scale;

            $image['padding_bottom'] = ($image['height'] + 5) / $image['width'] * 100;

            $percent = 100 / ( count($json)-1 );

            $percent = $percent * $k;

            $bgposition_y = $image['height_offset'] / ($image['scaled_sprite_height'] - $image['scaled_height']);
            $bgposition_y = $bgposition_y * 100;
            $content .= '.'. $class .' { background-position: 0 '.$bgposition_y.'%; background-size: '.$bgsize_x.'%; max-height: '.$height.'px; max-width: '.$width.'px; }' . PHP_EOL;
            $content .= '.'. $class .':before { content: ""; float: left; padding-bottom: ' . $image['padding_bottom'] . '%; }' . PHP_EOL;

            $heightOffset += $image['height'] + 5;
        }

        //echo $content;

        // Minify CSS content
        $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
        $content = str_replace(': ', ':', $content);
        $content = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $content);
        file_put_contents(app::get()->getConfiguration()->get('public_directory') . '/build/combined/sprite.css',  $content);


        $view = new Css(200);
        $view->data = $content;

        return $view;

    }

    protected static function resize($source_image, $maxWidth)
    {
        $source_image_width = imagesx($source_image);
        $source_image_height = imagesy($source_image);

        if ($source_image === false || $source_image === null) {
            return null;
        }

        $source_aspect_ratio = $source_image_width / $source_image_height;

        $thumbnail_image_width = $maxWidth;
        $thumbnail_image_height = (int) ($maxWidth / $source_aspect_ratio);

        $resized_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);

        imagealphablending( $resized_image, false );
        imagesavealpha( $resized_image, true );
        imagecopyresampled($resized_image, $source_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);

        imagedestroy($source_image);

        return $resized_image;
    }

    /**
     * Combines all the stylesheets includes into one file
     *
     * @param $bufferedContent
     * @return mixed
     */
    public static function buildSpriteJson($bufferedContent) {

        $paths = array();
        $uniquePaths = array();

        $bufferedContent = preg_replace_callback('/<img\s(.*?)>/s',
            function($match) use(&$paths, &$uniquePaths) {

                $attributes = SpriteFactory::parseAttributes($match[1]);
                
                if ( !isset($attributes['data-sprite']) ) {
                    return $match[0];
                }

                $path = $attributes['src'];
                $width = $attributes['data-sprite_width'] ? $attributes['data-sprite_width'] : null;

                $path = str_replace('/build', '', $path);
                $class = preg_replace('/[^a-z0-9]/i', '-', ltrim($path, '/'));

                if(in_array($path,$uniquePaths) == false) {
                    $uniquePaths[] = $path;
                    $paths[] = array('path'=>$path, 'width'=>$width);
                }

                return '<div class="sa-sprite '.$class.'"></div>';

            }, $bufferedContent);

        $json = json_encode($paths, JSON_PRETTY_PRINT);

        $hash = md5($json);

        if (!file_exists(app::get()->getConfiguration()->get('tempDir').'/build/images')) {
            mkdir(app::get()->getConfiguration()->get('tempDir').'/build/images', 0777, true);
        }

        file_put_contents( app::get()->getConfiguration()->get('tempDir').'/build/images/sprite.json', $json );
        
        $bufferedContent = str_replace('</head>', '<link data-no_combine="1" rel="stylesheet" href="/build/combined/sprite.css"></head>', $bufferedContent);

        return $bufferedContent;

    }

    public static function replaceImagesWithSprites($bufferedContent) {

        if(app::get()->getConfiguration()->get('sprite_images') == false) {
            return $bufferedContent;
        }

        $paths = array();
//        $object = $thtmlObj;

        $bufferedContent = preg_replace_callback('/<img\s(.*?)>/s',
            function($match) use(&$paths) {

                $attributes = SpriteFactory::parseAttributes($match[1]);

                if ( !isset($attributes['data-sprite']) ) {
                    return $match[0];
                }

                $path = $attributes['src'];

                $path = str_replace('/build', '', $path);

                $class = preg_replace('/[^a-z0-9]/i', '-', ltrim($path, '/'));

                $paths[] = array('path'=>$path);

                // group attributes into string so they can be included on element
                $attributesStr = '';
                foreach($attributes as $k => $v) {
                    if($k == 'src' || $k == 'data-sprite') continue;
                    if($k == 'class') {
                        $class .= ' ' . $v;
                        continue;
                    }

                    $attributesStr .= ' ' . $k . '="' . $v . '"';
                }

                return '<div class="sa-sprite '.$class.'"' . $attributesStr . '></div>';

            }, $bufferedContent);

        $bufferedContent = str_replace('</head>', '<link data-no_combine="1" rel="stylesheet" href="/build/combined/sprite.css"></head>', $bufferedContent);

        return $bufferedContent;

    }


}
?>