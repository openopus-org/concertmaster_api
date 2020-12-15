<?
  chdir ($_SERVER["BASEHTMLDIR"]);
  include_once ("../lib/inc.php");

  $forbidden = implode ("|", $omnisearch_forbidden);
  $forbidden_regexp = '('. $forbidden. ')';

  mysqli_query ($mysql, "truncate table omnisearch");
  mysqli_query ($mysql, "insert into omnisearch select concat(composer_name, ' ', work_title, ' ', GROUP_CONCAT(REGEXP_REPLACE(performer, '{$forbidden_regexp}', '') separator ' ')) summary, concat(composer_name, ' ', work_title) worksummary, composer_name, '0', r.work_id, r.spotify_albumid, r.subset, '0', '0', '0', '0' from recording r, recording_performer rp where r.spotify_albumid=rp.spotify_albumid and r.subset=rp.subset and r.work_id=rp.work_id and r.composer_name != '' group by spotify_albumid, work_id, subset");
  mysqli_query ($mysql, "insert into omnisearch select concat(complete_name, ' ', title, ' ', COALESCE(subtitle,''), ' ', COALESCE(searchterms, ''), ' ', GROUP_CONCAT(REGEXP_REPLACE(performer, '{$forbidden_regexp}', '') separator ' ')) summary, concat(complete_name, ' ', title) worksummary, complete_name, work.composer_id, r.work_id, r.spotify_albumid, r.subset, work.recommended, work.popular, '0', '0' from recording r, recording_performer rp, openopus.composer, openopus.work where r.work_id=rp.work_id and r.spotify_albumid=rp.spotify_albumid and r.subset=rp.subset and r.work_id=work.id and composer_id = composer.id and (r.composer_name = '' or r.composer_name is null) group by spotify_albumid, work_id, subset");
  mysqli_query ($mysql, "update omnisearch as o inner join (select spotify_albumid, work_id, subset, sum(plays) pl, count(distinct user_id) users from user_recording ur where from_unixtime(lastplay) > (NOW() - INTERVAL 14 DAY) group by spotify_albumid, work_id, subset) as ur on o.spotify_albumid = ur.spotify_albumid and o.work_id = ur.work_id and o.subset = ur.subset set plays = ur.pl, o.users = ur.users");