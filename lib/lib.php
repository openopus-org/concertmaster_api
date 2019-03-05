<?
  // slug generator

  function slug ($string, $replace = array(), $delimiter = '-') 
  {
    // https://github.com/phalcon/incubator/blob/master/Library/Phalcon/Utils/Slug.php

    if (!extension_loaded ('iconv')) 
    {
      throw new Exception ('iconv module not loaded');
    }

    // save the old locale and set the new locale to UTF-8
    
    $oldLocale = setlocale (LC_ALL, '0');
    setlocale (LC_ALL, 'en_US.UTF-8');
    $clean = iconv ('UTF-8', 'ASCII//TRANSLIT', $string);
    
    if (!empty($replace))
    {
      $clean = str_replace ((array) $replace, ' ', $clean);
    }

    $clean = preg_replace ("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
    $clean = strtolower ($clean);
    $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
    $clean = trim ($clean, $delimiter);

    // revert back to the old locale
    
    setlocale (LC_ALL, $oldLocale);
    return $clean;
  }

  function oldslug ($phrase)
  {
        $result = strtolower($phrase);
        $result = preg_replace ("/[^a-z0-9\s-]/", "", $result);
        $result = trim (preg_replace("/[\s-]+/", " ", $result));
        $result = trim(substr($result, 0, 100));
        $result = preg_replace ("/\s/", "-", $result);

        return $result;
  }

  // simple mysql insert

  function mysqlinsert ($mysql, $table, $insert)
  {
    foreach ($insert as $ini => $ins)
    {
      $insert[$ini] = mysqli_real_escape_string ($mysql, $ins);
    }

    $query = "insert into {$table} (". implode (", ", array_keys ($insert)). ") values ('". implode ("','", $insert). "')";
    $query = str_replace ("''", "null", $query);
    
    mysqli_query ($mysql, $query);

    return mysqli_insert_id ($mysql);
  }

  // multiline mysql insert

  function mysqlmultinsert ($mysql, $table, $lines)
  {
    foreach ($lines as $line)
    {
      foreach ($line as $ini => $ins)
      {
        $insert[$ini] = mysqli_real_escape_string ($mysql, $ins);
      }

      $values[] = "('". implode ("','", $insert). "')";
    }

    $query = "insert into {$table} (". implode (", ", array_keys ($lines[0])). ") values ". implode (", ", $values);
    $query = str_replace ("''", "null", $query);
    
    mysqli_query ($mysql, $query);

    return mysqli_insert_id ($mysql);
  }

  // mysql update

  function mysqlupdate ($mysql, $table, $update, $where)
  {
    foreach ($update as $upa => $ups)
    {
      $set[] = "{$upa}='". mysqli_real_escape_string ($mysql, $ups) . "'";
    }

    $query = "update $table set ". implode (", ", $set). " where ". array_keys($where)[0]. "='". mysqli_real_escape_string ($mysql, array_values($where)[0]). "'";
    $query = str_replace ("''", "null", $query);
    
    mysqli_query ($mysql, $query);

    return mysqli_affected_rows ($mysql);
  }

  // mysql query to assoc array

  function mysqlfetch ($mysql, $query)
  {
    $data = mysqli_query ($mysql, $query, MYSQLI_USE_RESULT);

    if ($data)
    {
      while ($ardata = mysqli_fetch_assoc ($data))
      {
        $r[] = $ardata;
      }

      return (isset ($r) ? $r : false);
    }
    else
    {
      return false;
    }
  }

  // api retrieving

  function apidownparse ($url, $format, $token)
  {
    $api = CURL_Internals ($url, false, false, false, $token);

    if ($format == "json")
    {
      return json_decode ($api, true);
    }
    else if ($format == "xml")
    {
      $p = xml_parser_create();
      xml_parse_into_struct ($p, $api, $values, $keys);
      xml_parser_free ($p);

      return $values;
    }
  }

  // conversion mm:ss into secs

  function timetosec ($time)
  {
    $expl = explode (":", $time);

    return ($expl[0] * 60) + $expl[1];
  }

  // api wrapers

  function rovidownparse ($url)
  {
  	$sig = md5 (ROVIKEY. ROVISECRET. time ());
    return apidownparse ($url. "&format=json&apikey=". ROVIKEY. "&sig=". $sig, "json", false);
  }

  function spotifydownparse ($url, $token)
  {
    return apidownparse ($url, "json", $token);
  }

  // basic curl retrieving

  function CURL_Internals ($url, $bust = true, $plusheader, $pluspost, $token)
  {
    $ts = time ();
    $ch = curl_init ();

    $fp = fopen (DEBUG, "w");

    $header = array();
    $header[] = 'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5';
    $header[] = 'Cache-Control: max-age=0';
    $header[] = 'Connection: keep-alive';
    $header[] = 'Keep-Alive: 300';
    $header[] = 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7';
    $header[] = 'Accept-Language: en-us,en;q=0.5';
    $header[] = 'Pragma: ';

    if ($plusheader)
    {
      if (is_array ($plusheader))
      {
        $credentials = base64_encode ($plusheader[0].":".$plusheader[1]);
        $header[] = 'Authorization: Basic '. $credentials;
      }
    }
    else if ($token)
    {
      $header[] = 'Authorization: Bearer '.$token;
    }

    if ($bust)
    {
        curl_setopt ($ch, CURLOPT_URL, $url. "?". $ts);
    }
    else
    {
        curl_setopt ($ch, CURLOPT_URL, $url);
    }

    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt ($ch, CURLOPT_USERAGENT, USERAGENT);
    curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt ($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt ($ch, CURLOPT_ENCODING, '');
    curl_setopt ($ch, CURLOPT_TIMEOUT, 200);
    //curl_setopt ($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt ($ch, CURLOPT_VERBOSE, TRUE);
    curl_setopt ($ch, CURLOPT_STDERR, $fp);

    if ($pluspost)
    {
      curl_setopt ($ch, CURLOPT_POST, 1);
      curl_setopt ($ch, CURLOPT_POSTFIELDS, $pluspost);
    }

    $api = curl_exec ($ch);

    curl_close ($ch);
    fclose ($fp);

    return $api;
  }

  // spotify wrapper

  function spotifyauth ()
  {
    $api = CURL_Internals (SPOTIFYTOKENAPI, false, Array (SPOTIFYID, SPOTIFYSECRET), "grant_type=client_credentials", false);
    $tok = json_decode ($api, true);

    return $tok["access_token"];
  }

  // upc check function

  function upc_checkdigit ($upc_code)
  {
    $odd_total  = 0;
    $even_total = 0;

    for($i=0; $i<strlen($upc_code); $i++)
    {
        if((($i+1)%2) == 0) {
            /* Sum even digits */
            $even_total += $upc_code[$i];
        } else {
            /* Sum odd digits */
            $odd_total += $upc_code[$i];
        }
    }

   	if (strlen ($upc_code) == 11)
   	{
      	$sum = (3 * $odd_total) + $even_total;
   	}
   	else if (strlen ($upc_code) == 12)
   	{
   		$sum = (3 * $even_total) + $odd_total;
   	}
    else
    {
      $sum = 0;
    }

    /* Get the remainder MOD 10*/
    $check_digit = $sum % 10;

    /* If the result is not zero, subtract the result from ten. */
    return ($check_digit > 0) ? 10 - $check_digit : $check_digit;
  }

  // receives an uni array and insert into cm's db

  function save_recordings ($mysql, $array, $p)
  {
    global $forbidden_labels, $historical_labels;

    $ii = -1;
    if (sizeof ($array))
    {
      foreach ($array as $performance)
      {
        $ii = $ii + 1;

        // looking for the album

        if (isset ($performance["album"]))
        {
          $album = $performance["album"];

          if (!isset ($album["releaseDate"]))
          {
            $album["releaseDate"] = "2000";
          }

          // changing date format

          $pattern = '/([0-9])+$/i';
          $replacement = '$2';
          preg_match ($pattern, $album["releaseDate"], $matches);

          if (is_array ($matches))
          {
            if (count($matches) >= 1)
            {
              $album["releaseDate"] = $matches[0]."-01-01";
            }
          }

          // inserting

          $insert = Array
          (
            "work_id" => $p["id"],
            "label" => $album["label"],
            "year" => $album["releaseDate"],
            "upc" => $album["upc"],
            "uni_imgurl" => str_replace ("_100.jpg", "_300.jpg", $album["imagePath"]),
            "uni_id" => $performance["id"],
            "compilation" => ($performance["performance_compilation"]) ? 1 : 0
          );

          //print_r ($insert);
          mysqlinsert ($mysql, "recording", $insert);
          $rid = mysqli_insert_id ($mysql);

          $return[$ii] = Array
            (
              "id" => $rid,
              "label" => $album["label"],
              "year" => explode ("-", $album["releaseDate"])[0],
              "upc" => $album["upc"],
              "cover" => PUBLIC_URL. "/cover/". $rid. "|". str_replace ("/", "-", str_replace ("/images/coverart/", "", $insert["uni_imgurl"])),
              "uni_id" => $performance["id"],
              "historical" => (in_array ($album["label"], $historical_labels)) ? "true" : "false",
              "compilation" => ($performance["performance_compilation"]) ? "true" : "false"
            );

          foreach ($performance["performers"] as $performer)
          {
            $pfinsert = Array
            (
              "recording_id" => $rid,
              "performer" => $performer["name"],
              "role" => $performer["info"]
            );

            //print_r ($insert);
            mysqlinsert ($mysql, "recording_performer", $pfinsert);
            $return[$ii]["performers"][] = Array ("name" => $pfinsert["performer"], "role" => $pfinsert["role"]);
          }

          if (in_array ($album["label"], $forbidden_labels))
          {
            unset ($return[$ii]);
          }
        }
      }
    }

    return $return;
  }

  // receives uni track info and insert into cm's db

  function save_tracks ($mysql, $album, $tracks, $p)
  {
    $return = Array ();

    foreach ($tracks as $track)
    {
      foreach ($album as $pos => $tr)
      {
        if ($tr["track_id"] == $track)
        {
          $trinsert = Array
          (
            "recording_id" => $p["recording_id"],
            "cd" => 1,
            "position" => $pos,
            "length" => timetosec ($tr["track_length"]),
            "title" => $tr["track_title"],
            "uni_id" => $tr["track_id"]
          );

          //print_r ($trinsert);
          mysqlinsert ($mysql, "track", $trinsert);

          $return[] = Array
          (
            "id" => mysqli_insert_id ($mysql),
            "title" => $tr["track_title"],
            "position" => $pos,
            "length" => timetosec ($tr["track_length"])
          );
        }
      }
    }

    return $return;
  }

  // fetches spotify database

  function save_spotify ($mysql, $recording)
  {
    global $timesteps, $starttime, $timetimings;

    //  auth

    $token = spotifyauth ();

    // spotify album id

    $spotifyalbums = spotifydownparse (SPOTIFYAPI. '/search?q=upc:' . $recording["upc"]. '%20OR%20upc:0'. $recording["upc"]. '%20OR%20upc:00'. $recording["upc"]. '&type=album', $token);
    $timesteps[] = "spotify search";
    $timetimings[] = (microtime (true) - $starttime);

    if ($spotifyalbums["albums"]["total"])
    {
      $return["spotify_albumid"] = $spotifyalbums["albums"]["items"][0]["id"];

      mysqlupdate ($mysql, "recording", Array ("spotify_albumid"=>$spotifyalbums["albums"]["items"][0]["id"]), Array ("id"=>$recording["id"]));

      //mysqlupdate ($mysql, "update recording set spotify_albumid = '{$spotifyalbums["albums"]["items"][0]["id"]}' where id = '{$recording["id"]}'");

      if ($return["spotify_albumid"])
      {
        // spotify tracks

        $spotifytracks = spotifydownparse (SPOTIFYAPI. '/albums/'. $return["spotify_albumid"]. '/tracks?limit=50', $token);
        $timesteps[] = "spotify album fetch";
        $timetimings[] = (microtime (true) - $starttime);

        while ($spotifytracks["next"])
        {
          $spotifynewtracks = spotifydownparse ($spotifytracks["next"], $token);
          $spotifytracks["items"] = array_merge ($spotifytracks["items"], $spotifynewtracks["items"]);
          $spotifytracks["next"] = $spotifynewtracks["next"];
        }

        foreach ($recording["tracks"] as $trid => $track)
        {
          $return["tracks"][$trid]["spotify_trackid"] = $spotifytracks["items"][$track["position"]]["id"];
          $return["tracks"][$trid]["length"] = round ($spotifytracks["items"][$track["position"]]["duration_ms"] / 1000, 0, PHP_ROUND_HALF_UP);

          mysqlupdate ($mysql, "track", Array ("spotify_trackid"=>$spotifytracks["items"][$track["position"]]["id"], "length"=>$return["tracks"][$trid]["length"]), Array ("id"=>$recording["tracks"][$trid]["id"]));
          //mysqlupdate ($mysql, "update track set spotify_trackid = '{$spotifytracks["items"][$track["position"]]["id"]}', length = '{$return["tracks"][$trid]["length"]}' where id = '{$recording["tracks"][$trid]["id"]}'");
        }
      }
    }
    else
    {
      mysqlupdate ($mysql, "recording", Array ("spotify_absent"=>"true"), Array ("id"=>$recording["id"]));
      //mysqlupdate ($mysql, "update recording set spotify_absent = true where id = '{$recording["id"]}'");
      $return = false;
    }

    return $return;
  }

  // api return mode

  function apireturn ($apireturn)
  {
    global $starttime, $timesteps, $timetimings;

    $apireturn["status"]["processingtime"] = (microtime (true) - $starttime);
    $apireturn["status"]["api"] = SOFTWARENAME. "-dyn";
    $apireturn["status"]["version"] = SOFTWAREVERSION;

    if (API_RETURN == "json")
    {
      return json_encode ($apireturn);
    }
    elseif (API_RETURN == "array")
    {
      return (print_r ($apireturn, true));
    }
    elseif (API_RETURN == "serial")
    {
      return serialize ($apireturn);
    }
    elseif (API_RETURN == "xml")
    {
      return xmlrpc_encode ($apireturn);
    }
  }

  // cache saving

  function savecache ($file, $content)
  {
      $filename = WEBDIR. $file;
      $dirname = dirname ($filename);

      if (!is_dir ($dirname))
      {
        mkdir ($dirname, 0777, true);
      }

      if (!ALWAYS_EXT)
      {
        $fp = fopen ($filename, "w");
        fwrite ($fp, str_replace (SOFTWARENAME. "-dyn", SOFTWARENAME. "-cache", $content));
        fclose ($fp);
      }

      return $content;
  }

  // keeping only certain keys in arrays

  function arraykeep ($array, $keys)
  {
    $i = 0;

    foreach ($array as $ar)
    {
      foreach ($keys as $key)
      {
        $result[$i][$key] = $ar[$key];
      }

      ++$i;
    }

    return $result;
  }

  // keeping only certain keys but in value-only format

  function arraykeepvalues ($array, $keys)
  {
    $i = 0;

    foreach ($array as $ar)
    {
      foreach ($keys as $key)
      {
        $result[$i] = $ar[$key];
      }

      ++$i;
    }

    return $result;
  }

  // deleting certain keys from arrays

  function arraydelete ($array, $keys)
  {
    foreach ($array as $k => $ar)
    {
      foreach ($keys as $key)
      {
        unset ($array[$k][$key]);
      }
    }

    return $array;
  }

  // delete duplicates from an two-dimensional array, using a key as basis

  function arraydedup ($array, $key)
  {
    foreach ($array as $id => $item)
    {
      foreach ($item as $k => $v)
      {
        if ($k == $key)
        {
          if (in_array ($v, $results))
          {
            unset ($array[$id]);
          }
          $results[] = $v;
        }
      }
    }

    return $array;
  }

  // identity check

  function simpleauth ($mysql, $id, $hash)
  {
    $auth = mysqlfetch ($mysql, "select auth from user where spotify_id = '{$id}'");
    
    if (!$auth)
    {
        return false;
    }
    else
    {
        if (md5 (floor ((time() + (60 * 1)) / (60 * 5)). "-". $id. "-". $auth[0]["auth"]) == $hash)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
  }

  // post field check

  function postcheck ($array, $fields)
  {
    $return = true;

    foreach ($fields as $f)
    {
      if (!isset ($array[$f]))
      {
        $return = false;
      }
      else if (!$array[$f])
      {
        $return = false;
      }
    }

    return $return;
  }

  // lists 

  function composerlist ($condition, $uid)
  {
    global $mysql;

    $return = [];
    $composers = mysqlfetch ($mysql, "select composer_id from user_composer where user_id='{$uid}' and {$condition} = 1");
    
    foreach ($composers as $comp)
    {
      $return[] = $comp["composer_id"];
    }

    return $return;
  }

  function worklist ($uid)
  {
    global $mysql;

    $return = [];
    $works = mysqlfetch ($mysql, "select work_id from user_work where user_id='{$uid}' and favorite = 1");
    
    foreach ($works as $work)
    {
      $return[] = $work["work_id"];
    }

    return $return;
  }

  function playlists ($uid)
  {
    global $mysql;

    $return = [];
    $playlists = mysqlfetch ($mysql, "select id, name, playlist.user_id as owner from playlist, user_playlist where user_playlist.user_id='{$uid}' and playlist_id=id order by name asc");
    
    foreach ($playlists as $playlist)
    {
      $return[] = ["id"=>$playlist["id"],"name"=>$playlist["name"],"owner"=>$playlist["owner"]];
    }

    return $return;
  }
  
  function guessrole ($name, $rldb)
  {
    $name = preg_replace ('/^((sir|lord|dame) )/i', '', $name);

    global $orchestra_kw, $ensemble_kw, $choir_kw;

    foreach ($choir_kw as $kw)
    {
      if (stripos ($name, $kw) !== false)
      {
        return "Choir";
      }
    }
    
    foreach ($ensemble_kw as $kw)
    {
      if (stripos ($name, $kw) !== false)
      {
        return "Ensemble";
      }
    }

    foreach ($orchestra_kw as $kw)
    {
      if (stripos ($name, $kw) !== false)
      {
        return "Orchestra";
      }
    }

    if ($rldb[slug ($name)])
    {
      return $rldb[slug ($name)];
    }

    return "Artist";
  }

  function fetchspotify ($work, $return)
  {
    global $mysql;

    // creating works titles reference

    $query = "select id, lower(title) title, genre, ifnull (lower(searchtitle),lower(title)) searchtitle from work where composer_id={$work[0]["composer"]} order by id desc";
    $worksdb = mysqlfetch ($mysql, $query);

    foreach ($worksdb as $wk)
    {
      similar_text (strtolower ($work[0]["title"])  , $wk["title"], $similarity);
      
      if ($wk["searchtitle"] == strtolower ($wk["title"]))
      {
        $wk["searchtitle"] = worksimplifier ($wk["title"]);
      }

      if ($similarity > MIN_SIMILAR)
      {
        $wkdb[] = array_merge ($wk, Array ("similarity" => $similarity));
      }
    }

    // adding alternate titles of the work to the reference

    foreach (explode (",", $work[0]["alternatetitles"]) as $alttitle)
    {
      $wkdb[] = Array 
        (
          "id" => $work[0]["id"],
          "title" => strtolower ($work[0]["title"]),
          "genre" => $work[0]["genre"],
          "searchtitle" => $alttitle
        );
    }

    // searching spotify

    $token = spotifyauth ();

    if ($return == "albums")
    {    
      $spalbums = spotifydownparse (SPOTIFYAPI. "/search/?limit=". SAPI_ITEMS. "&type=track&q=". urlencode ($work[0]["searchtitle"]. " artist:{$work[0]["complete_name"]}"), $token);
      $loop = 0;

      while ($spalbums["tracks"]["next"] && $loop < SAPI_PAGES)
      {
        $morealbums = spotifydownparse ($spalbums["tracks"]["next"], $token);
        $spalbums["tracks"]["items"] = array_merge ($spalbums["tracks"]["items"], $morealbums["tracks"]["items"]);
        $spalbums["tracks"]["next"] = $morealbums["tracks"]["next"];
        $loop++;
      }
    }
    else if ($return == "tracks")
    {
      $fspalbums = spotifydownparse (SPOTIFYAPI. "/albums/". $work[0]["spotify_albumid"]. "/?limit=". SAPI_ITEMS, $token);
      $extras = Array ("upc"=>$fspalbums["external_ids"]["upc"], "label"=>$fspalbums["label"]);

      if (!sizeof ($fspalbums["tracks"]["items"]))
      {
        $spalbums = spotifydownparse ($fspalbums["tracks"]["href"], $token);
      }
      else
      {
        $spalbums = $fspalbums;
      }

      $loop = 0;

      while ($spalbums["tracks"]["next"])
      {
        $morealbums = spotifydownparse ($spalbums["tracks"]["next"], $token);

        $spalbums["tracks"]["items"] = array_merge ($spalbums["tracks"]["items"], $morealbums["items"]);
        $spalbums["tracks"]["next"] = $morealbums["next"];
        $loop++;
      }
    }

    foreach ($spalbums["tracks"]["items"] as $kalb => $alb)
    {
      $alb["name"] = str_replace (end (explode (" ", $alb["artists"][0]["name"])), "", str_replace (end (explode (" ", $alb["artists"][0]["name"])). ": ", "", str_replace ($alb["artists"][0]["name"], "", str_replace ($alb["artists"][0]["name"]. ": ", "", $alb["name"]))));
      $alb["name"] = preg_replace ('/^(( )*( |\,|\(|\'|\"|\-|\;|\:)( )*)/i', '', $alb["name"], 1);

      //similar_text ($work[0]["title"], explode (":", $alb["name"])[0], $similarity);
      similar_text ($work[0]["searchtitle"], worksimplifier (explode (":", $alb["name"])[0]), $similarity);

      $albunsfound = Array 
        (
          "track_title" => $alb["name"],
          "search_title" => $work[0]["searchtitle"],
          "simplified_track_title" => worksimplifier (explode (":", $alb["name"])[0]),
          "similarity" => $similarity
        );
      
      //if ($similarity > $spotres[$alb["album"]["id"]]["mostsimilar"] && $similarity > MIN_SIMILAR)

      foreach (explode (",", $work[0]["alternatetitles"]) as $alttitle)
      {
        similar_text ($alttitle, worksimplifier (explode (":", $alb["name"])[0]), $othersimilarity);

        if ($othersimilarity > $similarity)
        {
          $similarity = $othersimilarity;
        }
      }

      if ($similarity > MIN_SIMILAR && $alb["artists"][0]["name"] == $work[0]["complete_name"]) 
      {
        $simwkdb = 0;
        $mostwkdb = 0;

        foreach ($wkdb as $wk)
        {
          //similar_text ($wk["title"], explode (":", $alb["name"])[0], $sim);
          similar_text ($wk["searchtitle"], worksimplifier (explode (":", $alb["name"])[0]), $sim);
          
          //if ($sim > MIN_SIMILAR) echo $wk["id"]. " - ". $sim. " - ". $wk["searchtitle"]. " - ". worksimplifier (explode (":", $alb["name"])[0]). "\n[". $alb["name"]. "]\n\n";
          
          if ($sim > $simwkdb) 
          {
            $simwkdb = $sim;
            $mostwkdb = $wk["id"];
            $mostwkdbtitle = $wk["searchtitle"];
          }
        }

        //echo " GUESSED: ". $mostwkdb. " - ". $mostwkdbtitle. "\n[". $alb["name"]. "]\n\n";

        if ($mostwkdb == $work[0]["id"])
        {
          unset ($performers);

          foreach ($alb["artists"] as $kart => $art)
          {
            if ($kart && !strpos ($art["name"], "/"))
            {
              $performers[] = $art["name"];
            }
          }

          if (sizeof ($performers))
          {
            if ($alb["album"]["release_date_precision"] == "day") 
            {
              $year = $alb["album"]["release_date"];
            }
            else if ($alb["album"]["release_date_precision"] == "month") 
            {
              $year = $alb["album"]["release_date"]. "-01";
            }
            else
            {
              $year = $alb["album"]["release_date"]. "-01-01";
            }

            //$spotres[$alb["album"]["id"]] = Array 
            $albums[$alb["album"]["id"]][] = Array 
            (
              "similarity_between" => Array ($work[0]["searchtitle"], worksimplifier (explode (":", $alb["name"])[0])),
              "mostsimilar" => $similarity,
              "full_title" => $alb["name"],
              "title" => trim (end (explode (":", $alb["name"]))),
              "similarity" => $similarity,
              "work_id" => $wid,
              "year" => $year,
              "spotify_imgurl" => $alb["album"]["images"][0]["url"],
              "spotify_albumid" => $alb["album"]["id"],
              "performers" => $performers,
              "tracks" => sizeof ($albums[$alb["album"]["id"]])+1
            );

            $tracks[] = Array 
            (
              "full_title" => $alb["name"],
              "title" => trim (end (explode (":", $alb["name"]))),
              "cd" => $alb["disc_number"],
              "position" => $alb["track_number"],
              "length" => round ($alb["duration_ms"] / 1000, 0, PHP_ROUND_HALF_UP),
              "spotify_trackid" => $alb["id"]
            );
          }

          $mostsimilar = $similarity;
        }
      }
    }

    $stats = Array 
      (
        "spotify_responses" => count ($spalbums["tracks"]["items"]),
        "useful_responses" => count (${$return}),
        "usefulness_rate" => round(100*(count (${$return})/count ($spalbums["tracks"]["items"])), 2). "%"
      );

    return Array ("type"=> $return, "items"=>${$return}, "stats"=>$stats, "extras"=>$extras);
  }

  function fetchrecordings ($work)
  {
    global $mysql;

    // creating performer roles reference

    $query = "select performer, role from performersdigest where qty > 5 order by qty asc";
    $rolesdb = mysqlfetch ($mysql, $query);
    foreach ($rolesdb as $rl)
    {
      $rldb[slug (preg_replace ('/^((sir|lord|dame) )/i', '', $rl["performer"]))] = $rl["role"];
    }

    // searching spotify

    $wid = $work[0]["id"];
    $ii = -1;

    $spot = fetchspotify ($work, "albums");
    $spotres = $spot["items"];
    $return["stats"] = $spot["stats"];

    foreach ($spotres as $kalb => $salb)
    {
      $ii = $ii + 1;
      $alb = end($salb);

      // inserting

      $insert = Array
        (
          "work_id" => $wid,
          "year" => $alb["year"],
          "spotify_imgurl" => $alb["spotify_imgurl"],
          "spotify_albumid" => $alb["spotify_albumid"],
          "singletrack" => ($alb["tracks"] > 1) ? 0 : 1
        );

      if (!ALWAYS_EXT)
      {
        mysqlinsert ($mysql, "recording", $insert);
        $rid = mysqli_insert_id ($mysql);
      }
      else
      {
        $rid = 0;
      }

      $return[$ii] = Array
        (
          "id" => $rid,
          "year" => explode ("-", $alb["year"])[0],
          "cover" => $alb["spotify_imgurl"],
          "spotify_albumid" => $alb["spotify_albumid"],
          "compilation" => ($alb["tracks"] > 1) ? "false" : "true"
        );

      unset ($pfinsert);

      foreach ($alb["performers"] as $kart => $art)
      {
        $pfinsert[] = Array
          (
            "recording_id" => $rid,
            "performer" => $art,
            "role" => guessrole ($art, $rldb)         
          );

        //print_r ($pfinsert);

        $return[$ii]["performers"][] = Array ("name" => end($pfinsert)["performer"], "role" => end($pfinsert)["role"]);
      }

      if (!ALWAYS_EXT)
      {
        mysqlmultinsert ($mysql, "recording_performer", $pfinsert);
      }
    }

    return $return;
  }

  function compilationdigest ($apireturn)
  {
    $total = sizeof ($apireturn["recordings"]);
    $compilations = array_count_values (array_column ($apireturn["recordings"], "compilation"))["true"];
    $ratio = $compilations / $total;

    $apireturn["status"]["rows"] = $total;

    if ($ratio >= MIN_COMPIL_RATIO)
    {
      foreach ($apireturn["recordings"] as $key => $rec)
      {
        $apireturn["recordings"][$key]["compilation"] = "false";
      }
    }

    return $apireturn;
  }

  function worksimplifier ($name)
  {
    $name = strtolower ($name);
    $pattern = '/(\,|\(|\'|\"|\-|\;).*/i';
    $stepone = preg_replace ($pattern, '', $name);
    
    $pattern = '/ in .\b( (minor|major|sharp major|sharp minor|flat major|flat minor|flat|sharp))?/i';
    return preg_replace ($pattern, '', $stepone);
  }