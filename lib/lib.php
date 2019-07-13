<?
  // api wrapers

  function spotifydownparse ($url, $token)
  {
    return apidownparse ($url, "json", $token);
  }

  // spotify wrapper

  function spotifyauth ()
  {
    $api = CURL_Internals (SPOTIFYTOKENAPI, false, Array (SPOTIFYID, SPOTIFYSECRET), "grant_type=client_credentials", false);
    $tok = json_decode ($api, true);

    return $tok["access_token"];
  }

  // fetch and analyze spotify metadata

  function fetchspotify ($work, $return, $offset = 0)
  {
    global $mysql;
    
    // catalogue or title mode?

    if ($work["work"]["namesearch"])
    {
      $mode = "title";
      $search = $work["work"]["searchtitle"];
      $number = false;
    }
    else
    {
      preg_match_all (CATALOGUE_REGEX, $work["work"]["title"], $matches);

      if (sizeof ($matches[0]))
      {
        $mode = "catalogue";
        $search = end($matches[2]). " ". end($matches[8]);

        if (strtolower (end($matches[2])) == "op" || strtolower (end($matches[2])) == "opus")
        {
          preg_match_all ('/(no\.)( )*([0-9])+/i', $work["work"]["title"], $opmatches);
          
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
        $search = $work["work"]["searchtitle"];
        $number = false;
      }
    }

    // creating works titles reference

    if ($mode == "title")
    {
      $wkdb = ($work["similarlytitled"] ? $work["similarlytitled"] : []);

      // adding alternate titles of the work to the reference

      if ($work["work"]["alternatetitles"])
      {
        foreach (explode (",", $work["work"]["alternatetitles"]) as $alttitle)
        {
          $wkdb[] = Array 
            (
              "id" => $work["work"]["id"],
              "title" => $work["work"]["title"],
              "searchtitle" => $alttitle
            );
        }
      }
      
      // adding the work itself to the top of the reference

      array_unshift ($wkdb, Array 
          (
            "id" => $work["work"]["id"],
            "title" => $work["work"]["title"],
            "searchtitle" => $work["work"]["searchtitle"]
          ));
    }

    // searching spotify

    $token = spotifyauth ();

    if ($return == "albums")
    {    
      $spalbums = spotifydownparse (SPOTIFYAPI. "/search/?limit=". SAPI_ITEMS. "&type=track&offset={$offset}&q=track:". trim(urlencode ($search. " artist:{$work["composer"]["complete_name"]}")), $token);
      $loop = 1;
      //$spalbums["tracks"]["next"] = $offset + SAPI_ITEMS;

      while ($spalbums["tracks"]["next"] && $loop <= SAPI_PAGES)
      {
        $morealbums = spotifydownparse ($spalbums["tracks"]["next"], $token);
        $spalbums["tracks"]["items"] = array_merge ($spalbums["tracks"]["items"], $morealbums["tracks"]["items"]);
        $spalbums["tracks"]["next"] = $morealbums["tracks"]["next"];
        $loop++;
      }

      //if ($spalbums["tracks"]["total"] < SAPI_ITEMS) $spalbums["tracks"]["next"] = "";
    }
    else if ($return == "tracks")
    {
      $fspalbums = spotifydownparse (SPOTIFYAPI. "/albums/". $work["recording"]["spotify_albumid"]. "/?limit=". SAPI_ITEMS, $token);

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
      similar_text ($work["work"]["searchtitle"], worksimplifier (explode (":", $alb["name"])[0]), $similarity);

      $albunsfound = Array 
        (
          "track_title" => $alb["name"],
          "search_title" => $work["work"]["searchtitle"],
          "simplified_track_title" => worksimplifier (explode (":", $alb["name"])[0]),
          "similarity" => $similarity
        );
      
      //if ($similarity > $spotres[$alb["album"]["id"]]["mostsimilar"] && $similarity > MIN_SIMILAR)

      foreach (explode (",", $work["work"]["alternatetitles"]) as $alttitle)
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
        preg_match_all ('/('. str_replace (' ', '( )*', str_replace ("/", "\/", trim(end($matches[2])))). ')(( |\.))*('. str_replace (' ', '( )*', trim(end($matches[8]))). '($| |\W))/i', $alb["name"], $trmatches);

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

      if ($similarity > MIN_SIMILAR && slug($alb["artists"][0]["name"]) == slug($work["composer"]["complete_name"])) 
      {
        $simwkdb = 0;
        $mostwkdb = 0;

        foreach ($wkdb as $wk)
        {
          //similar_text ($wk["title"], explode (":", $alb["name"])[0], $sim);
          similar_text (str_replace (" ", "", $wk["searchtitle"]), str_replace (" ", "", worksimplifier (explode (":", $alb["name"])[0], $work["work"]["fullname"])), $sim);
          
          //if ($sim > MIN_SIMILAR) echo $wk["id"]. " - ". $sim. " - ". $wk["searchtitle"]. " - ". worksimplifier (explode (":", $alb["name"])[0], true). "\n[". $alb["name"]. "]\n\n";
          
          if ($sim > $simwkdb) 
          {
            $simwkdb = $sim;
            $mostwkdb = $wk["id"];
            $mostwkdbtitle = $wk["searchtitle"];
          }
        }

        //echo "GUESSED: ". $mostwkdb. " - ". $mostwkdbtitle. "\n[". $alb["name"]. "]\n\n";
        
        if ($mostwkdb == $work["work"]["id"] || $mode == "catalogue")
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
              "similarity_between" => Array ($work["work"]["searchtitle"], worksimplifier (explode (":", $alb["name"])[0])),
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
              "title" => trim (str_replace ("(Live)", "", end (explode (":", end (explode ("/", $alb["name"])))))),
              "cd" => $alb["disc_number"],
              "position" => $alb["track_number"],
              "length" => round ($alb["duration_ms"] / 1000, 0, PHP_ROUND_HALF_UP),
              "spotify_trackid" => $alb["id"],
              "performers" => $performers
            );
          }

          $mostsimilar = $similarity;
        }
      }
    }

    $stats = Array 
      (
        "term_used" => trim(urlencode ($search. " artist:{$work["composer"]["complete_name"]}")),
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

    $extrarecordings = mysqlfetch ($mysql, "select ifnull(observation,'') observation, spotify_imgurl, spotify_albumid, subset, year, recommended, compilation, oldaudio, verified, wrongdata, spam, badquality from recording where ". $where);
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
        if ($ed["observation"]) $spot["extras"]["observation"] = $ed["observation"];
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
            if ($ed["observation"]) $spot["items"][$ed["spotify_albumid"]][$pos]["observation"] = $ed["observation"];
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
          $spot["items"]["{$ep["spotify_albumid"]}-{$ep["subset"]}"][0]["extraperformers"][] = $array;
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