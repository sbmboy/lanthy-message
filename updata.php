<?php
header("Content-type: text/html; charset=utf-8");
function akismet_comment_check( $key, $data ) {
  $request = 'blog='. urlencode($data['blog']) .
          '&user_ip='. urlencode($data['user_ip']) .
          '&user_agent='. urlencode($data['user_agent']) .
          '&referrer='. urlencode($data['referrer']) .
          '&comment_author='. urlencode($data['comment_author']) .
          '&comment_author_email='. urlencode($data['comment_author_email']) .
          '&comment_author_url='. urlencode($data['comment_author_url']) .
          '&comment_content='. urlencode($data['comment_content']);
  $host = $http_host = $key.'.rest.akismet.com';
  $path = '/1.1/comment-check';
  $port = 443;
  $akismet_ua = "WordPress/4.4.1 | Akismet/3.1.7";
  $content_length = strlen( $request );
  $http_request  = "POST $path HTTP/1.0\r\n";
  $http_request .= "Host: $host\r\n";
  $http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
  $http_request .= "Content-Length: {$content_length}\r\n";
  $http_request .= "User-Agent: {$akismet_ua}\r\n";
  $http_request .= "\r\n";
  $http_request .= $request;
  $response = '';
  if( false != ( $fs = @fsockopen( 'ssl://' . $http_host, $port, $errno, $errstr, 10 ) ) ) {   
      fwrite( $fs, $http_request );
      while ( !feof( $fs ) )
          $response .= fgets( $fs, 1160 ); // One TCP-IP packet
      fclose( $fs );
      $response = explode( "\r\n\r\n", $response, 2 );
  }
  if ( 'true' == $response[1] )
      return true;
  else
      return false;
}
if(isset($_POST['email'])){
  // 包含配置文件
  include_once 'config.php';
  $_POST['message'] = trim($_POST['message']);
  $_POST['posturl']=$_SERVER['HTTP_REFERER'];
  $_POST['host']=$_SERVER['HTTP_HOST'];
  $_POST['ip']=$_SERVER['REMOTE_ADDR'];
  $_POST['posttime']=time();
  $_POST['useragent']=$_SERVER['HTTP_USER_AGENT'];
  $_POST['userlanguage']=$_SERVER['HTTP_ACCEPT_LANGUAGE'];
  $data = array('blog' => 'http://wxapp.lanthy.com/',
              'user_ip' => $_POST['ip'],
              'user_agent' => $_POST['useragent'],
              'referrer' => $_POST['posturl'],
              'comment_author' => $_POST['name'],
              'comment_author_email' => $_POST['email'],
              'comment_author_url' => '',
              'comment_content' => $_POST['message']);
  $_POST['status']=akismet_comment_check('0b6e94020e53',$data)?'trash':'inbox';
  $lanthy_message_dbname=glob("*.db");
  if(empty($lanthy_message_dbname)){
    $lanthy_message_dbname=md5(time()).'.db';
    $db = new SQLite3($lanthy_message_dbname,SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
    $db->exec("begin exclusive transaction");
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
    $db->exec("end transaction");
    $db->close();
  }else{
    $lanthy_message_dbname=$lanthy_message_dbname[0];
  }
  //打开数据库
  $db = new SQLite3($lanthy_message_dbname,SQLITE3_OPEN_READWRITE);
  $db->exec("begin exclusive transaction");
  $ziduan='';
  $value='';
  foreach($message as $k=>$v){
    if(isset($_POST[$k])){
      $_POST[$k] = $db->escapeString($_POST[$k]);
      $ziduan.='"'.$k.'",';
      $value.='"'.$_POST[$k].'",';
    }else{
      $ziduan.='"'.$k.'",';
      $value.='NULL,';
    }
  }
  $ziduan=substr($ziduan,0,-1);
  $value=substr($value,0,-1);
  $sql="INSERT INTO \"content\" ($ziduan) VALUES ($value)";
  $result=$db->exec($sql);
  $db->exec("end transaction");
  // 关闭数据库
  $db->close();
  unset($_POST);
  if($result){
    echo '<script>alert("Success! Thank You!");window.location.href="'.$_SERVER['HTTP_REFERER'].'"</script>';
  }else{
    echo '<script>alert("Failure1! Sorry!");window.location.href="'.$_SERVER['HTTP_REFERER'].'"</script>';
  }
  exit;
}else{
  echo '<script>alert("Failure2! Sorry!");window.location.href="'.$_SERVER['HTTP_REFERER'].'"</script>';
}
exit;
?>