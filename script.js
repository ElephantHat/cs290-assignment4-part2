function delMovie(idNum){
	$.ajax({
		type: "POST",
		url: 'index.php',
		data:{request:'delete', id: idNum},
        success:function(html) {
			window.location.replace("index.php");
		},
		error: function(html){
			alert("Request failed. Couldn't delete movie. Sorry.");
		}
	});
 }

function rentMovie(idNum, rentedStatus){
	$.ajax({
		type: "POST",
		url: 'index.php',
		data:{request:'rent', id: idNum, rented: rentedStatus},
        success:function(html) {
			window.location.replace("index.php");
		},
		error: function(html){
			alert("Request failed. Couldn't rent movie. Sorry.");
		}
	});
 }
