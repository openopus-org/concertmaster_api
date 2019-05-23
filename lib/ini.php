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
  define ("SOFTWAREVERSION", "1.19.05");
  define ("USERAGENT", SOFTWARENAME. "/" . SOFTWAREVERSION. " ( ". SOFTWAREMAIL. " )");
  define ("RECRETS", 60);
  define ("MIN_SIMILAR", 40);
  define ("MIN_COMPIL_RATIO", 0.8);
  define ("MIN_COMPIL_UNIVERSE", 10);
  define ("MIN_RELEVANCE_PERFORMER", 5);
  define ("SAPI_ITEMS", 50);
  define ("SAPI_PAGES", 5);
  define ("API_RETURN", "json");
  define ("HASH_SALT", "vUJmLwFgniCBmqcreBbsX9Jb");
  define ("CATALOGUE_REGEX", "/\,( )*(bwv|hwv|op|opus|d|k|kv|hess|woo|fs|k\.anh|wq|w|sz|kk|m|s|h|trv|rv|jw ([a-z]+\/)|(hob\.([a-z])+\:))( |\.)*(([0-9]+)([a-z])?)/i");

  // spotify constants

  define ("SPOTIFYAPI", "https://api.spotify.com/v1");
  define ("SPOTIFYTOKENAPI", "https://accounts.spotify.com/api/token");

  // performer roles keywords

  $orchestra_kw = ['orchestra', 'symphony', 'philharmonic', 'philharmoniker', 'philharmonie', 'symphoniker', 'orchester', 'academy', 'orchestre', 'orchestra', 'orquesta', 'orquestra', 'orkester', 'orkest', 'philharmonia', 'academie', 'academia', 'accademia', 'akademie', 'society', 'societe', 'societa', 'sinfonietta', 'camerata', 'sinfonia', 'staatskapelle', 'strings', 'collegium', 'consortium'];
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
