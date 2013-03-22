<?php

$REX['ADDON']['install']['yrewrite'] = 1;
// ERRMSG IN CASE: $REX['ADDON']['installmsg']['yrewrite'] = "Leider konnte nichts installiert werden da.";

$sql = rex_sql::factory();
$sql->setQuery('ALTER TABLE `rex_article` ADD `yrewrite_url` VARCHAR( 255 ) NOT NULL ;');

$sql->setQuery('CREATE TABLE IF NOT EXISTS `rex_yrewrite_domain` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL,
  `mount_id` int(11) NOT NULL,
  `start_id` int(11) NOT NULL,
  `notfound_id` int(11) NOT NULL,
  `alias_domain` varchar(255) NOT NULL,
  `clang` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;');

?>