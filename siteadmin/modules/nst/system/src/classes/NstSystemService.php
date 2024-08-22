<?php

namespace nst\system;

use nst\system\NstSystemController;
use sa\system\saAuth;
use sa\system\saUser;

class NstSystemService
{
    public function filterNavThroughPermissions($menuItems) 
    {
        // Get current SA User
        /** @var saUser $saUser */
        $auth = saAuth::getInstance();
        $saUser = is_object($auth) ? $auth->getAuthUser() : null;

        // Convoluted as hell array of objects that make up the SA side-nav
        foreach ($menuItems['siteadmin_root']->children as $key => $tab) {
            // Switch statement for filtering on group permissions on hiding modules
            switch ($tab->name) {
                case 'Settings': 
                    if (!$saUser?->hasGroupPermission('system-settings-access')) {
                        unset($menuItems['siteadmin_root']->children[$key]);
                    }
                    break;
                case 'Admin Reports': 
                        if (!$saUser?->hasGroupPermission('system-settings-access')) {
                            unset($menuItems['siteadmin_root']->children[$key]);
                        }
                        break;
                case 'SA Store':
                    if (!$saUser?->hasGroupPermission('store-access')) {
                        unset($menuItems['siteadmin_root']->children[$key]);
                    }
                    break;
                case 'Developer':
                    if (!$saUser?->hasGroupPermission('developer-access')) {
                        unset($menuItems['siteadmin_root']->children[$key]);
                    }
                    break;
                case 'Nurses':
                    foreach ($tab->children as $subKey => $subTab) {
                        switch ($subTab->name) {
                            case 'Create Nurses':
                                if (!$saUser?->hasGroupPermission('member-create-nurses')) {
                                    unset($menuItems['siteadmin_root']->children[$key]->children[$subKey]);
                                }
                                break;
                            case 'Merge Nurses':
                                if (!$saUser?->hasGroupPermission('member-merge-nurses')) {
                                    unset($menuItems['siteadmin_root']->children[$key]->children[$subKey]);
                                }
                                break;
                            default:
                        }
                    }
                    break;
                case 'Providers':
                    foreach ($tab->children as $subKey => $subTab) {
                        switch ($subTab->name) {
                            case 'Create Provider':
                                if (!$saUser?->hasGroupPermission('member-create-providers')) {
                                    unset($menuItems['siteadmin_root']->children[$key]->children[$subKey]);
                                }
                                break;
                            default:
                        }
                    }
                    break;
                case 'Payroll':
                    foreach ($tab->children as $subKey => $subTab) {
                        switch ($subTab->name) {
                            case 'Reports':
                                if (!$saUser?->hasGroupPermission('payroll-manage-reports')) {
                                    unset($menuItems['siteadmin_root']->children[$key]->children[$subKey]);
                                }
                                break;
                            default:
                        }
                    }
                    break;
                case 'Quickbooks':
                    if (!$saUser?->hasGroupPermission('quickbooks-access')) {
                        unset($menuItems['siteadmin_root']->children[$key]);
                    }
                    break;
                case 'Shifts':
                    foreach ($tab->children as $subKey => $subTab) {
                        switch ($subTab->name) {
                            case 'Shift Action Log':
                                unset($menuItems['siteadmin_root']->children[$key]->children[$subKey]);
                                if (!$saUser?->hasGroupPermission('events-shift-action-log-access')) {
                                    unset($menuItems['siteadmin_root']->children[$key]->children[$subKey]);
                                }
                                break;
                            case 'Manage Shifts':
                                unset($menuItems['siteadmin_root']->children[$key]->children[$subKey]);
                                break;
                            default:
                        }
                    }
                    break;
                default:
            }
        }

        return $menuItems;
    }
}