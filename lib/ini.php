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
  define ("SOFTWAREVERSION", "1.19.08");
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
  
  // spotify constants

  define ("SPOTIFYAPI", "https://api.spotify.com/v1");
  define ("SPOTIFYTOKENAPI", "https://accounts.spotify.com/api/token");

  // open opus 

  define ("OPENOPUS", "http://api.openopus.org");

  // helper libraries

  include_once (UTILIB. "/lib.php");
  include_once (LIB. "/lib.php");

  // likely trashy compilation albums

  define ("COMPILATION_TERMS", "clas sleep rela babies kids mind lounge essen best dream");

  // api init

  $starttime = microtime (true);
  $apireturn = Array ("status" => Array ("version" => SOFTWAREVERSION));

  // db init

  $mysql = mysqli_connect (DBHOST, DBUSER, DBPASS, INSTANCE. "_". DBDB);
  mysqli_set_charset ($mysql, "utf8");
