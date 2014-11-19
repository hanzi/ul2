<?php
include("settings.php");

function dLog($msg) {
	file_put_contents("inc/log.txt", $msg."\r\n", FILE_APPEND);
}

$db = new SQLite3('inc/uploads.db');
$db->busyTimeout(5000);
if($_SERVER['REMOTE_ADDR'] == $setting["server_ip"]) {
	$now = time();
	$errors = 0;
	$numdeleted = 0;
	dLog(date('c', $now));

	$results = $db->query('SELECT filename, id FROM uploads WHERE deleted = 0 AND ('.$now.'-datetime) > 604800'); // 1 week
	while($row = $results->fetchArray()) {
		if(@unlink($setting["dir"].$row["filename"])) {
			$db->exec('UPDATE uploads SET deleted = 1 WHERE id = "'.$row["id"].'"');
			dLog('deleted: '.$row["filename"]."");
			$numdeleted++;
		} else {
			dLog('not deleted: '.$row["filename"]."");
			$errors++;
		}
	}
	$db->exec('UPDATE cron SET datetime = '.$now.', numdeleted = '.$numdeleted.', errors = "'.$errors.'" ');

	echo "deleted: ".$numdeleted.", errors: ".$errors;
	dLog("deleted: ".$numdeleted.", errors: ".$errors);
	dLog("----------");
} else {
	$results = $db->querySingle('SELECT * FROM cron', true);
	echo "Zuletzt ausgeführt ".date('d.m.Y H:i:s', $results['datetime']).", ".$results['numdeleted']." gelöscht, ".$results['errors']." Fehler";
}
$db->close();
unset($db);
?>