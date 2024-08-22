@extends('master')
@section('site-container')
@asset::/member/profile/css/stylesheet.css

@view::_member_profile_header_nav

<div class="dashboard-container">
    <div class="row">
        <?php
        if($dashboard_items) {
            foreach($dashboard_items as $dashboard_item)
            {
                ?>
                <div class="col-xs-6 col-sm-4">
                    <div class="dashboard-element">
                        <a href="<?=$dashboard_item['link'] ?>" class="dashboard-button">
                            <i class="fa <?=$dashboard_item['icon'] ? $dashboard_item['icon'] : 'fa-star' ?>"></i><br>
                            <?=$dashboard_item['name'] ?>
                        </a>
                    </div>
                </div>
                <?php
            }
        }
        ?>
    </div>
</div>

@view::_member_profile_footer

@show