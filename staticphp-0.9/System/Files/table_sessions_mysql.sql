CREATE TABLE "sessions" (
  "id" varchar(120) NOT NULL,
  "data" blob,
  "expires" int(11) NOT NULL,
  PRIMARY KEY  ("id"),
  KEY "expires" ("expires")
) ENGINE=innoDB DEFAULT CHARSET=utf8;