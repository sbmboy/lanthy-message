<div class="wrap">
    <h1 class="wp-heading-inline">所有留言</h1>
    <hr class="wp-header-end">
    <ul class="subsubsub">
        <li class="all"><a href="admin.php?page=lanthymessage" <?php if($_GET['page']=='lanthymessage') echo 'class="current"'; ?>>全部<span class="count">（<?=$message_all?>）</span></a> |</li>
        <li class="publish"><a href="admin.php?page=unreadmessage"<?php if($_GET['page']=='unreadmessage') echo 'class="current"'; ?>>未读<span class="count">（<?=$message_unread?>）</span></a> |</li>
        <li class="draft"><a href="admin.php?page=spammessage"<?php if($_GET['page']=='spammessage') echo 'class="current"'; ?>>垃圾箱<span class="count">（<?=$message_trash?>）</span></a></li>
    </ul>
    <form id="posts-filter" method="get">
        <input type="hidden" name="page" value="<?=$_GET['page']?>">
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <label for="bulk-action-selector-top" class="screen-reader-text">选择批量操作</label>
                <select name="action" id="bulk-action-selector-top">
                    <option value="-1">批量操作</option>
                    <option value="delete">移至回收站</option>
                </select>
                <input type="submit" id="doaction" class="button action" value="应用">
            </div>
            <div class="tablenav-pages">
                <span class="displaying-num"><?=$count?>个项目</span>
                <span class="pagination-links">
                    <?php if($page>1): ?>
                    <a class="prev-page" href="?page=lanthymessage&_paged=<?php echo (($page==2||$page==0)?"":($page-1));?>"><span class="screen-reader-text">上一页</span><span aria-hidden="true">‹</span></a>
                    <?php else: ?>
                    <span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
                    <?php endif; ?>
                    <span class="screen-reader-text">当前页</span>
                    <span id="table-paging" class="paging-input"><span class="tablenav-paging-text">第<?=$page?>页，共<span class="total-pages"><?=1+intval($count/$num)?></span>页</span></span>
                    <?php
                    if($page<1+intval($count/$num)):?>
                        <a class="next-page" href="?page=lanthymessage&_paged=<?=$page==0?'2':$page+1?>"><span class="screen-reader-text">下一页</span><span aria-hidden="true">›</span></a>
                    <?php else: ?>
                        <span class="tablenav-pages-navspan" aria-hidden="true">›</span>
                    <?php endif; ?>
                </span>
                <br class="clear">
            </div>
            <table class="wp-list-table widefat fixed striped posts">
                <thead>
                    <tr>
                        <td id="cb" class="manage-column column-cb check-column">
                            <label class="screen-reader-text" for="cb-select-all-1">全选</label>
                            <input id="cb-select-all-1" type="checkbox">
                        </td>
                        <th scope="col" id="message" class="manage-column column-message column-primary"> <span>留言信息</span></th>
                        <th scope="col" id="name" class="manage-column column-name column-tags">姓名</th>
                        <th scope="col" id="email" class="manage-column column-email column-tags">邮箱</th>
                        <th scope="col" id="referrer" class="manage-column column-referrer column-title">来源网址</th>
                        <th scope="col" id="date" class="manage-column column-date"><span>留言日期</span></th>
                    </tr>
                </thead>
                <tbody id="the-list">
                    <?php
                    foreach($message as $v):
                    ?>
                    <tr>
                        <th scope="row" class="check-column">
                            <input type="checkbox" name="id[]" value="<?=$v['rowid']?>">
                        </th>
                        <td class="title column-message has-row-actions column-primary page-title" data-colname="留言信息">
                            <abbr title="<?=$v['message']?>">
                                <?php if(is_null($v['opentime'])): ?>
                                    <strong><?php echo substr($v['message'],0,60); ?>— <span class="post-state">未读</span></strong>
                                <?php else: ?>
                                    <?php echo substr($v['message'],0,60); ?>
                                <?php endif; ?>
                            </abbr>
                            <div class="row-actions">
                                <span class="edit"><a href="?page=<?=$_GET['page']?>&action=view&id=<?=$v['rowid']?>" aria-label="查看">查看</a> | </span>
                                <span class="trash"><a href="?page=<?=$_GET['page']?>&action=delete&id=<?=$v['rowid']?>" class="submitdelete" aria-label="移至回收站">移至回收站</a></span>
                            </div>
                        </td>
                        <td class="name column-name" data-colname="姓名"><?=$v['name']?></td>
                        <td class="email column-email" data-colname="邮箱"><?=$v['email']?></td>
                        <td class="referrer column-referrer" data-colname="来源网址"><?=$v['posturl']?></td>
                        <td class="date column-date" data-colname="留言日期"><abbr title="<?=date("Y/m/d H:i:s",$v['posttime'])?>"><?=date("Y-m-d",$v['posttime'])?></abbr></td>
                    </tr>
                    <?php
                    endforeach;
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="manage-column column-cb check-column">
                            <label class="screen-reader-text" for="cb-select-all-2">全选</label>
                            <input id="cb-select-all-2" type="checkbox">
                        </td>
                        <th scope="col" id="message" class="manage-column column-message column-primary"> <span>留言信息</span></th>
                        <th scope="col" id="name" class="manage-column column-name">姓名</th>
                        <th scope="col" id="email" class="manage-column column-email">邮箱</th>
                        <th scope="col" id="referrer" class="manage-column column-referrer">来源网址</th>
                        <th scope="col" id="date" class="manage-column column-date"><span>留言日期</span></th>
                    </tr>
                </tfoot>
            </table>
            <div class="tablenav top">
                <div class="tablenav-pages">
                    <span class="displaying-num"><?=$count?>个项目</span>
                    <span class="pagination-links">
                        <?php if($page>1): ?>
                        <a class="prev-page" href="?page=lanthymessage&_paged=<?php echo (($page==2||$page==0)?"":($page-1));?>"><span class="screen-reader-text">上一页</span><span aria-hidden="true">‹</span></a>
                        <?php else: ?>
                        <span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
                        <?php endif; ?>
                        <span class="screen-reader-text">当前页</span>
                        <span id="table-paging" class="paging-input"><span class="tablenav-paging-text">第<?=$page?>页，共<span class="total-pages"><?=1+intval($count/$num)?></span>页</span></span>
                        <?php
                        if($page<1+intval($count/$num)):?>
                            <a class="next-page" href="?page=lanthymessage&_paged=<?=$page==0?'2':$page+1?>"><span class="screen-reader-text">下一页</span><span aria-hidden="true">›</span></a>
                        <?php else: ?>
                            <span class="tablenav-pages-navspan" aria-hidden="true">›</span>
                        <?php endif; ?>
                    </span>
                    <br class="clear">
                </div>
            </div>
        </div>
    </form>
</div>