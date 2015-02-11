<?php 
//TODO: 
//	form validation
//	delete single/all vids
//	rent/return vid
//	category filtering

// database credentials not included in the public git repo
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST')
	if ($_POST['request'] == 'add')
		addMovie();

// populate movie table
function popTable(){
	global $mysqli;
	$retval="";

	//prep & execute query
	if(!($stmt=$mysqli->prepare("SELECT id, name, category, length, rented FROM rstvideo")))
		{echo "Prepare failed: ".$mysqli->connect_errno." ".$mysqli->connect_error;}
	if(!($stmt->execute()))
		{echo "Execute failed: ".$mysqli->connect_errno." ".$mysqli->connect_error;}
	if(!($stmt->bind_result($id,$name,$category,$length,$rented)))
		{echo "Bind failed: ".$mysqli->connect_errno." ".$mysqli->connect_error;}
	
	while($stmt->fetch()){
		$retval.= "<tr><td>".$id." ".$name." ".$category." ".$length." ".$rented."</td></tr>";
	}

	if ($stmt->num_rows < 1) $retval="<tr><td>No videos! Add some to the inventory, ya dingus!</td></tr>";

	$stmt->close();
	return $retval;
}

// add a movie to the database
function addMovie(){
	global $mysqli;
	
	if(!($stmt = $mysqli->prepare("INSERT INTO rstvideo(name, category, length) VALUES(?,?,?)")))
		{echo "Prepare failed: ".$stmt->errno." ".$stmt->error;}
	if(!($stmt->bind_param("ssi", $_POST['name'], $_POST['category'], $_POST['length'])))
		{echo "Bind failed: ".$stmt->errno." ".$stmt->error;}
	if(!($stmt->execute()))
		{echo "Execute failed: ".$stmt->errno." ".$stmt->error;}
	else
		{echo "Added ".$stmt->affected_rows." successfully.";}

	$stmt->close();
}

$table = popTable();
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Welcome to RST Video!</title>
    <link href="style.css" rel="stylesheet" type="text/css" media="screen" />
    <script type="text/javascript" src="script.js"></script>
</head>

<body>
	<form method="post" action="index.php">
			<h2>Add Video</h2>
			<input type="hidden" name="request" value="add" />
			<p><label>Name of video: <input type="text" name="name" value=""/></label></p>
			<p><label>Category of video: <input type="text" name="category" value=""/></label></p>
			<p><label>Length (in minutes): <input type="number" min="0" name="length" required/></label></p>
		
			<p><input type="submit" name="add" value="Add video" /></p>
	</form>	
	<hr>
	<h2>Rent Videos</h2>
	<table id="movielist">
		<?php echo $table; ?> 
	</table>

  
</body>
</html>
