<?php if ($performance_status=='check') { ?>
<li class="navbar-header-hide-small">
<?php } else if ($performance_status=='warning') { ?>
<li style="background-color: rgba(238, 215, 16, 1)" class=" navbar-header-hide-small">
<?php } else if ($performance_status=='danger') { ?>
<li style="background-color: rgba(238, 55, 30, 1)" class=" navbar-header-hide-small">
<?php } ?>

    <a data-toggle="dropdown" class="dropdown-toggle" href="#" style="background-color: inherit" title="System Performance">
        <?php if ($performance_status=='check') { ?>
            <i class="fa fa-check"></i>
        <?php } else if ($performance_status=='warning') { ?>
            <i class="fa fa-exclamation-triangle"></i>
        <?php } else if ($performance_status=='danger') { ?>
            <i class="fa fa-exclamation-triangle"></i>
            <i class="fa fa-exclamation-triangle"></i>
            <i class="fa fa-exclamation-triangle"></i>
            <i class="fa fa-exclamation-triangle"></i>
            <i class="fa fa-exclamation-triangle"></i>
        <?php } ?>
    </a>
    <ul class="pull-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close notifications-drop-down">
        <li class="dropdown-header">
            <?=$performance_header_msg?>
        </li>

        <?php

        foreach($performance_msg as $msg) {

            $color = '';
            switch($msg['type']) {
                case 'warning':
                    $color = 'rgba(238, 215, 16, 1)';
                break;
                case 'danger':
                    $color = 'red';
                    break;
            }

            ?>
            <li style="line-height: 22px; padding: 10px 5px 10px 5px; font-size: 12px">
                <div style="border-bottom: 1px solid #e4ecf3;">
                    <span>
                        <i class="no-hover fa fa-exclamation-triangle" style="color:<?=$color?>; font-size: 20px"></i>
                        <?= html_entity_decode($msg['msg'])?>
                    </span>
                </div>
            </li>
            <?php
        }

        ?>


<!---->
<!--        <li>-->
<!--            <a href="#">-->
<!--                <div class="clearfix">-->
<!--									<span class="pull-left">-->
<!--										<i class="btn btn-xs no-hover btn-info fa fa-twitter"></i>-->
<!--										Followers-->
<!--									</span>-->
<!--                    <span class="pull-right badge badge-info">0</span>-->
<!--                </div>-->
<!--            </a>-->
<!--        </li>-->

    </ul>
</li>

<li class="grey">
  <a data-toggle="dropdown" href="#" class="dropdown-toggle" id="saWelcomeInfo">
    <span class="user-info">
      <small>Welcome,</small>
      <?=is_object($user) ? $user->getFirstName() : ''?>
    </span>

    <i class="fa fa-caret-down"></i>
  </a>

  <ul class="user-menu pull-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
    <li>
    <a href="@url('sa_settings')">
        <i class="fa fa-cogs"></i>
        Settings
    </a>
    </li>
    <li>
    <a href="url('sa_store')">
        <i class="fa fa-cloud-download"></i>
        Software Updates
    </a>
    </li>

    <li class="divider"></li>

    <li>
      <a href="@url('sa_logoff')">
        <i class="fa fa-power-off"></i>
        Log Out
      </a>
    </li>
  </ul>
</li>