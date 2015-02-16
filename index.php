<?php 

//for troubleshooting
$loopy = false;


// database credentials not included in the public git repo
include 'db.php';

// validate
$errors = array(
	'add' => array (
		'err' => false,
		'msg' => ''
	),
	'popcat' => array (
		'err' => false,
		'msg' => ''
	),
	'poptable' => array (
		'err' => false,
		'msg' => ''
	),
	'validate' => array (
		'err' => false,
		'msg' => ''
	),
	'rent' => array (
		'err' => false,
		'msg' => ''
	),
	'delete' => array (
		'err' => false,
		'msg' => ''
	)
);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['request']=='add'){
	$_POST['name'] = trim($_POST['name']);
	$_POST['category'] = trim($_POST['category']);
	if($_POST['name'] == ''){
		$errors['validate']['err'] = true;
		$errors['validate']['msg'].= "Name cannot be blank.<br>";
	}

// Commented out to comply with assignment testing description	
//	if($_POST['length'] == ''){
//		$errors['validate']['err'] = true;
//		$errors['validate']['msg'].= "Length cannot be blank.<br>";
//	}
	if($errors['validate']['err'])
		$errors['validate']['msg'].="Video not added. Please try again.<br>";

// Commented out to comply with assignment testing description	
//	if($_POST['category'] == '')
//		$_POST['category'] = 'Uncategorized';
	if (!$errors['validate']['err'])
		addMovie();
}
else if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['request']=='delete'){
		delMovie($_POST['id']);
}
else if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['request']=='rent'){
		rentMovie($_POST['id'], $_POST['rented']);
}

// rent movie
function rentMovie($id, $rented){
	global $mysqli;
	global $errors;	

	if($rented == '1')
		$rented = 0;
	else if ($rented == '0')
		$rented = 1;

	//prep & execute query
	if(!($stmt=$mysqli->prepare("UPDATE rstvideo SET rented=".$rented." WHERE id='".$id."'"))){
		$errors['rent']['err']=true; $errors['rent']['msg'].=
			"MYSQL query prepare failed.<br>";
	}
	if(!($stmt->execute())){
		$errors['rent']['err']=true; $errors['rent']['msg'].=
			"MYSQL query execution failed.<br>";
	}
	
	while($stmt->fetch()){
	}

	if ($stmt->num_rows < 1) $retval= ($errors['rent']['err']) ? 
		$errors['rent']['msg']."Couldn't rent video." 
		: "Video rented";
	



}
// delete movie
function delMovie($id){
	global $mysqli;
	global $errors;	


	$query = ($id=="allMovies") ? "DELETE FROM rstvideo" : "DELETE FROM rstvideo WHERE id='".$id."'";

	//prep & execute query
	if(!($stmt=$mysqli->prepare($query))){
		$errors['delete']['err']=true; $errors['delete']['msg'].=
			"MYSQL query prepare failed.<br>";
	}
	if(!($stmt->execute())){
		$errors['delete']['err']=true; $errors['delete']['msg'].=
			"MYSQL query execution failed.<br>";
	}
	
	while($stmt->fetch()){
	}

	if ($stmt->num_rows < 1) $retval= ($errors['delete']['err']) ? 
		$errors['delete']['msg']."Couldn't delete video." 
		: "Video deleted";
	
}

// populate category filter 
function popCat(){
	global $mysqli;
	global $errors;
	$retval="";

	//prep & execute query
	if(!($stmt=$mysqli->prepare("SELECT DISTINCT category FROM rstvideo ORDER BY category"))){
		$errors['popcat']['err']=true; $errors['popcat']['msg'].=
			"MYSQL query prepare failed.<br>";
	}
	if(!($stmt->execute())){
		$errors['popcat']['err']=true; $errors['popcat']['msg'].=
			"MYSQL query execution failed.<br>";
	}
	if(!($stmt->bind_result($category))){
		$errors['popcat']['err']=true; $errors['popcat']['msg'].=
			"MYSQL query results binding failed.<br>";
	}
	
	while($stmt->fetch()){
		if ($category=="") 
			$retval.= "";
		else
		   $retval.= (isset($_POST['request']) && $_POST['request'] == 'filter' && $category == $_POST['category'])	?  "<option value='".htmlspecialchars($category)."' selected='selected'>".htmlspecialchars($category)."</option>" :
"<option value='".htmlspecialchars($category)."'>".htmlspecialchars($category)."</option>";

	}

	if ($stmt->num_rows < 1) $retval= ($errors['popcat']['err']) ? 
		"<td><tr>".$errors['popcat']['msg']."Couldn't retrieve video inventory.</td></tr>" 
		: "<tr><td>No videos! Add some to the inventory, ya dingus!</td></tr>";

	$stmt->close();	
	return $retval;
}
// populate movie table
function popTable(){
	global $mysqli;
	global $errors;
	$retval="";

	//prep & execute query
	$query = "SELECT id, name, category, length, rented FROM rstvideo";
	$query.= (isset($_POST['request']) && $_POST['request'] == "filter" && $_POST['category'] != "allMovies")  ? " WHERE category='".$_POST['category']."' ORDER BY name" : " ORDER BY name"; 
	if(!($stmt=$mysqli->prepare($query))){
		$errors['poptable']['err']=true; $errors['poptable']['msg'].=
			"MYSQL query prepare failed.<br>";
	}
	if(!($stmt->execute())){
		$errors['poptable']['err']=true; $errors['poptable']['msg'].=
			"MYSQL query execution failed.<br>";
	}
	if(!($stmt->bind_result($id,$name,$category,$length,$rented))){
		$errors['poptable']['err']=true; $errors['poptable']['msg'].=
			"MYSQL query results binding failed.<br>";
	}
	
	while($stmt->fetch()){
		$rented = "<button onClick='rentMovie(".$id.", ".$rented.")'>".$val=($rented>0) ? "Checked Out" : "Available" ."</button>";
		$delete = "<button onClick='delMovie(".$id.")'>Delete</button>";
		$retval.= "<tr id='".$id."'><td>".htmlspecialchars($name)."</td><td>".htmlspecialchars($category)."</td><td>".htmlspecialchars($length)."</td><td>".$rented."</td><td>".$delete."</td></tr>";
	}

	if ($stmt->num_rows < 1) $retval= ($errors['poptable']['err']) ? 
		"<td><tr>".$errors['poptable']['msg']."Couldn't retrieve video inventory.</td></tr>" 
		: "<tr><td>No videos! Add some to the inventory, ya dingus!</td><td>--</td><td>--</td></tr>";

	$stmt->close();	
	return $retval;
}

// add a movie to the database
function addMovie(){
	global $mysqli;
	global $errors;
	
	if(!($stmt = $mysqli->prepare("INSERT INTO rstvideo(name, category, length) VALUES(?,?,?)"))){
		$errors['add']['err']=true; $errors['add']['msg'].=
			"MYSQL query prepare failed, record not added.<br>";
	}
	if(!($stmt->bind_param("ssi", $_POST['name'], $_POST['category'], $_POST['length']))){
		$errors['add']['err']=true; $errors['add']['msg'].=
			"MYSQL query parameter binding failed, record not added.<br>";
	}
	if(!($stmt->execute())){
		$errors['add']['err']=true; $errors['add']['msg'].=
			"MYSQL query execution failed, record not added.<br>";
	}
	else
		{echo "Added video successfully.";}

	$stmt->close();
}

$table = popTable();
$categories = popCat();
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Welcome to RST Video!</title>
    <link href="style.css" rel="stylesheet" type="text/css" media="screen" />
    <script type="text/javascript" src="script.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
</head>

<body>
<?php
if($errors['validate']['err'])
	echo $errors['validate']['msg'];
if($errors['add']['err'])
	echo $errors['add']['msg'];


if(!$loopy)
	echo '<form method="post" action="index.php">';
else
	echo '<form method="post" action="http://web.engr.oregonstate.edu/~burleigb/cs290/cs290-assignment4-part1/src/loopback.php">';
?>
			<h2>Add Video</h2>
			<input type="hidden" name="request" value="add" />
			<p><label>Name of video: <input type="text" name="name" required/></label></p>
			<p><label>Category of video: <input type="text" name="category" /></label></p>
			<p><label>Length (in minutes): <input type="number" min="0" name="length" ></label></p>
		
			<p><input type="submit" name="add" value="Add video" /></p>
	</form>	
	<hr>
	<h2>Rent Videos</h2>
	<form method="post" action="index.php">
	<select name='category'>
	<option value="allMovies">--All--</option>
	<?php echo $categories; ?>
	</select>
	<input type="submit" value="Filter" />
	<input type="hidden" name = 'request' value="filter" />
	</form>

	<form method="post" action="index.php">
	<input type="submit" value="Delete ALL movies" />
	<input type="hidden" name = 'request' value="delete" />
	<input type="hidden" name = 'id' value="allMovies" />
	</form>


	<table id="movielist">
	<tr class="darkgrey" ><td>Movie Name</td><td>Category</td><td>Length</td></tr>
		<?php echo $table; ?> 
	</table>
</body>
</html>
