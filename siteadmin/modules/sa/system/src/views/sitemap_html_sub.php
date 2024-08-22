<ul>

<?php 

foreach($items as $item) {
	?>
    <li>
        <a href="<?=$item['loc'] ?>"><?=$item['title'] ?></a>
        <?php if ($item['children_markup'] && $item['children_markup'] != '') { ?>
            <?= $item['children_markup'] ?>
        <?php } ?>
    </li>
    <?php
} ?>

</ul>