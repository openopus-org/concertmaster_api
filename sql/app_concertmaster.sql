SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `playlist` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(256) NOT NULL,
  `user_id` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `playlist_recording` (
  `playlist_id` int(10) UNSIGNED NOT NULL,
  `position` int(10) UNSIGNED DEFAULT NULL,
  `work_id` int(11) UNSIGNED NOT NULL,
  `spotify_albumid` varchar(256) CHARACTER SET latin1 NOT NULL,
  `subset` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `recording` (
  `work_id` int(11) UNSIGNED NOT NULL,
  `year` date DEFAULT NULL,
  `recommended` tinyint(1) DEFAULT NULL,
  `compilation` tinyint(1) DEFAULT 0,
  `oldaudio` tinyint(1) DEFAULT 0,
  `upc` varchar(256) DEFAULT NULL,
  `spotify_albumid` varchar(256) NOT NULL,
  `spotify_imgurl` varchar(1024) DEFAULT NULL,
  `subset` int(11) UNSIGNED NOT NULL DEFAULT 1,
  `verified` tinyint(1) DEFAULT 0,
  `wrongdata` tinyint(1) DEFAULT 0,
  `spam` tinyint(1) DEFAULT 0,
  `badquality` tinyint(1) DEFAULT 0,
  `observation` varchar(1060) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `recording_performer` (
  `spotify_albumid` varchar(256) NOT NULL,
  `work_id` int(11) UNSIGNED NOT NULL,
  `subset` int(11) UNSIGNED NOT NULL DEFAULT 1,
  `performer` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `shortrec` (
  `id` int(11) UNSIGNED NOT NULL,
  `work_id` int(11) UNSIGNED NOT NULL,
  `spotify_albumid` varchar(256) CHARACTER SET latin1 NOT NULL,
  `subset` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `track` (
  `work_id` int(11) UNSIGNED NOT NULL,
  `spotify_albumid` varchar(256) NOT NULL,
  `subset` int(11) UNSIGNED NOT NULL,
  `cd` int(10) UNSIGNED NOT NULL,
  `position` int(10) UNSIGNED NOT NULL,
  `length` int(10) UNSIGNED NOT NULL,
  `title` varchar(256) NOT NULL,
  `spotify_trackid` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `user` (
  `id` varchar(256) NOT NULL,
  `auth` varchar(128) NOT NULL,
  `name` varchar(256) DEFAULT NULL,
  `email` varchar(256) DEFAULT NULL,
  `country` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `user_composer` (
  `user_id` varchar(256) NOT NULL,
  `composer_id` int(10) UNSIGNED NOT NULL,
  `favorite` tinyint(1) DEFAULT NULL,
  `forbidden` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `user_playlist` (
  `user_id` varchar(256) NOT NULL,
  `playlist_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `user_recording` (
  `user_id` varchar(256) CHARACTER SET latin1 NOT NULL,
  `work_id` int(11) UNSIGNED NOT NULL,
  `spotify_albumid` varchar(256) CHARACTER SET latin1 NOT NULL,
  `subset` int(11) NOT NULL DEFAULT 1,
  `favorite` tinyint(1) DEFAULT NULL,
  `plays` int(10) UNSIGNED DEFAULT 0,
  `lastplay` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_work` (
  `user_id` varchar(256) NOT NULL,
  `work_id` int(10) UNSIGNED NOT NULL,
  `composer_id` int(10) UNSIGNED DEFAULT NULL,
  `favorite` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `playlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `playlist_recording`
  ADD UNIQUE KEY `playlist_id` (`playlist_id`,`subset`,`work_id`,`spotify_albumid`),
  ADD KEY `position` (`position`);

ALTER TABLE `recording`
  ADD PRIMARY KEY (`work_id`,`spotify_albumid`,`subset`) USING BTREE,
  ADD KEY `work_id` (`work_id`),
  ADD KEY `year` (`year`),
  ADD KEY `recommended` (`recommended`),
  ADD KEY `compilation` (`compilation`),
  ADD KEY `verified` (`verified`),
  ADD KEY `badquality` (`badquality`),
  ADD KEY `wrong` (`wrongdata`),
  ADD KEY `oldaudio` (`oldaudio`),
  ADD KEY `spam` (`spam`);

ALTER TABLE `recording_performer`
  ADD KEY `role` (`role`),
  ADD KEY `performer` (`performer`(191)),
  ADD KEY `spotify_albumid` (`spotify_albumid`,`work_id`,`subset`);

ALTER TABLE `shortrec`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `album` (`work_id`,`spotify_albumid`,`subset`);

ALTER TABLE `track`
  ADD PRIMARY KEY (`spotify_trackid`) USING BTREE,
  ADD KEY `position` (`position`),
  ADD KEY `cd` (`cd`),
  ADD KEY `work_id` (`work_id`,`spotify_albumid`,`subset`);

ALTER TABLE `user`
  ADD UNIQUE KEY `spotify_id` (`id`),
  ADD KEY `since` (`auth`),
  ADD KEY `name` (`name`),
  ADD KEY `country` (`country`);

ALTER TABLE `user_composer`
  ADD PRIMARY KEY (`user_id`,`composer_id`),
  ADD KEY `favorite` (`favorite`),
  ADD KEY `forbidden` (`forbidden`);

ALTER TABLE `user_playlist`
  ADD PRIMARY KEY (`user_id`,`playlist_id`);

ALTER TABLE `user_recording`
  ADD PRIMARY KEY (`user_id`,`work_id`,`spotify_albumid`,`subset`) USING BTREE,
  ADD KEY `lastplay` (`lastplay`),
  ADD KEY `plays` (`plays`),
  ADD KEY `favorite` (`favorite`);

ALTER TABLE `user_work`
  ADD PRIMARY KEY (`user_id`,`work_id`),
  ADD KEY `favorite` (`favorite`),
  ADD KEY `composer_id` (`composer_id`);


ALTER TABLE `playlist`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `shortrec`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
