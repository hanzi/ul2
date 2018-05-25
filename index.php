<?php
include("settings.php");
?>
<!DOCTYPE html>
<html lang="de">
	<head>
		<title>Upload - Ul3.5</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<script type="text/javascript" src="js/jquery-2.1.0.min.js"></script>
		<script type="text/javascript" src="js/bootstrap.min.js"></script>
		<script type="text/javascript" src="js/scripts.js"></script>
		<link rel="icon" href="favicon.png" type="image/png">
		<link rel="stylesheet" href="css/bootstrap.min.css">
		<link rel="stylesheet" href="css/style.css">
		<meta name="robots" content="noindex, nofollow">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
	</head>
	<body>

	<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				</button>
				<a href=""><div id="branding"></div></a>
			</div>

			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav">
					<li><a href="#"><span class="glyphicon glyphicon-open"></span> Upload</a></li>
					<li><a href="#history">Hochgeladene Dateien</a></li>
					<!-- <li><a href="#draw">Malen & Einfügen<sup class="text-danger">Beta</sup></a></li> -->
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<!-- <li><a href="#admin">Admin</a></li> -->
				</ul>
			</div>
		</div>
	</nav>

	<div class="jumbotron" id="uploadform">
		<div class="container">
			<h1>Upload<small>Script</small></h1>
			<form action="upload.php?mode=legacy" method="post" enctype="multipart/form-data" id="upload-form">
				<div class="btn-group">
				<span class="btn btn-primary btn-file btn-lg">
					<span class="glyphicon glyphicon-open"></span>
					Dateien auswählen & hochladen
					<input type="file" id="file-upload" name="file-upload" multiple>
				</span>
				<!-- <button type="submit" name="submit" id="submit" class="btn btn-lg btn-default"><span class="glyphicon glyphicon-open"></span> Jetzt hochladen</button> -->
				</div>
			</form>

			<p class="text-muted" id="dragdroptext"><small><br>Oder: Dateien mit der Maus hier reinziehen.</small></p>

			<br><br>

			<div id="progressbars">
			</div>

		</div>
	</div>

	<div class="container">
		<h2>Hinweise</h2>
		<ul>
			<li>Dateien werden zusammen mit deiner IP <strong>IP <?= $_SERVER["REMOTE_ADDR"] ?></strong> bis zu <strong>7 Tage</strong> aufbewahrt.</li>
			<li>Die maximale Dateigröße beträgt <strong>25 MB</strong>.</li>
			<li>Unter "Hochgeladene Dateien" stehen alle Dateien, die unter deiner <strong>IP <?= $_SERVER["REMOTE_ADDR"] ?></strong> hochgeladen wurden.</li>
		</ul>
	</div>

	<div class="container" id="history">
		<h2>Hochgeladene Dateien</h2>

		<table class="table table-condensed table-hover" id="myfiles">
			<thead>
				<tr>
					<th class="col-md-10">Dateiname</th>
					<th class="text-right">Datei löschen</th>
				</tr>
			</thead>
			<tbody>
<?php
$db = new SQLite3('inc/uploads.db');
$db->busyTimeout(5000);
if($db) {
	$now = time();
	$results_cnt = $db->querySingle('SELECT count(*) FROM uploads WHERE ip = "'.$_SERVER["REMOTE_ADDR"].'" AND deleted = 0');
	if($results_cnt != '0'){
		$results = $db->query('SELECT filename, datetime, id FROM uploads WHERE ('.$now.'-datetime) < 86400 AND ip = "'.$_SERVER["REMOTE_ADDR"].'" AND deleted = 0 ORDER BY datetime DESC');
		while ($row = $results->fetchArray()) {
			echo '<tr id="f'.$row["id"].'">
				<td class="filename"><a href="'.$setting["url_to_files"].''.$row["filename"].'" title="'.date('d.m.Y H:i:s', $row["datetime"]).'">'.$row["filename"].'</a></td>
				<td class="text-right deletelink"><a data-id="'.$row["id"].'" class="text-danger"><span class="glyphicon glyphicon-remove"></span> Löschen</a></td>
			</tr>';
		}
	} else {
		echo '<tr id="emptyhistory"><td><em>Noch nichts hochgeladen</em></td><td></td></tr>'.PHP_EOL;
	}
} else {
		echo '<tr><td><em>Datenbankfehler</em></td><td></td></tr>'.PHP_EOL;
}
$db->close();
unset($db);
?>
			</tbody>
		</table>
	</div>

	<div class="container" id="admin">
		<h3>Admin</h3>
		<form method="POST">
		<div class="input-group col-md-3">
			<span class="input-group-addon">
				<span class="glyphicon glyphicon-lock"></span>
			</span>
			<input id="pw" type="password" class="form-control" placeholder="Passwort">
			<span class="input-group-btn">
				<button class="btn btn-default" type="submit"><span class="glyphicon glyphicon-log-in"></span></button>
			</span>
		</div>
		</form>
		<div id="admin-result"></div>
		<div class="container"></div>
	</div>

	<div class="container" id="admin">
		<h3>ShareX config</h3>
		<pre>{
  "Name": "ul2",
  "DestinationType": "ImageUploader, TextUploader, FileUploader",
  "RequestURL": "<?= $setting["url_to_ul3"] ?>upload.php",
  "FileFormName": "file-upload",
  "URL": "$json:url$"
}</pre>
	</div>

	<div class="hidden"></div>
	<p>&nbsp;</p>
	</body>
</html>