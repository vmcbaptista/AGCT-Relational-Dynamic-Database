<?php
require_once("custom/php/common.php");
	
	$getData = new FetchData();

class FetchData{
	
	private $bd;
	
	public function __construct(){
		$this->bd = new Db_Op();
		$this->getData();
	}
	
	public function getData(){
		$sanitizeId = $this->bd->userInput($_REQUEST['ent']);
		while($read_Props = $this->bd->runQuery("SELECT * FROM value WHERE relation_id=".$sanitizeId)->fetch_assoc())
		{
			$nome = $this->bd->runQuery("SELECT * FROM property WHERE property_id=".$read_Props['id'])->fetch_assoc()['name'];
			echo $nome . " : " .$read_Props['value']."</bd>";
		}
	}
}
?>