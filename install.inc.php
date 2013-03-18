<?php

$REX['ADDON']['install']['yrewrite'] = 1;
// ERRMSG IN CASE: $REX['ADDON']['installmsg']['yrewrite'] = "Leider konnte nichts installiert werden da.";

$sql = rex_sql::factory();
$sql->setQuery('ALTER TABLE `rex_article` ADD `yrewrite_url` VARCHAR( 255 ) NOT NULL ;');


?>