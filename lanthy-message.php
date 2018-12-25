<?php
/**
 * Plugin Name: 蓝悉留言管理
 * Plugin URI: https://www.lanthy.com
 * Description: 蓝悉科技为客户定制开发的在线留言功能，包括管理，查看，自动过滤垃圾邮件等功能，请在模板中添加&lt;div id="lanthy-message"&gt;&lt;/div&gt;标记。
 * Version: 2.40
 * Author: 朱海龙
 */

// 激活插件
register_activation_hook( __FILE__, 'lanthy_message_install');
function lanthy_message_install() {
    // 引入配置文件
    include_once plugin_dir_path(__FILE__).'config.php';
    // 创建数据库
    $db=new SQLite3(plugin_dir_path(__FILE__).md5(time()).'.db',SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
    /**创建数据表 */
    $sql='';
    foreach($message as $k=>$v){
      $sql .= '"'.$k.'" varchar,';
    }
    $sql=substr($sql,0,-1);
    $sql = "CREATE TABLE IF NOT EXISTS \"content\" (".$sql.")";
    $db->exec($sql);
    /*建立索引*/
    foreach($message as $k=>$v){
      $db->exec("create index if not exists \"INDEX_{$k}\" on \"content\" (\"{$k}\")");
    }
    $test['name']='Lanthy';
    $test['email']='zhuhailong@lanthy.com';
    $test['message']='这是一条测试信息，如果你在使用过程中出现任何问题，欢迎你联系我：zhuhailong@lanthy.com';
    $test['posttime']=time();
    $test['posturl']='http://'.$_SERVER['HTTP_HOST'];

    $test['status']='inbox';
    $ziduan='';
    $value='';
    foreach($message as $k=>$v){
        if(isset($test[$k])){
            $test[$k] = $db->escapeString($test[$k]); // 启用gzcompress压缩
            $ziduan.='"'.$k.'",';
            $value.='"'.$test[$k].'",';
        }else{
            $ziduan.='"'.$k.'",';
            $value.='NULL,';
        }
    }
    $ziduan=substr($ziduan,0,-1);
    $value=substr($value,0,-1);
    $sql="INSERT INTO \"content\" ($ziduan) VALUES ($value)";
    $db->exec($sql);
    $db->close();

    // 生成js文件

    // 生成接收文件
    // file_put_content(get_bloginfo('wpurl').'post.php',file_get_content(plugin_dir_path(__FILE__).'postMessage.cof'));
}

// 取消激活
register_deactivation_hook( __FILE__, 'lanthy_message_uninstall' );
function lanthy_message_uninstall() {
    // 删除数据库
    $lanthy_message_dbname=glob(plugin_dir_path(__FILE__)."*.db");
    foreach($lanthy_message_dbname as $v){
        unlink($v);
    }  
}

// 给页面添加一个js
add_action( 'get_footer', 'lanthy_message_footer_js', 10 );
function lanthy_message_footer_js() {
    echo '<script src="'.plugins_url('js/message.js',__FILE__).'"></script>';
}

// 创建菜单
add_action( 'admin_menu', 'lanthy_message_menu', 10, 0 );
function lanthy_message_menu(){
    // 创建顶级菜单
    add_menu_page( 
        '在线留言', // page Title
        '留言管理', // menu Title
        'manage_options', 
        'lanthymessage', // slug
        'lanthy_message_show_all', // function
        'dashicons-email',  // icon
        12 // 菜单的位置
    );
    // 创建子菜单
    add_submenu_page( 
        'lanthymessage', // 父级的slug
        '未读留言', // title
        '未读留言', // menu
        'manage_options',
        'unreadmessage',
        'lanthy_message_show_unread' // show
    );
    add_submenu_page( 
        'lanthymessage', // 父级的slug
        '垃圾留言', // title
        '垃圾留言', // menu
        'manage_options',
        'spammessage',
        'lanthy_message_show_spam' // show
    );
}

// 显示全部询盘
function lanthy_message_show_all(){
    include_once plugin_dir_path(__FILE__).'functions.php';
    $lanthy_message_dbname=glob(plugin_dir_path(__FILE__)."*.db");
    $lanthy_message_dbname=$lanthy_message_dbname[0];
    $lanthy_message=new LanthyMessage($lanthy_message_dbname);
    $message_all = $lanthy_message->getPostsNum('content',"Where \"status\"='inbox'");
    $message_unread = $lanthy_message->getPostsNum('content',"Where \"opentime\" is NULL and \"status\"='inbox'");
    $message_trash = $lanthy_message->getPostsNum('content',"Where \"status\"='trash'");
    if(isset($_GET['action'])){
        switch ($_GET['action']) {
            case 'view':
                # code...
                if(isset($_GET['id'])&&$_GET['id']>0){
                    $message_detail=$lanthy_message->getPost($_GET['id']);
                    if(is_null($message_detail['opentime'])){
                        $sql="UPDATE 'Content' SET 'opentime' = ".time()." WHERE rowid='{$_GET['id']}'";
                        $db = new SQLite3($lanthy_message_dbname,SQLITE3_OPEN_READWRITE);
                        $result=$db->exec($sql);
                        $db->close();
                        $message_detail['opentime']=time();
                    }
                    require_once plugin_dir_path(__FILE__). 'config.php';
                    require_once plugin_dir_path(__FILE__). 'show-detail.php';
                }else{
                    echo '<script>window.location.href="'.$_SERVER['HTTP_REFERER'].'";</script>';
                    exit;
                }
                break;

            case 'delete':
                # code...
                if(isset($_GET['id'])){
                    $db = new SQLite3($lanthy_message_dbname,SQLITE3_OPEN_READWRITE);
                    if(is_array($_GET['id'])){
                        foreach($_GET['id'] as $id){
                            $sql="UPDATE \"Content\" SET \"status\" = 'trash' WHERE rowid = ".intval($id);
                            $db->exec($sql);
                        }
                    }else{
                        $sql="UPDATE \"Content\" SET \"status\" = 'trash' WHERE rowid = ".intval($_GET['id']);
                        $db->exec($sql);
                    }
                    $db->close();
                    echo '<script>window.location.href="/wp-admin/admin.php?page='.$_GET['page'].'";</script>';
                    exit;
                }else{
                    echo '<script>window.location.href="'.$_SERVER['HTTP_REFERER'].'";</script>';
                    exit;
                }
                break;
            default:
                # code...
                break;
        }
    }else{
        if(isset($_GET['_paged'])) $page=intval($_GET['_paged']); else $page=0;
        $sql='order by posttime DESC';
        $num=15;
        $count=$lanthy_message->getPostsNum('content',"Where \"status\"='inbox' ".$sql);
        $message=$lanthy_message->getListPage('content',"Where \"status\"='inbox' {$sql} limit ".($page-1)*$num.",{$num}");
        require_once plugin_dir_path(__FILE__). 'show-list.php';
    }
}

// 显示未读询盘
function lanthy_message_show_unread(){
    include_once plugin_dir_path(__FILE__).'functions.php';
    $lanthy_message_dbname=glob(plugin_dir_path(__FILE__)."*.db");
    $lanthy_message_dbname=$lanthy_message_dbname[0];
    $lanthy_message=new LanthyMessage($lanthy_message_dbname);
    $message_all = $lanthy_message->getPostsNum('content',"Where \"status\"='inbox'");
    $message_unread = $lanthy_message->getPostsNum('content',"Where \"opentime\" is NULL and \"status\"='inbox'");
    $message_trash = $lanthy_message->getPostsNum('content',"Where \"status\"='trash'");
    if(isset($_GET['action'])){
        switch ($_GET['action']) {
            case 'view':
                # code...
                if(isset($_GET['id'])&&$_GET['id']>0){
                    $message_detail=$lanthy_message->getPost($_GET['id']);
                    if(is_null($message_detail['opentime'])){
                        $sql="UPDATE 'Content' SET 'opentime' = ".time()." WHERE rowid='{$_GET['id']}'";
                        $db = new SQLite3($lanthy_message_dbname,SQLITE3_OPEN_READWRITE);
                        $result=$db->exec($sql);
                        $db->close();
                    }
                    require_once plugin_dir_path(__FILE__). 'config.php';
                    require_once plugin_dir_path(__FILE__). 'show-detail.php';
                }else{
                    echo '<script>window.location.href="'.$_SERVER['HTTP_REFERER'].'";</script>';
                    exit;
                }
                break;

            case 'delete':
                # code...
                if(isset($_GET['id'])){
                    $db = new SQLite3($lanthy_message_dbname,SQLITE3_OPEN_READWRITE);
                    if(is_array($_GET['id'])){
                        foreach($_GET['id'] as $id){
                            $sql="UPDATE \"Content\" SET \"status\" = 'trash' WHERE rowid = ".intval($id);
                            $db->exec($sql);
                        }
                    }else{
                        $sql="UPDATE \"Content\" SET \"status\" = 'trash' WHERE rowid = ".intval($_GET['id']);
                        $db->exec($sql);
                    }
                    $db->close();
                    echo '<script>window.location.href="/wp-admin/admin.php?page='.$_GET['page'].'";</script>';
                    exit;
                }else{
                    echo '<script>window.location.href="'.$_SERVER['HTTP_REFERER'].'";</script>';
                    exit;
                }
                break;

            default:
                # code...
                break;
        }
    }else{
        if(isset($_GET['_paged'])) $page=intval($_GET['_paged']); else $page=0;
        $sql='order by posttime DESC';
        $num=15;
        $count=$lanthy_message->getPostsNum('content',"Where \"opentime\" is NULL and \"status\"='inbox' ".$sql);
        $message=$lanthy_message->getListPage('content',"Where \"opentime\" is NULL and \"status\"='inbox' {$sql} limit ".($page-1)*$num.",{$num}");
        require_once plugin_dir_path(__FILE__). 'show-list.php';
    }
}

// 显示垃圾询盘
function lanthy_message_show_spam(){
    include_once plugin_dir_path(__FILE__).'functions.php';
    $lanthy_message_dbname=glob(plugin_dir_path(__FILE__)."*.db");
    $lanthy_message_dbname=$lanthy_message_dbname[0];
    $lanthy_message=new LanthyMessage($lanthy_message_dbname);
    $message_all = $lanthy_message->getPostsNum('content',"Where \"status\"='inbox'");
    $message_unread = $lanthy_message->getPostsNum('content',"Where \"opentime\" is NULL and \"status\"='inbox'");
    $message_trash = $lanthy_message->getPostsNum('content',"Where \"status\"='trash'");
    if(isset($_GET['action'])){
        switch ($_GET['action']) {
            case 'reduction':
                # code...
                if(isset($_GET['id'])){
                    $db = new SQLite3($lanthy_message_dbname,SQLITE3_OPEN_READWRITE);
                    if(is_array($_GET['id'])){
                        foreach($_GET['id'] as $id){
                            $sql="UPDATE \"Content\" SET \"status\" = 'inbox' WHERE rowid = ".intval($id);
                            $db->exec($sql);
                        }
                    }else{
                        $sql="UPDATE \"Content\" SET \"status\" = 'inbox' WHERE rowid = ".intval($_GET['id']);
                        $db->exec($sql);
                    }
                    $db->close();
                    echo '<script>window.location.href="/wp-admin/admin.php?page='.$_GET['page'].'";</script>';
                    exit;
                }else{
                    header('Location:'.$_SERVER['HTTP_REFERER']);
                    exit;
                }
                break;

            case 'delete':
                # code...
                if(isset($_GET['id'])){
                    $db = new SQLite3($lanthy_message_dbname,SQLITE3_OPEN_READWRITE);
                    if(is_array($_GET['id'])){
                        foreach($_GET['id'] as $id){
                            $sql="DELETE FROM \"Content\" WHERE rowid = ".intval($id);
                            $db->exec($sql);
                        }
                    }else{
                        $sql="DELETE FROM \"Content\" WHERE rowid = ".intval($_GET['id']);
                        $db->exec($sql);
                    }
                    $db->close();
                    echo '<script>window.location.href="/wp-admin/admin.php?page='.$_GET['page'].'";</script>';
                    exit;
                }else{
                    echo '<script>window.location.href="'.$_SERVER['HTTP_REFERER'].'";</script>';
                    exit;
                }
                break;

            default:
                # code...
                break;
        }
    }else{
        if(isset($_GET['_paged'])) $page=intval($_GET['_paged']); else $page=0;
        $sql='order by posttime DESC';
        $num=15;
        $count=$lanthy_message->getPostsNum('content',"Where \"status\"='trash' ".$sql);
        $message=$lanthy_message->getListPage('content',"Where \"status\"='trash' {$sql} limit ".($page-1)*$num.",{$num}");
        require_once plugin_dir_path(__FILE__). 'show-spam.php';
    }
}
?>