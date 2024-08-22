<?php
/**
 * Developer notes
 *
 * Divs are closed in sa/member/src/views/_member_profile_footer.php.
 */
use sacore\application\app;

$providerName = \sa\member\auth::getAuthMember()->getCompany();
$userName = \sa\member\auth::getAuthUser()->getFirstName() . ' ' . \sa\member\auth::getAuthUser()->getLastName();
$user = \sa\member\auth::getAuthUser();
?>
<div id="main-wrapper">

<div class="nav-header">
    <a href="/dashboard" class="brand-logo">
        <img class="logo-compact" src="/themes/nst/assets/images/icon.png" alt="">
        <img class="brand-title" src="/themes/nst/assets/images/white-logo.png" alt="">
    </a>

    <div class="nav-control">
        <div class="hamburger">
            <span class="line"></span><span class="line"></span><span class="line"></span>
        </div>
    </div>
</div>
<div class="header" id="header-vue-wrapper">
    <div class="header-content">
        <nav class="navbar navbar-expand">
            <div class="collapse navbar-collapse justify-content-between">
                <div class="header-left">
                    <div class="dashboard_bar">
                        @yield('page-title')
                    </div>
                </div>

                <ul class="navbar-nav header-right">
                    <li class="nav-item">
                        <form method="GET" action="<?=app::get()->getRouter()->generate('nurse_list')?>" @submit="submitSearch">
                            <div class="input-group search-area d-lg-inline-flex d-none">
                                <input type="text" class="form-control header-search" v-model="search_term" name="search_term" placeholder="Search Nurses...">
                                <div class="input-group-append">
                                    <button type="submit" class="input-group-text header-search-icon" id="header-search"><i class="flaticon-381-search-2"></i></button>
                                </div>
                            </div>
                        </form>
                    </li>
                    <!--<li class="nav-item dropdown notification_dropdown">
                        <a class="nav-link  ai-icon" href="#" role="button" data-toggle="dropdown">
                            <svg width="26" height="28" viewBox="0 0 26 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9.45251 25.6682C10.0606 27.0357 11.4091 28 13.0006 28C14.5922 28 15.9407 27.0357 16.5488 25.6682C15.4266 25.7231 14.2596 25.76 13.0006 25.76C11.7418 25.76 10.5748 25.7231 9.45251 25.6682Z" fill="#3E4954"/>
                                <path d="M25.3531 19.74C23.8769 17.8785 21.3995 14.2195 21.3995 10.64C21.3995 7.09073 19.1192 3.89758 15.7995 2.72382C15.7592 1.21406 14.5183 0 13.0006 0C11.4819 0 10.2421 1.21406 10.2017 2.72382C6.88095 3.89758 4.60064 7.09073 4.60064 10.64C4.60064 14.2207 2.12434 17.8785 0.647062 19.74C0.154273 20.3616 0.00191325 21.1825 0.240515 21.9363C0.473484 22.6721 1.05361 23.2422 1.79282 23.4595C3.08755 23.8415 5.20991 24.2715 8.44676 24.491C9.84785 24.5851 11.3543 24.64 13.0007 24.64C14.646 24.64 16.1524 24.5851 17.5535 24.491C20.7914 24.2715 22.9127 23.8415 24.2085 23.4595C24.9477 23.2422 25.5268 22.6722 25.7597 21.9363C25.9983 21.1825 25.8448 20.3616 25.3531 19.74Z" fill="#3E4954"/>
                            </svg>
                            <span class="badge light text-white bg-primary rounded-circle">52</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <div id="DZ_W_Notification1" class="widget-media dz-scroll p-3 height380">
                                <div class="media mb-4">
                                    <span class="p-3 mr-3">
                                        <i class="las la-exclamation-circle"></i>
                                    </span>
                                    <div class="media-body">
                                        <p class="fs-14 mb-1 text-black font-w500">Jane Doe accepted the shift for <strong>March 31st, 2021 at 1:00pm</strong></p>
                                        <span class="fs-14">12h ago</span>
                                    </div>
                                </div>
                                <div class="media mb-4">
                                    <span class="p-3 mr-3">
                                        <i class="las la-exclamation-circle"></i>
                                    </span>
                                    <div class="media-body">
                                        <p class="fs-14 mb-1 text-black font-w500">Jane Doe accepted the shift for <strong>March 31st, 2021 at 1:00pm</strong></p>
                                        <span class="fs-14">12h ago</span>
                                    </div>
                                </div>
                                <div class="media mb-4">
                                    <span class="p-3 mr-3">
                                        <i class="las la-exclamation-circle"></i>
                                    </span>
                                    <div class="media-body">
                                        <p class="fs-14 mb-1 text-black font-w500">Jane Doe accepted the shift for <strong>March 31st, 2021 at 1:00pm</strong></p>
                                        <span class="fs-14">12h ago</span>
                                    </div>
                                </div>
                                <div class="media mb-4">
                                    <span class="p-3 mr-3">
                                        <i class="las la-exclamation-circle"></i>
                                    </span>
                                    <div class="media-body">
                                        <p class="fs-14 mb-1 text-black font-w500">Jane Doe accepted the shift for <strong>March 31st, 2021 at 1:00pm</strong></p>
                                        <span class="fs-14">12h ago</span>
                                    </div>
                                </div>
                                <div class="media mb-4">
                                    <span class="p-3 mr-3">
                                        <i class="las la-exclamation-circle"></i>
                                    </span>
                                    <div class="media-body">
                                        <p class="fs-14 mb-1 text-black font-w500">Jane Doe accepted the shift for <strong>March 31st, 2021 at 1:00pm</strong></p>
                                        <span class="fs-14">12h ago</span>
                                    </div>
                                </div>
                                <div class="media mb-4">
                                    <span class="p-3 mr-3">
                                        <i class="las la-exclamation-circle"></i>
                                    </span>
                                    <div class="media-body">
                                        <p class="fs-14 mb-1 text-black font-w500">Jane Doe accepted the shift for <strong>March 31st, 2021 at 1:00pm</strong></p>
                                        <span class="fs-14">12h ago</span>
                                    </div>
                                </div>
                                <div class="media mb-4">
                                    <span class="p-3 mr-3">
                                        <i class="las la-exclamation-circle"></i>
                                    </span>
                                    <div class="media-body">
                                        <p class="fs-14 mb-1 text-black font-w500">Jane Doe accepted the shift for <strong>March 31st, 2021 at 1:00pm</strong></p>
                                        <span class="fs-14">12h ago</span>
                                    </div>
                                </div>
                                <div class="media mb-4">
                                    <span class="p-3 mr-3">
                                        <i class="las la-exclamation-circle"></i>
                                    </span>
                                    <div class="media-body">
                                        <p class="fs-14 mb-1 text-black font-w500">Jane Doe accepted the shift for <strong>March 31st, 2021 at 1:00pm</strong></p>
                                        <span class="fs-14">12h ago</span>
                                    </div>
                                </div>
                                <div class="media mb-4">
                                    <span class="p-3 mr-3">
                                        <i class="las la-exclamation-circle"></i>
                                    </span>
                                    <div class="media-body">
                                        <p class="fs-14 mb-1 text-black font-w500">Jane Doe accepted the shift for <strong>March 31st, 2021 at 1:00pm</strong></p>
                                        <span class="fs-14">12h ago</span>
                                    </div>
                                </div>
                            </div>
                            <a class="all-notification" href="#">See all notifications <i class="ti-arrow-right"></i></a>
                        </div>
                    </li>-->
                    <li class="nav-item dropdown header-profile">
                        <a class="nav-link" href="#" role="button" data-toggle="dropdown">
                            <img src="/themes/nst/assets/images/profile.png" width="20" alt=""/>
                            <div class="header-info">
                                <span class="text-black"><?=$userName?></span>
                                <p class="fs-12 mb-0">Provider</p>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="<?= app::get()->getRouter()->generate('member_users') ?>" class="dropdown-item ai-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                <span class="ml-2">Manage Users</span>
                            </a>
                            <a href="<?= app::get()->getRouter()->generate('member_logoff') ?>" class="dropdown-item ai-icon">
                                <svg id="icon-logout" xmlns="http://www.w3.org/2000/svg" class="text-danger" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                <span class="ml-2">Logout </span>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>

<script>

    window.onload = function() {
        window.vue_wrapper = new Vue({
            el: '#header-vue-wrapper',
            vuetify: new Vuetify({
                theme: {
                    themes: {
                        light: {
                            primary: '#ee4037',
                            secondary: '#8BC740',
                            danger: '#FF6746',
                            success: '#1BD084',
                            info: '#48A9F8',
                            warning: '#FE8024',
                            light: '#F4F5F9',
                            dark: '#B1B1B1',
                            blue: '#5e72e4',
                            indigo: '#6610f2',
                            purple: '#6f42c1',
                            pink: '#e83e8c',
                            red: '#EE3232',
                            orange: '#ff9900',
                            yellow: '#FFFA6F',
                            green: '#297F00',
                            teal: '#20c997',
                            cyan: '#3065D0',
                            white: '#fff',
                            gray: '#6c757d',
                            gray_dark: '#343a40'
                        }
                    }
                }
            }),
            data: function() {
                return {
                    search_term: ''
                }
            },
            mounted: function() {

            },
            methods: {
                submitSearch(e) {
                    console.log('searching for: ' + this.search_term);
                    e.preventDefault();
                }
            }
        });
    }
</script>
