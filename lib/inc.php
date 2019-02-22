<?
  // removing notices

  error_reporting (E_ALL ^ (E_WARNING | E_NOTICE));

  // header json

  if (!$nojson)
  {
    header ("Content-Type: application/json");
  }

  // global constants

  define ("INFRADIR", "/var/www");
  define ("BASEDIR", INFRADIR. "/concertmaster_api");
  define ("WEBDIR", BASEDIR. "/html");
  define ("DBHOST", "localhost");
  define ("DBUSER", "concertmaster");
  define ("DBPASS", "v3nt1l4d0r");
  define ("DBDB", "concertmaster");
  define ("LIB", BASEDIR. "/lib");
  define ("LOG", BASEDIR. "/log");
  define ("DEBUG", LOG. '/debug.txt');
  define ("SOFTWARENAME", "Concertmaster");
  define ("SOFTWAREVERSION", "0.19");
  define ("SOFTWAREMAIL", "adrianosbr@gmail.com");
  define ("USERAGENT", SOFTWARENAME. "/" . SOFTWAREVERSION. " ( ". SOFTWAREMAIL. " )");
  define ("RECRETS", 60);
  define ("MIN_SIMILAR", 40);
  define ("MIN_COMPIL_RATIO", 0.8);
  define ("API_RETURN", "json");
  define ("TMP_DIR", "/tmp");
  define ("HASH_SALT", "vUJmLwFgniCBmqcreBbsX9Jb");
  define ("PUBLIC_URL", "https://api.concertmaster.app");
  define ("ALWAYS_EXT", false);
  define ("JOIN_SPOTIFY", true);

  // spotify constants

  define ("SPOTIFYID", "d51f903ebcac46d9a036b4a2da05b299");
  define ("SPOTIFYSECRET", "1d153756c5f140408fd611619eded44a");
  define ("SPOTIFYAPI", "https://api.spotify.com/v1");
  define ("SPOTIFYTOKENAPI", "https://accounts.spotify.com/api/token");

  // forbidden and bad labels

  $forbidden_labels = ['Classical Archives','Music@Menlo LIVE','Amadis','Yoyo USA','Classico','CD Accord','Vista Vera','CMS Live','Summit Records'];
  $historical_labels = ['British Music Society','SOMM Recordings','West Hill Radio Archives','Music and Arts Programs of America','IDIS','Audite','Archiphon','Fono','Pierian Recording Society'];

  // concertmaster flavor

  define ("INSTANCE", "spo");

  // helper library

  include_once (LIB. "/lib.php");

  // api init

  $starttime = microtime (true);
  $apireturn = Array ("status" => Array ("version" => SOFTWAREVERSION));

  // db init

  $mysql = mysqli_connect (DBHOST, DBUSER, DBPASS, INSTANCE. "_". DBDB);
  mysqli_set_charset ($mysql, "utf8");
