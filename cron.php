<?php
include("settings.php");

function dLog($msg) {
	echo $msg."\r\n";
}

$db = new SQLite3('inc/uploads.db');
$db->busyTimeout(5000);
if($_SERVER['REMOTE_ADDR'] == $setting["server_ip"]) {
	$now = time();
	$errors = 0;
	$numdeleted = 0;
	dLog("Started cleanup at ".date('c', $now));

	$results = $db->query('SELECT filename, id, deleted FROM uploads WHERE ('.$now.'-datetime) > 604800'); // 1 week
	while($row = $results->fetchArray()) {
		if($row["deleted"] === 0){
			if(@unlink($setting["dir"].$row["filename"])) {
				$db->exec('DELETE FROM uploads WHERE id = "'.$row["id"].'"');
				dLog('deleted file: '.$row["filename"]."");
				$numdeleted++;
			} else {
				dLog('not deleted: '.$row["filename"]."");
				$errors++;
			}
		} else {
			$db->exec('DELETE FROM uploads WHERE id = "'.$row["id"].'"');
			dLog('cleaned log for already deleted file: '.$row["filename"]."");
		}
		
	}
	$db->exec('UPDATE cron SET datetime = '.$now.', numdeleted = '.$numdeleted.', errors = "'.$errors.'" ');

	dLog("Finished cleanup at ".date('c', $now)." - deleted: ".$numdeleted.", errors: ".$errors);
} else {
	$results = $db->querySingle('SELECT * FROM cron', true);
	echo "Zuletzt ausgeführt ".date('d.m.Y H:i:s', $results['datetime']).", ".$results['numdeleted']." gelöscht, ".$results['errors']." Fehler";
}
$db->close();
unset($db);
?>