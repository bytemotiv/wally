DROP TABLE "sessions";

CREATE TABLE IF NOT EXISTS "categories" (
"id"	INTEGER NOT NULL,
"name"	TEXT NOT NULL,
"icon"	TEXT NOT NULL,
"color"	TEXT NOT NULL,
PRIMARY KEY("id" AUTOINCREMENT)
);

CREATE TABLE IF NOT EXISTS "markers" (
"id"	INTEGER,
"name"	TEXT NOT NULL,
"lat"	REAL NOT NULL,
"lng"	REAL NOT NULL,
"notes"	TEXT,
"category"	INTEGER,
"rating"	INTEGER,
"address"	TEXT,
"sourceGeocoder"	TEXT,
"sourceId"	TEXT,
PRIMARY KEY("id" AUTOINCREMENT)
);

CREATE TABLE IF NOT EXISTS "ratings" (
"id"	INTEGER,
"name"	TEXT,
"icon"	TEXT,
PRIMARY KEY("id" AUTOINCREMENT)
);

CREATE TABLE IF NOT EXISTS `sessions` (
`session_id` VARCHAR(255),
`data` TEXT,
`ip` VARCHAR(45),
`agent` VARCHAR(300),
`stamp` INTEGER,
PRIMARY KEY (`session_id`)
);

CREATE TABLE IF NOT EXISTS "sharing" (
"key"	TEXT NOT NULL UNIQUE,
"created"	TEXT,
"valid_until"	TEXT,
"type"	TEXT,
"value"	TEXT
);

CREATE TABLE IF NOT EXISTS "tags" (
"marker"	INTEGER NOT NULL,
"tag"	TEXT NOT NULL,
PRIMARY KEY("marker","tag")
);

CREATE TABLE IF NOT EXISTS "uploads" (
"marker"	INTEGER NOT NULL,
"image"	TEXT NOT NULL,
"thumb"	TEXT,
"date"	TEXT,
PRIMARY KEY("marker","image")
);
