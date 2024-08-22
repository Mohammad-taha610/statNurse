<?php

use sacore\application\app;

// Get current SA User
/** @var \sa\system\saUser $saUser */
$auth = \sa\system\saAuth::getInstance();
$saUser = is_object($auth) ? $auth->getAuthUser() : null;

?>
@extends('master')
@asset::/applications/js/file-uploader.js
@asset::/siteadmin/member/js/nurse_files_vue.js
@asset::/siteadmin/member/js/nurse_basic_info_vue.js
@asset::/siteadmin/member/js/nurse_checkr_info_vue.js
<!-- @asset::/siteadmin/member/js/nurse_bank_info_vue.js -->
@asset::/siteadmin/member/js/nurse_notes_vue.js
@asset::/siteadmin/member/js/nurse_states_vue.js
<!-- @asset::/siteadmin/member/js/nurse_pay_card_info_vue.js -->
@asset::/siteadmin/member/js/provider_files_vue.js
@asset::/siteadmin/member/js/provider_pay_rates_vue.js
@asset::/siteadmin/member/js/provider_basic_info_vue.js
@asset::/siteadmin/member/js/provider_contacts_vue.js
@asset::/siteadmin/member/js/provider_option_presets_vue.js
@asset::/siteadmin/member/js/executive_basic_info_vue.js
@asset::/siteadmin/member/js/emergency_contacts_vue.js
@asset::/siteadmin/member/js/nurse_contact_info_vue.js
@asset::/siteadmin/member/js/member_footer_vue.js
@asset::/siteadmin/member/js/nurse_messaging_vue.js
@asset::/siteadmin/member/js/executive_facilities_vue.js
@section('site-container')



<v-app>
    <div class="row">
        <div class="col-xs-12">
            <form id="profile-form" class="form-horizontal" method="POST" action="<?= $postRoute ?>">
                <div class="tabbable">
                    <ul class="nav nav-tabs padding-16">
                        <li class="active">
                            <a data-toggle="tab" href="#edit-basic" v-on:click="current_tab = 'basic'">
                                <i class="primary--text fa fa-edit bigger-125"></i>
                                Basic Info
                            </a>
                        </li>
                        <?php if ($memberId) { ?>
                            <?php if($member_type == 'Executive') { ?>
                                <li class="">
                                    <a data-toggle="tab" href="#edit-facilities" v-on:click="current_tab = 'facilities'">
                                        <i class="primary--text fa fa-building bigger-125"></i>
                                       Facilities
                                    </a>
                                </li>
                            <?php } else if ($member_type == 'Provider') { ?>

                                <?php if ($saUser?->hasGroupPermission('events-create-approved-shifts')) { ?>
                                    <li class="">
                                        <a data-toggle="tab" href="#edit-payrates" v-on:click="current_tab = 'payrates'">
                                            <i class="primary--text fa fa-dollar bigger-125"></i>
                                            Pay Rates
                                        </a>
                                    </li>
                                <?php } ?>

                                <li class="">
                                    <a data-toggle="tab" href="#edit-option-presets"
                                       v-on:click="current_tab = 'provideroptionpresets'">
                                        <i class="primary--text fa fa-file bigger-125"></i>
                                        Option Presets
                                    </a>
                                </li>

                                <?php if ($saUser?->hasGroupPermission('member-manage-provider-profile-files')) { ?>
                                    <li class="">
                                        <a data-toggle="tab" href="#edit-providerfiles"
                                        v-on:click="current_tab = 'providerfiles'">
                                            <i class="primary--text fa fa-file bigger-125"></i>
                                            Provider Files
                                        </a>
                                    </li>
                                <?php } ?>
                            <?php } ?>
                            <?php if ($member_type == 'Nurse') { ?>
                                <li class="">
                                    <a data-toggle="tab" href="#edit-nursefiles"
                                       v-on:click="current_tab = 'nursefiles'">
                                        <i class="primary--text fa fa-file bigger-125"></i>
                                        Employee Files
                                    </a>
                                </li>
																<li class="">
                                    <a data-toggle="tab" href="#edit-nursecheckrinfo"
                                       v-on:click="current_tab = 'nursecheckrinfo'">
                                        <i class="primary--text fa fa-credit-card bigger-125"></i>
                                        Checkr Pay Info
                                    </a>
                                </li>
                                <li class="">
                                    <a data-toggle="tab" href="#edit-nursebankinfo"
                                       v-on:click="current_tab = 'nursebankinfo'">
                                        <i class="primary--text fa fa-money bigger-125"></i>
                                        Direct Deposit Info
                                    </a>
                                </li>
                                <li class="">
                                    <a data-toggle="tab" href="#payCardInfo" v-on:click="current_tab = 'pay-card-info'">
                                        <i class="primary--text far fa-address-card bigger-125"></i>
                                        Pay Card Info
                                    </a>
                                </li>
                            <?php } ?>
                            <li class="">
                                <a data-toggle="tab" href="#edit-usernames" v-on:click="current_tab = 'usernames'">
                                    <i class="primary--text fa fa-key bigger-125"></i>
                                    Username/Password
                                </a>
                            </li>
                            <?php if ($member_type == 'Provider') { ?>
                                <li class="">
                                    <a data-toggle="tab" href="#edit-contacts" v-on:click="current_tab = 'contacts'">
                                        <i class="primary--text fa fa-envelope bigger-125"></i>
                                        Contacts
                                    </a>
                                </li>
                            <?php } ?>

                            <?php if ($member_type == 'Nurse') { ?>
                                <li class="">
                                    <a data-toggle="tab" href="#edit-nursecontact-info"
                                       v-on:click="current_tab = 'contactInfo'">
                                        <i class="primary--text fa fa-envelope bigger-125"></i>
                                        Contact Info
                                    </a>
                                </li>
                                <!-- <li class="">
                                    <a data-toggle="tab" href="#edit-phone" v-on:click="current_tab = 'phone'">
                                        <i class="primary--text fa fa-phone bigger-125"></i>
                                        Phone Numbers
                                    </a>
                                </li> -->
                                <li class="">
                                    <a data-toggle="tab" href="#edit-emergency-contacts" v-on:click="current_tab = 'emergency-contacts'">
                                        <i class="primary--text fa fa-address-book bigger-125"></i>
                                        Emergency Contacts
                                    </a>
                                </li>
                                <li class="">
                                    <a data-toggle="tab" href="#notes" v-on:click="current_tab = 'notes'">
                                        <i class="primary--text far fa-sticky-note bigger-125"></i>
                                        Notes
                                    </a>
                                </li>
                                <li class="">
                                    <a data-toggle="tab" href="#states" v-on:click="current_tab = 'states'">
                                        <i class="primary--text far fa-map bigger-125"></i>
                                        States
                                    </a>
                                </li>
                                <li class="">
                                    <a data-toggle="tab" href="#messaging" v-on:click="current_tab = 'messaging'">
                                        <i class="primary--text fa fa-phone bigger-125"></i>
                                        Messaging
                                    </a>
                                </li>
                            <?php } ?>
                        <?php } ?>

                        <?php if ($other_tabs) { ?>
                            <?php foreach ($other_tabs as $tab) { ?>
                                <li class="">
                                    <a data-toggle="tab" href="#<?= $tab['id'] ?>">
                                        <i class="primary--text <?= $tab['icon'] ?> bigger-125"></i>
                                        <?= $tab['name'] ?>
                                    </a>
                                </li>
                            <?php } ?>
                        <?php } ?>
                    </ul>

                    <div class="tab-content profile-edit-tab-content">
                        <div id="edit-basic" class="tab-pane active">
                            <h4 class="header primary--text bolder smaller">General</h4>
                            <div class="row" style="justify-content: center;">

                                <div class="vspace-xs"></div>
                                <?php if ($member_type == 'Nurse' && $member_id > 0) { ?>
                                    <div class="col-xs-12 col-sm-6">

                                        <nurse-basic-info-view
                                                ref="basicInfo"
                                                id="<?= $nurse_id ?>"
                                        >

                                        </nurse-basic-info-view>
                                    </div>
                                <?php } else if ($member_type == 'Provider' && $member_id > 0) {
                                    $isProvider = true; ?>
                                    <div class="col-xs-12 col-sm-6">
                                        <provider-basic-info-view
                                                ref="basicInfo"
                                                :tab="current_tab"
                                                id="<?= $provider_id ?>"
                                        >
                                        </provider-basic-info-view>
                                    </div>
                                <?php } else if ($member_type == 'Executive' && $member_id > 0) {
                                    $isExecutive = true; ?>
                                    <div class="col-xs-12 col-sm-6">
                                        <executive-basic-info-view
                                                ref="basicInfo"
                                                :tab="current_tab"
                                                id="<?= $executive_id ?>"
                                        >
                                        </executive-basic-info-view>
                                    </div>
                                <?php } else { ?>
                                    <div class="col-xs-12 col-sm-8">

                                        <div class="form-group"
                                             style="<?= $member_type == 'Nurse' ? 'display: none;' : '' ?>">
                                            <label class="col-sm-4 control-label no-padding-right"
                                                   for="form-field-company">Provider Name</label>

                                            <div class="col-sm-8">
                                                <input class="col-xs-12 col-sm-10" type="text" id="form-field-company"
                                                       placeholder="Company" value="<?= $company ?>" name="company">
                                            </div>
                                        </div>

                                        <?php if ($member_type == 'Nurse') { ?>
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label no-padding-right"
                                                       for="form-field-first_name">First
                                                    Name</label>

                                                <div class="col-sm-8">
                                                    <input class="col-xs-12 col-sm-10" type="text"
                                                           id="form-field-first_name"
                                                           placeholder="First Name" value="<?= $first_name ?>"
                                                           name="first_name">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-sm-4 control-label no-padding-right"
                                                       for="form-field-first">Last
                                                    Name</label>

                                                <div class="col-sm-8">
                                                    <input class="col-xs-12 col-sm-10" type="text" id="form-field-last"
                                                           placeholder="Last Name" value="<?= $last_name ?>"
                                                           name="last_name">
                                                </div>
                                            </div>

                                            <div class="form-group"
                                                 style="<?= $member_type != 'Nurse' ? 'display: none;' : '' ?>">
                                                <label class="col-sm-4 control-label no-padding-right"
                                                       for="form-field-first">Credentials</label>
                                                <div class="col-xs-12 col-sm-7">
                                                    <div class="row" style="padding-top: 5px;">
                                                        <div class="col-xs-12 col-sm-3">
                                                            <label><input style="top: 1px; position: relative;"
                                                                          type="radio" name="credentials"
                                                                          value="CNA" <?= $credentials == 'CNA' ? 'checked' : '' ?>>
                                                                CNA </label>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-3">
                                                            <label><input style="top: 1px; position: relative;"
                                                                          type="radio" name="credentials"
                                                                          value="CMT" <?= $credentials == 'CMT' ? 'checked' : '' ?>>
                                                                CMT </label>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-3">
                                                            <label><input style="top: 1px; position: relative;"
                                                                          type="radio" name="credentials"
                                                                          value="LPN" <?= $credentials == 'LPN' ? 'checked' : '' ?>>
                                                                LPN </label>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-3">
                                                            <label><input style="top: 1px; position: relative;"
                                                                          type="radio" name="credentials"
                                                                          value="RN" <?= $credentials == 'RN' ? 'checked' : '' ?>>
                                                                RN </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <?php if ($member_type == 'Provider' || $member_type =='Executive') { ?>

                                            <div class="form-group">
                                                <label class="col-sm-4 control-label no-padding-right"
                                                       for="form-field-first">Administrator</label>
                                                <div class="col-sm-8">
                                                    <input class="col-xs-12 col-sm-10" type="text" id="form-field-last"
                                                           placeholder="Administrator" value="<?= $administrator ?>"
                                                           name="administrator">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label no-padding-right"
                                                       for="form-field-first">Director of Nursing</label>
                                                <div class="col-sm-8">
                                                    <input class="col-xs-12 col-sm-10" type="text" id="form-field-last"
                                                           placeholder="Director of Nursing"
                                                           value="<?= $director_of_nursing ?>"
                                                           name="director_of_nursing">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label no-padding-right"
                                                       for="form-field-first">Scheduler</label>
                                                <div class="col-sm-8">
                                                    <input class="col-xs-12 col-sm-10" type="text" id="form-field-last"
                                                           placeholder="Scheduler" value="<?= $scheduler_name ?>"
                                                           name="scheduler_name">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label no-padding-right"
                                                       for="form-field-first">Facility Phone Number</label>
                                                <div class="col-sm-8">
                                                    <input class="col-xs-12 col-sm-10" type="text" id="form-field-last"
                                                           placeholder="Facility Phone Number"
                                                           value="<?= $facility_phone_number ?>"
                                                           name="facility_phone_number">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label no-padding-right"
                                                       for="form-field-schedule">View Schedule</label>
                                                <div class="col-sm-8">
                                                    <a href="<?= app::get()->getRouter()->generate('sa_provider_shift_calendar', ['member_id' => $memberId]) ?>"><i
                                                                class="primary--text fa bigger-200 fa-calendar"</a>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>

                            <hr>
                            <div class="space-4"></div>


                            <div class="form-group" style="display: none;">
                                <label class="col-sm-3 control-label no-padding-right"
                                       for="form-field-type">Type</label>

                                <div class="col-sm-9">
                                    <select id="form-field-type" name="member_type">
                                        <option value="Nurse" <?= $member_type == 'Nurse' ? 'selected' : '' ?>>Nurse
                                        </option>
                                        <option value="Provider" <?= $member_type == 'Provider' ? 'selected' : '' ?>>
                                            Provider
                                        </option>
                                        <option value="Executive" <?= $member_type == 'Executive' ? 'selected' : '' ?>>
                                            Executive
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div id="edit-facilities" class="tab-pane">
                            <h4 class="header primary--text bolder smaller">Facilities</h4>
                             <div class="row">
                                 <div class="col-xs-12 col-sm-12">
                                        <executive-facilities-view
                                                :tab="current_tab"
                                                id="<?= $executive_id ?>"
                                        ></executive-facilities-view>
                                 </div>
                             </div>
                        </div>
                        <div id="edit-payrates" class="tab-pane">
                            <h4 class="header primary--text bolder smaller">Pay Rates</h4>
                            <?php if ($provider_id > 0) { ?>
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12">
                                        <provider-pay-rates-view
                                                ref="payRates"
                                                :tab="current_tab"
                                                id="<?= $provider_id ?>"
                                        ></provider-pay-rates-view>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <div id="edit-nursefiles" class="tab-pane">
                            <h4 class="header primary--text bolder smaller">Employee Files</h4>
                            <div id="nurse-files-vue">
                                <nurse-files-view
                                        id="<?= $nurse_id ?>"
                                        v-bind:uploads_allowed="true"
                                        member-type="<?= $member_type ?>"
                                ></nurse-files-view>
                            </div>
                        </div>
                        <?php if ($member_type !== 'Provider') { ?>
													<div id="edit-nursecheckrinfo" class="tab-pane">
                            <h4 class="header primary--text bolder smaller">Checkr Pay Info</h4>
                            <div id="nurse-files-vue">
                                <nurse-checkr-info-view
                                        id="<?= $nurse_id ?>"
                                        v-bind:uploads_allowed="true"
                                ></nurse-checkr-info-view>
                            </div>
                        </div>
												<div id="edit-nursebankinfo" class="tab-pane">
                            <h4 class="header primary--text bolder smaller">Direct Deposit Info</h4>
                            <div id="nurse-files-vue">
                                <nurse-bank-info-view
                                        id="<?= $nurse_id ?>"
                                        v-bind:uploads_allowed="true"
                                ></nurse-bank-info-view>
                            </div>
                        </div>
                        <div id="payCardInfo" class="tab-pane">
                            <h4 class="header primary--text bolder smaller">Pay Card Info</h4>
                            <div id="pay-card-info-vue">
                                <nurse_pay_card_info_view
                                        id="<?= $nurse_id ?>"
                                ></nurse_pay_card_info_view>
                            </div>
                        </div>
                        <div id="edit-nursecontact-info" class="tab-pane">
                            <h4 class="header primary--text bolder smaller">Contact Info</h4>
                            <div id="nurse-files-vue">
                                <nurse-contact-info-view
                                        id="<?= $nurse_id ?>"
                                        v-bind:uploads_allowed="true"
                                ></nurse-contact-info-view>
                            </div>
                        </div>
                        <div id="notes" class="tab-pane">
                            <h4 class="header primary--text bolder smaller">Nurse Notes</h4>
                            <div id="nurse-files-vue">
                                <nurse-notes-view
                                        id="<?= $nurse_id ?>"
                                        v-bind:uploads_allowed="true"
                                ></nurse-notes-view>
                            </div>
                        </div>
                        <div id="states" class="tab-pane">
                            <h4 class="header primary--text bolder smaller">Nurse States</h4>
                            <div id="nurse-states-vue">
                                <nurse-states-view
                                        id="<?= $nurse_id ?>"
                                ></nurse-states-view>
                            </div>
                        </div>
                        <div id="messaging" class="tab-pane">
                            <h4 class="header primary--text bolder smaller">SMS Messaging</h4>
                            <div id="nurse-messaging-vue">
                                <nurse-messaging-view
                                        id="<?= $nurse_id ?>"
                                ></nurse-messaging-view>
                            </div>
                        </div>
                        <?php } else { ?>

                        <div id="edit-option-presets" class="tab-pane">
                            <h4 class="header primary--text bolder smaller">Option Presets</h4>
                            <div id="provider-option-presets-vue">
                                <provider-option-presets-view
                                        provider-id="<?= $provider_id ?>"
                                        member-type="<?= $member_type ?>">

                                </provider-option-presets-view>
                            </div>
                        </div>

                        <?php } ?>
                        <div id="edit-emergency-contacts" class="tab-pane">
                            <h4 class="header primary--text bolder smaller">Emergency Contact Info</h4>
                            <div id="provider-files-vue">
                                <emergency-contacts-view
                                        id="<?= $nurse_id ?>"
                                        member-type="<?= $member_type ?>"
                                ></emergency-contacts-view>
                            </div>
                        </div>
                        <div id="edit-providerfiles" class="tab-pane">
                            <h4 class="header primary--text bolder smaller">Provider Files</h4>
                            <div id="provider-files-vue">
                                <provider-files-view
                                        id="<?= $provider_id ?>"
                                        member-type="<?= $member_type ?>"
                                ></provider-files-view>
                            </div>
                        </div>
                        <div id="edit-contacts" class="tab-pane">
                            <h4 class="header primary--text bolder smaller">Contacts</h4>
                            <div id="provider-contacts-vue">
                                <provider-contacts-view
                                        :tab="current_tab"
                                        id="<?= $provider_id ?>"
                                        member-type="<?= $member_type ?>"
                                ></provider-contacts-view>
                            </div>
                        </div>

                        @view::saProfile_table
                        @view::saProfile_dbForm

                        <?php foreach ($other_tabs as $tab) { ?>
                            <div id="<?= $tab['id'] ?>" class="tab-pane">
                                <div class="form-group">
                                    <h4 class="header primary--text bolder smaller"><?= $tab['name'] ?></h4>
                                </div>
                                <?= $tab['html'] ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <?php if ($member_id > 0) { ?>
                    <member-footer-view
                            member_id="<?= $member_id ?>"
                            member_type="<?= $member_type ?>"
                            id="<?= $member_type == 'Nurse' ? $nurse_id : $provider_id ?>"
                    ></member-footer-view>
                <?php } else { ?>
                    <div class="clearfix form-actions">
                        <div class="col-md-offset-3 col-md-9">
                            <button class="btn btn-info" type="submit">
                                <i class="fa fa-save bigger-110"></i>
                                Save
                            </button>

                            &nbsp; &nbsp;
                            <button class="btn" type="reset">
                                <i class="fa fa-undo bigger-110"></i>
                                Reset
                            </button>
                        </div>
                    </div>
                <?php } ?>
                <input type="hidden" id="form-field-pass1" name="sa_member_id" value="<?= $sa_member_id ?>">
            </form>
        </div>
    </div>
</v-app>
@show

<style>
    .v-chip .v-chip__content {
        width: 150px !important;
    }
</style>
