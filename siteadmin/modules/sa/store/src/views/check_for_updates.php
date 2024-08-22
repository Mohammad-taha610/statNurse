@extends('master')
@section('site-container')

<div class="row">
    <div class="col-sm-12 col-md-9 col-md-offset-2 text-center">
        <p class="lead">Site Administrator modules are updated regularly to provide new features and performance upgrades. These updates can help improve stability, security, and add new features.</p>
    </div>
</div>
<hr>
<div class="row store">
    <div class="col-xs-12 col-sm-12 col-md-7 col-lg-3">
        <div class="module">
            <div class="row">
                <div class="col-xs-12 col-md-4 text-center">
                    <div class="image">
                        <i class="fa fa-cog fa-3x"></i>
                    </div>
                </div>

                <div class="col-xs-12 col-md-8 name">
                    <div class="row">
                        <div class="col-xs-12">
                            System Summary
                        </div>
                    </div>
                    <div class="row version">
                        <div class="col-xs-12 <?=$info['system_updates'] > 0 ? 'redbold' : ''?>" id="system-updates">
                            <?=$info['system_updates']?> System Update<?=$info['system_updates'] > 1 || $info['system_updates'] == 0 ? 's' : ''?>
                        </div>
                    </div>
                    <div class="row version">
                        <div class="col-xs-12 <?=$info['installed_updates'] > 0 ? 'redbold' : ''?>"  id="module-updates">
                            <?=$info['installed_updates']?> Module Update<?=$info['installed_updates'] > 1 || $info['installed_updates'] == 0 ? 's' : ''?>
                        </div>
                    </div>
                    <div class="row version">
                        <div class="col-xs-12">
                            <?=$info['installed_modules']?> Modules Installed
                        </div>
                    </div>
                    <div class="row version">
                        <div class="col-xs-12">
                            <?=$info['installed_themes']?> Themes Installed
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-md-12" style="margin-top: 20px;">
                    <a href="<?= \sacore\application\app::get()->getRouter()->generate('sa_module_updateAll'); ?>" class=" updateallbtn btn btn-pink btn-block <?=($info['installed_updates'] > 0 || $info['system_updates'] > 0) ? '' : 'saHidden'?> ">Update All</a>
                    <button class="btn btn-info btn-block checkforupdates">Check For Updates</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

    $('.checkforupdates').click( function() {



        setTimeout( function() { $('.checkforupdates').html('<i class="fa fa-circle-o-notch fa-spin fa-2x fa-fw"></i>'); }, 1);

        modRequest.request('sa.store.get_information', null, null, function(data) {

            if ( data.info.system_updates > 0 || data.info.installed_updates > 0 ) {
                $('.updateallbtn').removeClass('saHidden');
            }

            if ( data.info.installed_updates > 0 ) {
                $('#module-updates').addClass('redbold');
                $('#module-updates').text( data.info.installed_updates+' Module Update'+ (data.info.installed_updates > 1 ? 's' : '') );
            }

            if ( data.info.system_updates ) {
                $('#system-updates').addClass('redbold');
                $('#system-updates').text( data.info.system_updates+' System Update'+ (data.info.system_updates > 1 ? 's' : '') );
            }

            $('.checkforupdates').html('Check For Updates');

        })

    })

</script>

@show