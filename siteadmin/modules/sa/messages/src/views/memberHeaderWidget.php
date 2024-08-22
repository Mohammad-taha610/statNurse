<li class="grey">

    <?php if ($is_logged_in) { ?>
        <a data-toggle="dropdown" href="#" class="dropdown-toggle">
            <?php if ($avatar) { ?>
                <img class="nav-user-photo" src="<?= $url->make('member_profile_miniavatar') ?>"
                     alt="Photo of <?= $first_name ?>">
            <?php } else { ?>
                <i class="nav-user-photo fa fa-user" style="padding: 5px 20px 5px 7px"></i>
            <?php } ?>
            <span class="user-info"> Welcome, <?= $first_name ?> </span>
            <i class="fa fa-caret-down"></i>
        </a>
        <ul class="user-menu pull-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
            <li>
                <a href="<?= $url->make('member_profile') ?>">
                    <i class="fa fa-user" style="width: 25px"></i>
                    Profile
                </a>
            </li>
            <li>
                <a href="<?= $url->make('member_profile') ?>?r=1#edit-usernames">
                    <i class="fa fa-users" style="width: 25px"></i>
                    Manage Users
                </a>
            </li>
            <li class="divider"></li>
            <li>
                <a href="<?= $url->make('member_logoff') ?>">
                    <i class="fa fa-power-off" style="width: 25px"></i>
                    Logout
                </a>
            </li>
        </ul>
    <?php } else { ?>

        <li>
            <a href="<?= $url->make('member_signup') ?>">
                Sign Up
            </a>
        </li>

        <li>
            <a href="<?= $url->make('member_login') ?>">
                Sign In
            </a>
        </li>

    <?php } ?>

</li>