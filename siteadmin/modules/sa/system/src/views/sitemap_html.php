<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <h1>Sitemap</h1>

            Below is a helpful site map of our website. To return home, <a href="/">click here</a>.
        </div>
    </div>


    <?php


    foreach($keyed_items as $module=>$items) {
        ?>
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-4">
                <h2><?=ucwords($module)?></h2>
            </div>
        </div>


        <?php
        $per_column = ceil(count($items) / 2);

        $lastlevel = 1;
        ?>

        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-6">

                <ul>
                    <?php
                    $count = 0;
                    foreach($items as $item) {

                        if (!isset($item['level']))
                            $item['level'] = 1;

                        if ($count>=$per_column && $item['level']==1)
                            break;

                        if ($lastlevel!=$item['level']) {
                            if ($item['level'] > $lastlevel) {
                                ?>
                                <ul class="">
                                <?php
                            }
                            elseif ($item['level'] < $lastlevel) {
                                for($i=$lastlevel; $item['level']<$i; $i--) {
                                    ?>
                                    </ul>
                                    <?php
                                }
                            }

                            $lastlevel = $item['level'];

                        }

                        ?>
                        <li>
                            <a href="<?=$item['loc'] ?>"><?=$item['title'] ?></a>

                            <?php if ($item['children_markup'] && $item['children_markup'] != '') { ?>
                                <?= $item['children_markup'] ?>
                            <?php } ?>
                        </li>
                        <?php


                        $count++;



                    }

                    for($i=$lastlevel; 1<$i; $i--) {
                    ?>
                </ul>
                <?php
                }
                ?>
                </ul>

            </div>


            <?php
            $lastlevel = 1;
            ?>


            <div class="col-xs-12 col-sm-12 col-md-6">

                <ul>
                    <?php
                    for($a=$count; $a<count($items); $a++) {

                        $item = $items[$a];

                        if (!isset($item['level']))
                            $item['level'] = 1;


                        if ($lastlevel!=$item['level']) {
                            if ($item['level'] > $lastlevel) {
                                ?>
                                <ul class="">
                                <?php
                            }
                            elseif ($item['level'] < $lastlevel) {
                                for($i=$lastlevel; $item['level']<$i; $i--) {
                                    ?>
                                    </ul>
                                    <?php
                                }
                            }

                            $lastlevel = $item['level'];

                        }

                        ?>
                        <li>
                            <a href="<?=$item['loc'] ?>"><?=$item['title'] ?></a>
                        </li>

                        <?php if ($item['children_markup'] && $item['children_markup'] != '') { ?>
                                <?= $item['children_markup'] ?>
                        <?php } ?>

                        <?php

                    }

                    for($i=$lastlevel; 1<$i; $i--) {
                    ?>
                </ul>
                <?php
                }
                ?>
                </ul>

            </div>
        </div>

        <?php
    }



    ?>

</div>