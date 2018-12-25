<?php
/**
 * class LanthyMessage
 */
class LanthyMessage{
	public $db;
	function __construct($dbfile=null){
		try{
			$this->db=new SQLite3($dbfile?$dbfile:"db",SQLITE3_OPEN_READONLY);
		}catch(Exception $e){
            echo '<div class="wrap"><h1 class="wp-heading-inline">出错了！</h1><p>'.$e->getMessage().'</p></div>';
			die();
		}
	}
	function __destruct(){
		if($this->db)$this->db->close();
	}
	function getData($sql){
		$result=$this->db->query($sql) or die("Error:".$sql);
		$ret=array();
		while($row=$result->fetchArray(SQLITE3_ASSOC))$ret[]=$row;
		$result->finalize();
		unset($result);
		unset($row);
		return $ret;
	}
	function getLine($sql,$type=true){
		return $this->db->querySingle($sql,$type);
	}
	function getPostsSql($sql){
		return $this->getData($sql);
    }
	function getPostsNum($table,$sql=""){
		$sql="select count(rowid) from {$table} {$sql}";
		return intval($this->getLine($sql,false));
	}
	function getPost($id){
		$sql="select rowid,* from 'Content' Where 'delete' > 0 and rowid=".intval($id);
		return $this->getLine($sql);
	}
	function getListPage($table,$sql){
		$sql="select rowid,* from {$table} {$sql}";
		return $this->getData($sql);
	}
}