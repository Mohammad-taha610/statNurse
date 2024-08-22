<?php


namespace sa\system;


use sacore\application\app;
use sacore\application\ioc;
use sacore\application\Middleware;
use sacore\application\responses\Redirect;

/**
 * Class SaPermissionMiddleware
 * @package sa\system
 */
class SaPermissionMiddleware extends Middleware
{
    /**
     * @param \sacore\application\Request $request
     * @return mixed
     */
    public function BeforeRoute($request)
    {
        $auth = saAuth::getInstance();
        $route = $request->getRoute();

        //Getting info from route
        $route_permissions = $request->getRouteParams()->get('route_permissions');
        $allowed = $request->getRouteParams()->get('allowed');
        $machine_protected = $request->getRouteParams()->get('machine_protected');
        if(is_null($machine_protected)) $machine_protected = false;

        //See if the route even needs the permissions checked
        //Unnecessary check, since you have to add this middleware for permissions you have decided it does
        //If they aren't authenticated then they have no permissions, redirect them to login(can change this later)
        if (!$auth->isAuthenticated()) {
            $_SESSION['sa_login_redirect'] = $request->getPathInfo();
            return new Redirect(app::get()->getRouter()->generate('sa_login'));
        }
        else {
            /** @var saUser $authUser */
            $user = saAuth::getAuthUser();

            //Todo: Testing purposes remove later
            $user = ioc::getRepository('saUser')->find(7);

            //If dev do nothing and go with default?
            if ( $user->getUserType()==$user::TYPE_DEVELOPER ) {

            }
            //If super user assume they have all permissions in developer?
            elseif ( $user->getUserType()==$user::TYPE_SUPER_USER ) {

                if (in_array('developer', $route_permissions)) {
                    return new Redirect(app::get()->getRouter()->generate('sa_permission_denied'));
                }
            }
            //Everyone else, do you have the permissions
            //Also looks like we are really only basing it based on one matched permission, that makes this easier
            else {
                $module = explode('_', $route_permissions[0])[0];
                $user_permissions = $user->getPermissions();

                $user_permissions[ $module ] = isset( $user_permissions[ $module ] ) ? $user_permissions[ $module ] : array();

                $permissionsAllowed = array_intersect( $route_permissions, array_keys($user_permissions[ $module ]));

                //If no matches can't do it
                if ( !$permissionsAllowed ) {
                    return new Redirect(app::get()->getRouter()->generate('sa_permission_denied'));
                }

                //Turn off developer routes for anyone who is in this if block??
                if ( in_array('developer', $route_permissions) && $allowed ) {
                    return new Redirect(app::get()->getRouter()->generate('sa_permission_denied'));
                }

                //Two factor stuff, not currently in my scope
                $sa_login_two_factor = false;
                if (!is_null(app::get()->getConfiguration()->get('sa_login_two_factor')))
                    $sa_login_two_factor = app::get()->getConfiguration()->get('sa_login_two_factor');

                if ( $allowed && $sa_login_two_factor && stristr($route->getPath(),'two_factor_verify') == false && stristr($route->getPath(),'logoff') == false )
                {
                    if ( !isset( $_SESSION['sa_login_two_factor_verified'] ) )
                        $_SESSION['sa_login_two_factor_verified'] = false;

                    $verified = $_SESSION['sa_login_two_factor_verified'];
                    if (!$verified)
                    {
                        return new Redirect(app::get()->getRouter()->generate('sa_two_factor_verify'));
                    }
                }

                //Device Verify stuff, not currently in my scope
                $deviceVerify = false;
                if (!is_null(app::get()->getConfiguration()->get('sa_device_verify')))
                    $deviceVerify = app::get()->getConfiguration()->get('sa_device_verify');

                //Limit permission based on machine info
                if ( $machine_protected && $allowed && $deviceVerify && !$sa_login_two_factor && stristr($route->getPath(),'sa_machineverifycode') == false && stristr($route->getPath(),'logoff') == false)
                {
                    /** @var saUserDevice $saUserDevice */
                    $saUserDevice = ioc::resolve('saUserDevice');
                    $verified = $saUserDevice::isDeviceVerified(saAuth::getMachineUUID(), $auth->getAuthUser());
                    if (!$verified)
                    {
                        return new Redirect(app::get()->getRouter()->generate('sa_machineverify'));
                    }
                }

                /** @var saUser $user */
                $user = $auth->getAuthUser();

                //See if SA user is allowed to login here, if not block him
                if($user && $user->getUserType() != saUser::TYPE_SUPER_USER && $user->getisLocationRestricted()) {
                    $userIp = systemController::get_client_ip();

                    if(!in_array($userIp, $user->getAllowedLoginLocations())) {
                        return new Redirect(app::get()->getRouter()->generate('sa_location_blocked'));
                    }
                }
            }
        }
        //Return nothing if everything looks good
    }


}