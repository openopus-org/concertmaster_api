<?
    // installation instance

    define ("INSTANCE", "dev"); // a prefix identifying the working database (useful to switch between dev and production)

    // web server 

    define ("INFRADIR", "/var/www"); // base OS directory 
    define ("UTILIB", INFRADIR. "/openopus_utils"); // directory for the utilities library
    define ("BASEDIR", INFRADIR. "/concertmaster_api"); // directory for all project, including public and non-public files
    define ("WEBDIR", BASEDIR. "/html"); // directory for publicly accessible files
    define ("LIB", BASEDIR. "/lib"); // directory for non-publicly accessible files, like libraries
    define ("LOG", BASEDIR. "/log"); // log dir
    define ("DEBUG", LOG. '/debug.txt'); // log file for some CURL operations detailed debug
    define ("TMP_DIR", "/tmp"); // OS temp directory

    // mysql 

    define ("DBDB", "concertmaster"); // mysql database basename (the real name will be prefixed by the instance above)
    define ("DBHOST", "localhost"); // mysql host address
    define ("DBUSER", "username"); // mysql username
    define ("DBPASS", "password"); // mysql password

    // admin

    define ("SOFTWAREMAIL", "adminmail@gmail.com"); // server admin email address

    // spotify 

    define ("SPOTIFYID", "lots of letters and numbers"); // spotify dev account id
    define ("SPOTIFYSECRET", "lots of letters and numbers"); // spotify dev account secret string
    define ("SPOTIFYCBURL", "scheme://url"); // one of your spotify callback urls

    // debug

    define ("NOCACHE", false); // true will stop caching api results - useful for debugging

    // library initialization

    include_once (LIB. "/ini.php");