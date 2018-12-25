<div class="wrap">
    <h1 class="wp-heading-inline">留言详细</h1>
    <a href="<?=$_SERVER['HTTP_REFERER']?>" class="page-title-action">返回上级</a>
    <table class="form-table">
        <tbody>
            <?php
                foreach($message as $k=>$v):
            ?>
            <tr>
                <th scope="row"><label for="blogname"><?=$v?></label></th>
                <td><p class="description" id="tagline-description"><?=strstr($k,'time')?date("Y/m/d H:i:s",$message_detail[$k]):$message_detail[$k]?></p></td></td>
            </tr>
            <?php
                endforeach;
            ?>
        </tbody>
    </table>
    <p class="submit">
        <a href="?page=<?=$_GET['page']?>&action=delete&id=<?=$message_detail['rowid']?>" class="button button-primary">放入垃圾箱</a>
    </p>
</div>