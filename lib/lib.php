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

      if (!NOCACHE)
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

  // guess the role of a performer using a given reference db
  
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

    return "";
  }

  // fetch and analyze spotify metadata

  function fetchspotify ($work, $return, $offset = 0)
  {
    global $mysql;

    // catalogue or title mode?

    if ($work[0]["namesearch"])
    {
      $mode = "title";
      $search = $work[0]["searchtitle"];
      $number = false;
    }
    else
    {
      preg_match_all (CATALOGUE_REGEX, $work[0]["title"], $matches);

      if (sizeof ($matches[0]))
      {
        $mode = "catalogue";
        $search = end($matches[2]). " ". end($matches[7]);

        if (strtolower (end($matches[2])) == "op" || strtolower (end($matches[2])) == "opus")
        {
          preg_match_all ('/(no\.)( )*([0-9])+/i', $work[0]["title"], $opmatches);
          
          if (sizeof ($opmatches[0])) 
          {
            $search .= " ". end ($opmatches[0]);
            $number = true;
          }
          else
          {
            $number = false;
          }
        }
      }
      else
      {
        $mode = "title";
        $search = $work[0]["searchtitle"];
        $number = false;
      }
    }

    // creating performer roles reference

    $query = "select performer, role from performersdigest where qty > 5 order by qty asc";
    $rolesdb = mysqlfetch ($mysql, $query);
    foreach ($rolesdb as $rl)
    {
      $rldb[slug (preg_replace ('/^((sir|lord|dame) )/i', '', $rl["performer"]))] = $rl["role"];
    }

    // creating works titles reference

    if ($mode == "title")
    {
      $query = "select id, lower(title) title, genre, ifnull (lower(searchtitle),lower(title)) searchtitle from work where composer_id={$work[0]["composer"]} order by id desc";
      $worksdb = mysqlfetch ($mysql, $query);

      foreach ($worksdb as $wk)
      {
        similar_text (strtolower ($work[0]["searchtitle"]), $wk["title"], $similarity);
        
        if ($wk["searchtitle"] == strtolower ($wk["title"]))
        {
          $wk["searchtitle"] = worksimplifier ($wk["title"]);
        }

        if ($similarity > MIN_SIMILAR || true)
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
    }

    // searching spotify

    $token = spotifyauth ();

    if ($return == "albums")
    {    
      $spalbums = spotifydownparse (SPOTIFYAPI. "/search/?limit=". SAPI_ITEMS. "&type=track&offset={$offset}&q=track:". trim(urlencode ($search. " artist:{$work[0]["complete_name"]}")), $token);
      $loop = 1;
      //$spalbums["tracks"]["next"] = $offset + SAPI_ITEMS;

      while ($spalbums["tracks"]["next"] && $loop <= SAPI_PAGES)
      {
        //$morealbums = spotifydownparse (SPOTIFYAPI. "/search/?limit=". SAPI_ITEMS. "&type=track&offset={$spalbums["tracks"]["next"]}&q=track:". trim(urlencode ($search. " artist:{$work[0]["complete_name"]}")), $token);
        $morealbums = spotifydownparse ($spalbums["tracks"]["next"], $token);
        $spalbums["tracks"]["items"] = array_merge ($spalbums["tracks"]["items"], $morealbums["tracks"]["items"]);
        $spalbums["tracks"]["next"] = $morealbums["tracks"]["next"];
        $loop++;
      }

      //if ($spalbums["tracks"]["total"] < SAPI_ITEMS) $spalbums["tracks"]["next"] = "";
    }
    else if ($return == "tracks")
    {
      $fspalbums = spotifydownparse (SPOTIFYAPI. "/albums/". $work[0]["spotify_albumid"]. "/?limit=". SAPI_ITEMS, $token);

      if ($fspalbums["release_date_precision"] == "day") 
      {
        $year = $fspalbums["release_date"];
      }
      else if ($fspalbums["release_date_precision"] == "month") 
      {
        $year = $fspalbums["release_date"]. "-01";
      }
      else
      {
        $year = $fspalbums["release_date"]. "-01-01";
      }
      
      $extras = Array 
        (
          "label" => $fspalbums["label"],
          "cover" => $fspalbums["images"][0]["url"],
          "year" => $year,
          "markets" => $fspalbums["available_markets"]
        );

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

      //echo slug($alb["artists"][0]["name"]). " - ". slug($work[0]["complete_name"]). "\n\n";
      //echo "\n". slug($alb["name"]). " - ". slug($search). "\n";
      //echo strpos (slug ($alb["name"]), slug($search)). "\n";
      
      //print_r ($matches);

      if ($mode == "catalogue")
      {
        preg_match_all ('/('. str_replace (' ', '( )*', str_replace ("/", "\/", trim(end($matches[2])))). ')(( |\.))*('. str_replace (' ', '( )*', trim(end($matches[7]))). '($| |\W))/i', $alb["name"], $trmatches);

        if (sizeof ($trmatches[0])) 
        {
          if ($number)
          {
            preg_match_all ('/(no\.)( )*'. str_replace("no. ", "", end($opmatches[0])). '($| |\W)/i', $alb["name"], $tropmatches);

            if (sizeof ($tropmatches[0])) 
            {
              $similarity = 100;
            }
            else
            {
              $similarity = 0;
            }
          }
          else
          {
            $similarity = 100;
          }
        }
        else
        {
          $similarity = 0;
        }
      }

      if ($similarity > MIN_SIMILAR && slug($alb["artists"][0]["name"]) == slug($work[0]["complete_name"])) 
      {
        $simwkdb = 0;
        $mostwkdb = 0;

        foreach ($wkdb as $wk)
        {
          //similar_text ($wk["title"], explode (":", $alb["name"])[0], $sim);
          similar_text (str_replace (" ", "", $wk["searchtitle"]), str_replace (" ", "", worksimplifier (explode (":", $alb["name"])[0])), $sim);
          
          //if ($sim > MIN_SIMILAR) echo $wk["id"]. " - ". $sim. " - ". $wk["searchtitle"]. " - ". worksimplifier (explode (":", $alb["name"])[0]). "\n[". $alb["name"]. "]\n\n";
          
          if ($sim > $simwkdb) 
          {
            $simwkdb = $sim;
            $mostwkdb = $wk["id"];
            $mostwkdbtitle = $wk["searchtitle"];
          }
        }

        //echo " GUESSED: ". $mostwkdb. " - ". $mostwkdbtitle. "\n[". $alb["name"]. "]\n\n";
        
        if ($mostwkdb == $work[0]["id"] || $mode == "catalogue")
        {
          //echo $alb["name"]. "\n\n";
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
            if (sizeof ($performers) == 1)
            {
              if (!strpos ($alb["artists"][0]["name"], "/")) 
              {
                if (guessrole ($performers[0], $rldb) == "Orchestra") $performers[] = $alb["artists"][0]["name"];
              }
            }

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
              "performers" => allperformers ($performers, $rldb),
              "tracks" => sizeof ($albums[$alb["album"]["id"]])+1
            );

            $tracks[] = Array 
            (
              "full_title" => $alb["name"],
              "title" => trim (str_replace ("(Live)", "", end (explode (":", $alb["name"])))),
              "cd" => $alb["disc_number"],
              "position" => $alb["track_number"],
              "length" => round ($alb["duration_ms"] / 1000, 0, PHP_ROUND_HALF_UP),
              "spotify_trackid" => $alb["id"],
              "performers" => allperformers ($performers, $rldb)
            );
          }

          $mostsimilar = $similarity;
        }
      }
    }

    $stats = Array 
      (
        "term_used" => trim(urlencode ($search. " artist:{$work[0]["complete_name"]}")),
        "spotify_responses" => count ($spalbums["tracks"]["items"]),
        "useful_responses" => count (${$return}),
        "usefulness_rate" => round(100*(count (${$return})/count ($spalbums["tracks"]["items"])), 2). "%"
      );

    if ($return == "albums" && $spalbums["tracks"]["next"])
    {
      $extras = Array ("next"=>$spalbums["tracks"]["next"]);  
    }

    return Array ("type"=> $return, "items"=>${$return}, "stats"=>$stats, "extras"=>$extras);
  }

  // add concertmaster own extradata to spotify metadata

  function extradata ($spot, $params)
  {
    global $mysql;

    if ($params["aid"])
    {
      $where = "work_id={$params["wid"]} and spotify_albumid='{$params["aid"]}' and subset={$params["set"]}";
    }
    else
    {
      $where = "work_id={$params["wid"]}";
    }

    $extrarecordings = mysqlfetch ($mysql, "select spotify_imgurl, spotify_albumid, subset, year, recommended, compilation, oldaudio, verified, wrongdata, spam, badquality from recording where ". $where);
    $extraperformers = mysqlfetch ($mysql, "select spotify_albumid, subset, performer, role from recording_performer where " . $where . " order by spotify_albumid asc, subset asc");

    if ($params["aid"]) $extratracks = mysqlfetch ($mysql, "select cd, position, length, title, spotify_trackid from track where " . $where . " order by spotify_albumid asc, subset asc, cd asc, position asc");

    if ($extratracks)
    {
      $extratracks[sizeof ($extratracks)-1]["performers"] = end($spot["items"])["performers"];
      $spot["items"] = $extratracks;
    }

    foreach ($extrarecordings as $ed)
    {
      if ($params["aid"])
      {
        if ($ed["year"]) $spot["extras"]["year"] = $ed["year"];
        if ($ed["verified"]) $spot["extras"]["verified"] = "true";
      }
      else
      {
        if ($ed["subset"] > 1 || ($ed["verified"] && !sizeof ($spot["items"][$ed["spotify_albumid"]])))
        {
          $spot["items"]["{$ed["spotify_albumid"]}-{$ed["subset"]}"][0] = $ed;
          $spot["items"]["{$ed["spotify_albumid"]}-{$ed["subset"]}"][0]["tracks"] = 2;
        }
        else 
        {
          $pos = sizeof ($spot["items"][$ed["spotify_albumid"]]) - 1;

          if ($pos >= 0)
          {
            if ($ed["year"]) $spot["items"][$ed["spotify_albumid"]][$pos]["year"] = $ed["year"];
            if ($ed["compilation"]) $spot["items"][$ed["spotify_albumid"]][$pos]["compilation"] = true;
            if ($ed["oldaudio"]) $spot["items"][$ed["spotify_albumid"]][$pos]["historic"] = true;
            if ($ed["verified"]) $spot["items"][$ed["spotify_albumid"]][$pos]["verified"] = true;
            if ($ed["recommended"]) $spot["items"][$ed["spotify_albumid"]][$pos]["recommended"] = true;

            if ($ed["badquality"] || $ed["wrongdata"] || $ed["spam"]) 
            {
              unset ($spot["items"][$ed["spotify_albumid"]]);
            }
          }
        }
      }
    }

    foreach ($extraperformers as $ep)
    {
      $array = Array ("name"=>$ep["performer"],"role"=>$ep["role"]);

      if ($params["aid"])
      {
        $spot["items"][sizeof($spot["items"])-1]["extraperformers"][] = $array;
      }
      else
      {
        if ($ep["subset"] > 1 || $spot["items"]["{$ep["spotify_albumid"]}-{$ep["subset"]}"][0]["verified"])
        {
          $spot["items"]["{$ep["spotify_albumid"]}-{$ep["subset"]}"][0]["performers"][] = $array;
        }
        else
        {
          $pos = sizeof ($spot["items"][$ep["spotify_albumid"]]) - 1;
          
          if ($pos >= 0) $spot["items"][$ep["spotify_albumid"]][$pos]["extraperformers"][] = $array;
        }
      }
    }

    return $spot;
  }

  // return an array of performers along with their guessed roles

  function allperformers ($array, $rldb)
  {
    foreach ($array as $art)
    {
      $pf[] = Array
        (
          "performer" => $art,
          "role" => guessrole ($art, $rldb)
        );

      $return[] = Array ("name" => end($pf)["performer"], "role" => end($pf)["role"]);
    }

    // orchestras (almost always) have conductors

    if (sizeof ($return) == 2)
    {
      foreach ($return as $i => $r)
      {
        if ($r["role"] == "Orchestra")
        {
          $return[($i ? 0 : 1)]["role"] = "Conductor";
        }
      }
    }

    // ensembles, choirs, orchestras and conductors should end the array

    foreach ($return as $ii => $rr)
    {
      if ($rr["role"] == "Orchestra")
      {
        $or[] = $rr;
        unset ($return[$ii]);
      }
      elseif ($rr["role"] == "Ensemble" || $rr["role"] == "Choir")
      {
        $er[] = $rr;
        unset ($return[$ii]);
      }
      elseif ($rr["role"] == "Conductor")
      {
        $cr[] = $rr;
        unset ($return[$ii]);
      }
    }

    if (sizeof ($er))
    {
      $return = array_merge ($return, $er);
    }

    if (sizeof ($or))
    {
      $return = array_merge ($return, $or);
    }

    if (sizeof ($cr))
    {
      $return = array_merge ($return, $cr);
    }

    return $return;
  }

  // absolves single track recordings if most recordings of a work is single track

  function compilationdigest ($apireturn)
  {
    $total = sizeof ($apireturn["recordings"]);
    $compilations = array_count_values (array_column ($apireturn["recordings"], "singletrack"))["true"];
    $ratio = $compilations / $total;

    $apireturn["status"]["rows"] = $total;
    $apireturn["status"]["stats"]["singletrack"] = $compilations;
    $apireturn["status"]["stats"]["singletrack_ratio"] = round(100*$ratio,2). "%";

    if ($total >= MIN_COMPIL_UNIVERSE)
    {
      foreach ($apireturn["recordings"] as $key => $rec)
      {
        if ($rec["singletrack"] == "true" && $ratio < MIN_COMPIL_RATIO) $apireturn["recordings"][$key]["compilation"] = "true";
      }
    }
  
    return $apireturn;
  }

  // create a searchable and comparable string for a given work title

  function worksimplifier ($name)
  {
    $name = strtolower ($name);
    $pattern = '/(\,|\(|\'|\"|\-|\;).*/i';
    $stepone = preg_replace ($pattern, '', $name);
    
    $pattern = '/ in .\b( (minor|major|sharp major|sharp minor|flat major|flat minor|flat|sharp))?/i';
    return preg_replace ($pattern, '', $stepone);
  }

  // inserting a recording into the recording abstract database

  function insertrecording ($request)
  {
    global $mysql;

    $query = "insert into recording (work_id, spotify_albumid, subset, spotify_imgurl) values ('{$request["wid"]}', '{$request["aid"]}', '{$request["set"]}', '{$request["cover"]}')";
    mysqli_query ($mysql, $query);

    // inserting performers into the recording abstract database

    if (mysqli_affected_rows ($mysql) > 0)
    {
      $performers = json_decode ($request["performers"], true);

      foreach ($performers as $pk => $pf)
      {
        $nperfs[] = Array ("performer"=>$pf["name"], "role"=>$pf["role"], "work_id"=>$request["wid"], "spotify_albumid"=>$request["aid"], "subset"=>$request["set"]);
      }

      mysqlmultinsert ($mysql, "recording_performer", $nperfs);
    }

    return true;
  }

  // convert decimal to roman

  function dectoroman ($number) 
  {
    $map = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
    $returnValue = '';
    while ($number > 0) 
    {
        foreach ($map as $roman => $int) 
        {
            if($number >= $int) {
                $number -= $int;
                $returnValue .= $roman;
                break;
            }
        }
    }
    return $returnValue;
}