<?php
namespace sa\member;

/**
 * Class ViewHelper
 * @package sa\member
 */
class ViewHelper
{
    protected $defaultNavOptions = array(
        "before_nav"     => '<ul>',
        "after_nav"      => '</ul>',
        "before_sub_nav" => '<ul class="sub-nav">',
        "after_sub_nav"  => '</ul>',
        "item_template"  => '<li><a href="%s"><i class="fa %s"></i>%s</a></li>',
        "active_item_template" => '<li><a href="%s" class="active"><i class="dashboard-sidebar-link fa %s"></i>%s</a></li>'
    );
    
    protected $customTemplate = null;

    /**
     * @param array $navItems
     * @param array $options
     * @param bool $isChild
     * @param string $html
     * @param null $customTemplate
     * @return string
     * @internal param null $template
     */
    public function walkRecursiveNav(array $navItems, array $options = array(), $isChild = false, $html = "", $customTemplate = null) {
        if($customTemplate != null) {
            $navOptions = array_merge($customTemplate, $options);
        } else {
            $navOptions = array_merge($this->defaultNavOptions, $options);
        }
        
        if($customTemplate != null) {
            $this->customTemplate = $customTemplate;
        }
        
        if(!$isChild) {
            if(!$isChild && $navOptions["before_nav"]) {
                $html .=  $navOptions["before_nav"];
            }
        } else {
            if($navOptions["before_sub_nav"]) {
                $html .=  $navOptions["before_sub_nav"];
            }
        }

        foreach($navItems as $item) {
            $html .= $this->renderNavItem($this->getTemplate($navOptions, $item), $item['link'], $item['icon'], $item['label']);

            if(!empty($item['children'])) {
                $html .= $this->walkRecursiveNav($item['children'], $navOptions, true);
            }
        }

        if(!$isChild) {
            if($navOptions["after_nav"]) {
                $html .=  $navOptions["after_nav"];
            }
        } else {
            if($navOptions["after_sub_nav"]) {
                $html .=  $navOptions["after_sub_nav"];
            }
        }

        return $html;
    }

    /**
     * @param array $options
     * @param $item
     * @return mixed
     */
    protected function getTemplate($options, $item) {
        $route = $_SERVER['REQUEST_URI'];

        if (trim($item['link']) == trim($route)) {
            return $options["active_item_template"];
        }

        return $options["item_template"];
    }

    /**
     * @param $label
     * @param $link
     * @param string $template
     * @param string $icon
     * @return string
     */
    public function renderNavItem($template, $link, $icon, $label) {
        return sprintf($template, $link, $icon, $label);
    }
}