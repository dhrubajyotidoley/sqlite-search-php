<?php
include_once('php_sqlite_rank.php');
$limit = 10;

$db = new SQLite3('edocreader.db');
$db->createFunction('rank', 'sql_rank');

/*get the search variable from URL*/
$var = SQLite3::escapeString($_REQUEST['q']);

/*get pagination*/
$s = SQLite3::escapeString($_REQUEST['s']);
?>

<?php     
if(strlen($var) < 3){
    $resultmsg =  "<p>Search Error</p><p>Keywords with less then three characters are omitted...</p>" ;
}

/*trim whitespace from the stored variable*/
$trimmed = trim($var);
$trimmed1 = trim($var);
/*separate key-phrases into keywords*/
$trimmed_array = explode(" ",$trimmed);
$trimmed_array1 = explode(" ",$trimmed1);

/* check for an empty string and display a message.*/
if ($trimmed == "") {
    $resultmsg =  "<p>Search Error</p><p>Please enter a search...</p>" ;
}
 
/* check for a search parameter*/
if (!isset($var)){
    $resultmsg =  "<p>Search Error</p><p>We don't seem to have a search parameter! </p>" ;
}

/* Build SQLite Query for each keyword entered*/
foreach ($trimmed_array as $trimm){

$query ="SELECT *, COUNT(*) as count FROM documents WHERE documents MATCH ('".$trimm."') ORDER BY rank(matchinfo(documents), 0, 1.0, 0.5) DESC;";

/* Execute the query to  get number of rows that contain search kewords*/
$numresults=$db->query($query);
$row = $numresults->fetchArray();
$row_num_links_main = $row['count'];

 /*If MATCH query doesn't return any results due to how it works do a search using LIKE*/
 if($row_num_links_main < 1){
    $query = "SELECT *, COUNT(*) as count FROM documents WHERE title LIKE '%$trimm%' OR category LIKE '%$trimm%' OR desc LIKE '%$trimm%' ORDER BY id DESC";
    	$numresults=$db->query($query);
		$row = $numresults->fetchArray();
		$row_num_links_main1 = $row['count'];
 }
 
 /* next determine if 's' has been passed to script, if not use 0.
  's' is a variable that gets set as we navigate the search result pages.*/
 if (empty($s)) {
     $s=0;
 }
 
  /* now let's get results.*/
  $query .= " LIMIT $s, $limit" ;
  $numresults = $db->query($query) or die ( "Couldn't execute query" );
  $row=  $numresults->fetchArray();
 
  /*store record id of every item that contains the keyword in the array we need to do this to avoid display of duplicate search result.*/
  do{
      $adid_array[] = $row[ 'id' ];
  }while( $row= $numresults->fetchArray());
} /*end foreach*/
 
/*Display a message if no results found*/
if($row_num_links_main == 0 && $row_num_links_main1 == 0){
    $resultmsg = "<p>Search results for: ". $trimmed."</p><p>Sorry, your search returned zero results</p>" ;
}
 
/*delete duplicate record id's from the array. To do this we will use array_unique function*/
$tmparr = array_unique($adid_array);
$i=0;
foreach ($tmparr as $v) {
   $newarr[$i] = $v;
   $i++;
}
 
/*total result*/
$row_num_links_main = $row_num_links_main + $row_num_links_main1;
 
/* display an error or, what the person searched*/
if( isset ($resultmsg)){
    echo $resultmsg;
}else{
    echo "<p>Search results for: <strong>" . $var."</strong></p>";
 
    foreach($newarr as $value){
 
    /* EDIT HERE and specify your table and field unique ID for the SQLite query*/
	$sql = "SELECT * from documents where id=".$value;
	$result = $db->query($sql);
	$row = array();
	$i = 0;
	while($res = $result->fetchArray(SQLITE3_ASSOC)){
		$content = implode(' ', array_slice(explode(' ', $res[desc]), 0, 20));
		echo 'Title: ' . $res[title] . '<br>  Content: ' . $content;
		$i++;
	}
 
    }  /*end foreach $newarr*/
 
    if($row_num_links_main > $limit){
    /* next we need to do the links to other search result pages*/
        if ($s >=1) { // do not display previous link if 's' is '0'
            $prevs=($s-$limit);
            echo '<div class="search_previous"><a href="'.$PHP_SELF.'?s='.$prevs.'&q='.$var.'">Previous</a>
            </div>';
        }
    /* check to see if last page*/
        $slimit =$s+$limit;
        if (!($slimit >= $row_num_links_main) && $row_num_links_main!=1) {
            /* not last page so display next link*/
            $n=$s+$limit;
            echo '<div  class="search_next"><a href="'.$PHP_SELF.'?s='.$n.'&q='.$var.'">Next</a>
            </div>';
        }
    }/*end if $row_num_links_main > $limit*/
}/*end if search result*/
?> 
