<?php
include("settings.php");
if(!isset($_GET['pw']) || $_GET['pw'] != $settings["adminpassword"]) die('access denied');
?>
<table class="table table-condensed table-striped" id="myfiles">
	<thead>
		<tr>
		<th>id</th>
		<th>ip</th>
		<th>datetime</th>
		<th>filename</th>
		<th>crc32</th>
		</tr>
	</thead>
	<tbody>
<?php
if($db = new SQLite3('inc/uploads.db')) {
	$db->busyTimeout(5000);
	$results = $db->query('SELECT id, ip, datetime, filename, crc32 FROM uploads WHERE deleted = 0');
	while($row = $results->fetchArray()) { 
?>

<tr>
<td><?= $row["id"] ?></td>
<td><?= $row["ip"] ?></td>
<td><?= date('c', $row["datetime"]) ?></td>
<td><a href="<?= $setting["url_to_files"].$row["filename"] ?>"><?= $row["filename"] ?></a></td>
<td><?= $row["crc32"] ?></td>
</tr>
<?php
	}
	$db->close();
	unset($db);
}
?>
</tbody></table>



