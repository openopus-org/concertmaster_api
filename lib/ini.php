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
  define ("SOFTWAREVERSION", "1.21");
  define ("USERAGENT", SOFTWARENAME. "/" . SOFTWAREVERSION. " ( ". SOFTWAREMAIL. " )");
  define ("RECRETS", 60);
  define ("MIN_SIMILAR", 40);
  define ("MIN_COMPIL_RATIO", 0.7);
  define ("MIN_COMPIL_UNIVERSE", 10);
  define ("MIN_RELEVANCE_PERFORMER", 5);
  define ("SAPI_ITEMS", 50);
  define ("SAPI_PAGES", 5);
  define ("API_RETURN", "json");
  define ("HASH_SALT", "vUJmLwFgniCBmqcreBbsX9Jb");
  define ("MAXIMUM_RAND_RECORDINGS", 20);
  define ("MAXIMUM_RAND_TRACKS", 50);
  define ("MAXIMUM_RAND_TIME", 3 * 60 * 60);
  
  // spotify constants

  define ("SPOTIFYAPI", "https://api.spotify.com/v1");
  define ("SPOTIFYTOKENAPI", "https://accounts.spotify.com/api/token");

  // open opus 

  define ("OPENOPUS", "http://api.openopus.org");
  define ("OPENOPUS_DEFCOMP", "https://assets.openopus.org/portraits/default.jpg");

  // omnisearch forbidden words

  $omnisearch_forbidden = Array ("symphony", "symphonique", "symphoniker", "symphonie", "sinfonietta", "orquesta", "orchestra", "symphonic", "philharmonic", "duo", "trio", "quartet", "quintet", "sextet", "septet", "octet", "opera", "the", "and", "of", "by", "from");

  // helper libraries

  include_once (UTILIB. "/lib.php");
  include_once (LIB. "/lib.php");

  // api init

  $starttime = microtime (true);
  $apireturn = Array ("status" => Array ("version" => SOFTWAREVERSION));

  // db init

  $mysql = mysqli_connect (DBHOST, DBUSER, DBPASS, INSTANCE. "_". DBDB);
  mysqli_set_charset ($mysql, "utf8");
