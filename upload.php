<?php
include("settings.php");

// upload processing
if(count($_FILES) >= 1) {
	try {
		$path = pathinfo($_FILES["file-upload"]["name"]);
		$extension = $path["extension"];
		$filename = basename($_FILES["file-upload"]["name"], ".".$extension);

		if(in_array($extension, $setting["disallow_extension"])) { echo json_encode(array('error' => 'Error uploading '.htmlspecialchars($_FILES["file-upload"]["name"]))); die(); }
		if(in_array($filename, $setting["disallow_filename"])) { echo json_encode(array('error' => 'Error uploading '.htmlspecialchars($_FILES["file-upload"]["name"]))); die(); }

		$characterset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$rnd = "";
		for($i = 1; $i <= 3; $i++) {
			$rnd .= $characterset[rand(0, strlen($characterset)-1)];
		}

		// Zeichen filtern, Leerzeichen zu "_"
		$filename = preg_replace(array('/\s/', '/[^a-zA-Z0-9_\-\.]/'), array('_', ''), $filename);
		
		// Dateinamen nach dem Schema ".htaccess"
		if($filename == $extension || $extension == "") {
			$new_filename = substr($filename, 0, 20)."-".$rnd;
			if($_GET["mode"] == "canvas") {
				$new_filename = "canvasimg-".$rnd.".jpg";
			}
		} else {
			$new_filename = substr($filename, 0, 20)."-".$rnd.".".substr($extension, 0, 10);
		}
		
		$db = new SQLite3('inc/uploads.db');
		$db->busyTimeout(5000);
		if(move_uploaded_file($_FILES["file-upload"]["tmp_name"], $setting["dir"].$new_filename) && $db) {
			// insert file into db
			$db->exec('INSERT INTO uploads (ip, datetime, filename, crc32) VALUES ("'.$_SERVER['REMOTE_ADDR'].'", '.time().', "'.$new_filename.'", "'.crc32(file_get_contents($setting["dir"].$new_filename)).'")');
			// get the file's id
			$lastid = $db->query('SELECT last_insert_rowid() FROM uploads');
			$lastfileid = $lastid->fetchArray();
			// close db
			$db->close();
			unset($db);
			if(isset($_GET["mode"]) && $_GET["mode"] == "legacy") { 
				echo '<html>
	<head>
		<title>Upload - Ul3</title>
		<script type="text/javascript" src="scripts.js"></script>
		<link rel="icon" href="favicon.png" type="image/png">
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
	<div id="status"><img src="fff/tick.png" class="fff" alt="Icon Success"> Datei hochgeladen:<br><br>';
				echo '<a class="link" href="'.$setting["url_to_files"].$new_filename.'">'.$new_filename.'</a><br>';
				echo '<input type="text" class="link linklegacy" value="'.$setting["url_to_files"].$new_filename.'"><br><br>';
				echo '<a href="'.$setting["url_to_ul3"].'"><img src="fff/arrow_left.png" class="fff" alt="Icon Back" align="middle"> Zurück</a>';
				echo '</div></body>';
			} else {
				echo json_encode(array('success' => $lastfileid[0], 'url' => $setting["url_to_files"].$new_filename, 'filename' => $new_filename));
			}
		} else {
			echo json_encode(array('error' => 'Error uploading '.htmlspecialchars($_FILES["file-upload"]["name"]))); die();
		}	
	} catch (Exception $e) {
		echo json_encode(array('error' => 'Something bad happened')); die();
	}
	
} else  {
	die();
}
?>