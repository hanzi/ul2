<?php
include("settings.php");
try {
	if(isset($_POST["id"]) && ctype_digit($_POST["id"])) {
		$db = new SQLite3('inc/uploads.db');
		$db->busyTimeout(5000);
		$results = $db->querySingle('SELECT filename FROM uploads WHERE ip = "'.$_SERVER["REMOTE_ADDR"].'" AND id = "'.$_POST["id"].'"');
		$unlink = @unlink($setting["dir"].$results);
		$setdeleted = $db->exec('UPDATE uploads SET deleted = 1 WHERE id = "'.$_POST["id"].'"');
		if($unlink && $setdeleted) {
			echo json_encode(array("success" => "Deleted"));
		} else {
			throw new Exception("Error deleting ".$setdeleted, 1);
		}
	} else {
		throw new Exception("Error deleting", 1);
	}
} catch (Exception $e) {
	echo json_encode(array("error" => "Could not delete.", "exception" => $e->getMessage()));
}
$db->close();
unset($db);