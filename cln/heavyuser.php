<?
  chdir ($_SERVER["CTINHTMLDIR"]);
  include_once ("../lib/inc.php");

  // zeroing the db
  mysqli_query ($mysql, "update user set heavyuser='0'");

  // establishing averages
  $query = "select sum(ratio)/count(user_id) as ratioavg, sum(days)/count(user_id) as daysavg from (SELECT user_id, country, sum(plays) qtd, count(distinct date_format(from_unixtime(lastplay), '%Y%m%d')) days, sum(plays)/count(distinct date_format(from_unixtime(lastplay), '%Y%m%d')) as ratio from user_recording, user where user.id=user_id and country is not null group by user_id) as ratios";
  $avgs = mysqlfetch ($mysql, $query);

  // retrieving heavy users
  $query = "select user_id as id from (SELECT user_id, country, sum(plays) qtd, count(distinct date_format(from_unixtime(lastplay), '%Y%m%d')) days, sum(plays)/count(distinct date_format(from_unixtime(lastplay), '%Y%m%d')) as ratio from user_recording, user where user.id=user_id and country is not null group by user_id) as ratios where ratio>{$avgs[0]["ratioavg"]} and days>{$avgs[0]["daysavg"]}";
  $ids = mysqlfetch ($mysql, $query);

  // updating the db
  $query = "update user set heavyuser='1' where id in (". implode(",", arraykeepvalues ($ids, ["id"])). ")";
  mysqli_query ($mysql, $query);