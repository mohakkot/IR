<?php
// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');
$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;
if ($query)
{
 // The Apache Solr Client library should be on the include path
 // which is usually most easily accomplished by placing in the
 // same directory as this script ( . or current directory is a default
 // php include path entry in the php.ini)
 require_once('solr-php-client/Apache/Solr/Service.php');
 // create a new solr service instance - host, port, and corename
 // path (all defaults in this example)
 $solr = new Apache_Solr_Service('localhost', 8983, '/solr/csci572/');
 // if magic quotes is enabled then stripslashes will be needed
 if (get_magic_quotes_gpc() == 1)
 {
 $query = stripslashes($query);
 }
 // in production code you'll always want to use a try /catch for any
 // possible exceptions emitted by searching (i.e. connection
 // problems or a query parsing error)
 try
 {
	if($_REQUEST['searchtype']=="external")
	{
		$param = array('sort' => 'pageRankFile desc');
		$results = $solr->search($query, 0, $limit,$param);
	}
	else
	{
		$results = $solr->search($query, 0, $limit);
	}
 }
 catch (Exception $e)
 {
 // in production you'd probably log or email this error to an admin
 // and then show a special message to the user but for this example
 // we're going to show the full exception
 die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
 }
}
//start reading csv
$num = [];
$CSVfp = fopen("NYDMap.csv", "r");
if($CSVfp !== FALSE) {
 while(($data = fgetcsv($CSVfp, 1000, ",")) !== FALSE){
  $num[$data[0]] = $data[1];
 }
}
fclose($CSVfp);
$counter=0;
foreach ($num as $key => $value) {
    //echo "$key   &nbsp;&nbsp;$value<br>";
    
    $counter++;
    //echo $counter;
    //echo "<br>";
}
//end of reading csv
?>
<html>
 <head>
 <title>PHP Solr Client</title>
 <style>
 body {background-color: lightyellow}
 </style>
 </head>
 <body>
 <center><h2 style="align:center;color:red">Solr Query Screen</h2>
 <form accept-charset="utf-8" method="get">
 <label for="q" style="align:center">Search Box:</label>
 <input id="q" name="q" type="text" style="align:center; width:40%" placeholder="Enter Your Query Here" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
 <input type="submit"/></center>
<br>
<center><input type="radio" name="searchtype" value="inbuilt"checked>Lucene Search</input>
<input type="radio" name="searchtype" value="external"> Pagerank Search</input></center><br>
 </form>
<?php
// display results
if ($results)
{
 $total = (int) $results->response->numFound;
 $start = min(1, $total);
 $end = min($limit, $total);
?>
 <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
 <ol>
<?php
 // iterate result documents
 foreach ($results->response->docs as $doc)
 {
?>
 <li>
 <table style="width:100%;border: 1px solid black; text-align: left">
<?php
 // iterate document fields / values

$valueid = "N/A";
$valueurl = "N/A";
$valuedesc = "N/A";
$valuetit = "N/A";
 foreach ($doc as $field => $value)
 {
	if($field =="id" || $field == "description" || $field=="title")
	{
		if($field == "id")
		{
			$pos=strripos($value,"/");
			$valueid = $value;
			$val=substr($value,$pos+1);
			$valueurl=$num[$val];
		}
		if($field == "description")
		{
			$valuedesc = $value;
		}
		if($field == "title")
		{
			$valuetit = $value;
		}
 	}
 }
?>
 <tr>
 <th><?php echo htmlspecialchars("Title", ENT_NOQUOTES, 'utf-8'); ?></th>
 <td><a href=<?php echo $valueurl;?> ><?php echo htmlspecialchars($valuetit, ENT_NOQUOTES, 'utf-8'); ?></td>
 </tr>
 <tr>
 <th><?php echo htmlspecialchars("URL", ENT_NOQUOTES, 'utf-8'); ?></th>
 <td><a href=<?php echo $valueurl;?> ><?php echo htmlspecialchars($valueurl, ENT_NOQUOTES, 'utf-8'); ?></td>
 </tr>
 <tr>
 <th><?php echo htmlspecialchars("ID", ENT_NOQUOTES, 'utf-8'); ?></th>
 <td><?php echo htmlspecialchars($valueid, ENT_NOQUOTES, 'utf-8'); ?></td>
 </tr>
 <tr>
 <tr>
 <th><?php echo htmlspecialchars("Description", ENT_NOQUOTES, 'utf-8'); ?></th>
 <td><?php echo htmlspecialchars($valuedesc, ENT_NOQUOTES, 'utf-8'); ?></td>
 </tr>
 <br>
 </table>
 </li>
<?php }
?>
</ol>
<?php
}
?>
 </body>
</html>
