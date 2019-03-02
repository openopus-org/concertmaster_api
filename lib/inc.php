<?
    // concertmaster flavor

    define ("INSTANCE", "spo");

    // web server directories

    define ("INFRADIR", "/var/www");
    define ("BASEDIR", INFRADIR. "/concertmaster_api");
    define ("WEBDIR", BASEDIR. "/html");
    define ("DBDB", "concertmaster");
    define ("LIB", BASEDIR. "/lib");
    define ("LOG", BASEDIR. "/log");
    define ("DEBUG", LOG. '/debug.txt');
    define ("TMP_DIR", "/tmp");

    // url definitions

    define ("PUBLIC_URL", "https://api.concertmaster.app");

    // mysql 

    define ("DBHOST", "localhost");
    define ("DBUSER", "concertmaster");
    define ("DBPASS", "v3nt1l4d0r");

    // admin

    define ("SOFTWAREMAIL", "adrianosbr@gmail.com");

    // spotify developer account

    define ("SPOTIFYID", "d51f903ebcac46d9a036b4a2da05b299");
    define ("SPOTIFYSECRET", "1d153756c5f140408fd611619eded44a");

    // debug

    define ("ALWAYS_EXT", false);

    // library initialization

    include_once (LIB. "/ini.php");