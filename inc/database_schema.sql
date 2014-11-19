-- this is where the cronjob script saves its status
CREATE TABLE "cron" ( 
	'datetime' DATETIME NOT NULL, 
	numdeleted INT NOT NULL, 
	errors INT NOT NULL
);

-- this is where the uploaded files' metadata is stored
CREATE TABLE "uploads" ( 
	id INTEGER PRIMARY KEY AUTOINCREMENT, 
	ip TEXT NOT NULL, 'datetime' DATETIME NOT NULL, 
	filename TEXT NOT NULL, crc32 INT, 
	deleted INT DEFAULT 0 
);