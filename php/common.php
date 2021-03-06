<?php
$VAL_CLIENT_SIDE=1; // variable that controls the client side validations. If 1 enables that validation
?>
 
 <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
           
            <link rel="stylesheet" type="text/css" href="<?php echo get_bloginfo('wpurl');?>/custom/css/screen.css">
            <link rel="stylesheet" type="text/css" href="<?php echo get_bloginfo('wpurl');?>/custom/css/table.css">
            <link rel="stylesheet" type="text/css" href="<?php echo get_bloginfo('wpurl');?>/custom/css/paging.css">
            <link rel="stylesheet" type="text/css" href="<?php echo get_bloginfo('wpurl');?>/custom/css/form.css">
            <link rel="stylesheet" type="text/css" href="<?php echo get_bloginfo('wpurl');?>/custom/css/jquery-ui.css">
            
            
            <script type="text/javascript" src="<?php echo get_bloginfo('wpurl');?>/custom/js/jquery-1.12.3.min.js"></script> 
            <script type="text/javascript" src="<?php echo get_bloginfo('wpurl');?>/custom/js/jquery-migrate-1.4.0.js"></script> 
            <script type="text/javascript" src="<?php echo get_bloginfo('wpurl');?>/custom/js/jquery-ui.min.js"></script>
           
               <script src="<?php echo get_bloginfo('wpurl');?>/custom/js/jquery.balloon.min.js">
            </script> <script type="text/javascript" src="<?php echo get_bloginfo('wpurl');?>/custom/js/showBallon.js"></script>
            </script> <script type="text/javascript" src="<?php echo get_bloginfo('wpurl');?>/custom/js/showAttr.js"></script>
            
            
            
            
            <script type="text/javascript" src="<?php echo get_bloginfo('wpurl');?>/custom/js/jquery.tablesorter.min.js"></script>
            <script type="text/javascript" src="<?php echo get_bloginfo('wpurl');?>/custom/js/jquery.tablesorter.pager.js"></script>
            <script type="text/javascript" src="<?php echo get_bloginfo('wpurl');?>/custom/js/tableSorter.js"></script>
            
           
         
            <script type="text/javascript" src="<?php echo get_bloginfo('wpurl');?>/custom/js/datepicker.js"></script>
<?php
        if ($VAL_CLIENT_SIDE)
        {
?>
            
            <script type="text/javascript" src="<?php echo get_bloginfo('wpurl');?>/custom/js/jquery.validate.js"></script>
            <script type="text/javascript" src="<?php echo get_bloginfo('wpurl');?>/custom/js/formValidation.js"></script>

<?php
        }
?>
            
	</head>
</html>
 <?php
 

/**
 * This class will handle all the operations related to the database
 * @author fabio
 *
 */
class Db_Op
{
    //DB_HOST,DB_USER,DB_PASSWORD,DB_NAME these are contants present in the wordpress
    private $mysqli;
  
    //This method will make the database connection
    public function __construct()
    {
      $this->mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
      if($this->mysqli->connect_errno)
      {
      	printf("Connect failed: %s\n", $mysqli->connect_error);
      	exit();
      }
    }
    
    public function getMysqli()
    {
        return $this->mysqli;
    }
	
    //This method will receive a String(query) and will process it 
    //If the query received starts with 
    // SELECT, SHOW, DESCRIBE, EXPLAIN it will return a mysqli_result object
    // else it will return true and at last if something goes wrong after/during the query 
    // run the result that will be returned is false.
    public function runQuery($query)
    {
    	$result = $this->mysqli->query($query);
	    if(!$result)
	    {
	    	echo $query."<br>".$this->mysqli->error;
	    }
	    else
	    {
	    	return $result;
	    }

    }
    //This method will receive a string and will apply 
    public function userInputVal($string)
    {
    	return $this->mysqli->real_escape_string($string);
    }
    
    //This method will disconnect a database connection
    public function disconnectToDb()
    {
      $this->mysqli->close();
    }
    
    //This method will receive a table name, a field name and will get all the value fron the field name. 
    public function getEnumValues($table, $field) 
    {
    	$enum_array = array();
    
    	$query = 'SHOW COLUMNS FROM `' . $table . '` LIKE "' . $field . '"';
    	$result = $this->runQuery($query);

    		$row = $result->fetch_row();
    		//Trata a coluna onde está o enum e guarda o valor em enum_arry
    		preg_match_all("/'(.*?)'/", $row[1], $enum_array);
    
    		if(!empty($enum_array[1]))
    		{
    			// Shift array keys to match original enumerated index in MySQL (allows for use of index values instead of strings)
    
    			foreach($enum_array[1] as $mkey => $mval)
    			{
    				$enum_fields[$mkey+1] = $mval;
    			}
    			return $enum_fields;
    
    		}
    		else
    		{
    			return array(); // Return an empty array to avoid possible errors/warnings if array is passed to foreach() without first being checked with !empty().
    		}
    	}
        
    /**
     * Method that returns the entity name with the given ID
     * @param int $id of the entity
     * @return string the name od the entity
     */
    public function getEntityName($id) {
        $queryEnt = "SELECT name FROM ent_type WHERE id = ".$id;
        $nome = $this->runQuery($queryEnt)->fetch_assoc()["name"];
        return $nome;
    }
    
    /**
     * This method is responsible to automaticly create the name of the relations by joining the names of the two entities that are associated
     * @param string $queryNome1 (The query that gets the name of the first entity)
     * @param string  $queryNome2 (The query that gets the name of the second entity)
     * @return string the name of the relation
     */
    public function criaNomeRel($queryNome1, $queryNome2)
    {
        $nome1 = $this->runQuery($queryNome1)->fetch_assoc()["name"];
        $nome2 = $this->runQuery($queryNome2)->fetch_assoc()["name"];
        $nome = $nome1."-".$nome2;
        return $nome;
    }
    
    /**
     * This method checks if the date is in a format that can be used in MySQL queries
     * @param type $date
     * @return boolean
     */
    public function validaDatas($date) {
        $format = "Y-m-d";
        $d = DateTime::createFromFormat($format, $date);
        if(!($d && $d->format($format) == $date)) {
?>
            <p>A data introduzida não é válida. Certifique-se que esta se encontra no formato AAAA-MM-DD</p>
<?php
            goBack();
            return false;
        }
        else {
            return true;
        }
    }
   
}




















    //Array para a componente pesquisa dinâmica.

   function operadores()
   {
        $operadores = array(
            "menor"=>"<",
            "maior"=>">",
            "igual"=>"=",
            "diferente"=>"!=",
            "maiorouigual"=>">=",
            "menorouigual"=>"<="
            );
        return $operadores;
   }

	function goBack()
	{
		echo "<script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");</script>
		<noscript>
		<a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
		</noscript>";
	}


	
?>
