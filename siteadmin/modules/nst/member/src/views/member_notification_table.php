
<table class="table notificationsTable">
    <?php
        foreach($notifications as $note) {
            ?>
            <tr class="row">
                <?php
                    if($note['image']) {
                        ?><td class="image"><img src="<?=$note['image'] ?>" class="img-responsive"/></td>
                    <?php }
                ?>
                <td class="message">
                    <?=$note['link'] ? '<a href="' . $note['link'] . '">' : '' ?>
                    <?=$note['message'] ?>
                    <?=$note['link'] ? '</a>' : '' ?>
                </td>
                <td class="date">
                    <?=date("M d, Y H:i:s",strtotime($note['date_created'])) ?>
                </td>
            </tr>
            <?php
        }
    ?>
</table>