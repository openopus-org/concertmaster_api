<?
  // removing notices

  error_reporting (E_ALL ^ (E_WARNING | E_NOTICE));

  // header json

  if (!$nojson)
  {
    header ("Content-Type: application/json");
  }

  // global constants

  define ("SOFTWARENAME", "Concertmaster");
  define ("SOFTWAREVERSION", "0.19");
  define ("USERAGENT", SOFTWARENAME. "/" . SOFTWAREVERSION. " ( ". SOFTWAREMAIL. " )");
  define ("RECRETS", 60);
  define ("MIN_SIMILAR", 40);
  define ("MIN_COMPIL_RATIO", 0.6);
  define ("API_RETURN", "json");
  define ("HASH_SALT", "vUJmLwFgniCBmqcreBbsX9Jb");

  // spotify constants

  define ("SPOTIFYAPI", "https://api.spotify.com/v1");
  define ("SPOTIFYTOKENAPI", "https://accounts.spotify.com/api/token");

  // forbidden and bad labels

  $forbidden_labels = ['Classical Archives','Music@Menlo LIVE','Amadis','Yoyo USA','Classico','CD Accord','Vista Vera','CMS Live','Summit Records'];
  $historical_labels = ['British Music Society','SOMM Recordings','West Hill Radio Archives','Music and Arts Programs of America','IDIS','Audite','Archiphon','Fono','Pierian Recording Society'];

  // performer roles keywords

  $orchestra_kw = ['orchestra', 'symphony', 'philharmonic', 'philharmoniker', 'philharmonie', 'symphoniker', 'orchester', 'academy', 'orchestre', 'orchestra', 'orquesta', 'orquestra', 'orkester', 'philharmonia', 'academie', 'academia', 'accademia', 'akademie', 'society', 'societe', 'societa', 'sinfonietta', 'camerata', 'sinfonia', 'staatskapelle', 'strings', 'collegium'];
  $ensemble_kw = ['ensemble', 'quartet', 'quintet', 'trio', 'duo', 'players', 'solisti', 'chamber'];
  $choir_kw = ['choir', 'chorus', 'choral', 'cantorum', 'coro', 'singers', 'kammerchor', 'voices', 'kantorei', 'rundfunkchor', 'singakademie', 'vocale', 'knabenchor', 'singverein', 'sangerknaben', 'scholars'];

  // helper library

  include_once (LIB. "/lib.php");

  // api init

  $starttime = microtime (true);
  $apireturn = Array ("status" => Array ("version" => SOFTWAREVERSION));

  // db init

  $mysql = mysqli_connect (DBHOST, DBUSER, DBPASS, INSTANCE. "_". DBDB);
  mysqli_set_charset ($mysql, "utf8");
