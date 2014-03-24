<?php

/**
 * YREWRITE Addon
 * @author jan.kristinus@yakamara.de
 * @package redaxo4.5
 */

$addonname = 'yrewrite';

$REX['ADDON']['install']['yrewrite'] = 1;
// ERRMSG IN CASE: $REX['ADDON']['installmsg']['yrewrite'] = "Leider konnte nichts installiert werden da.";

$I18N->appendFile($REX['INCLUDE_PATH'] . '/addons/yrewrite/lang');

if ($REX['VERSION'] != '4' || $REX['SUBVERSION'] < '5') {
    $REX['ADDON']['install']['yrewrite'] = 0;
    $REX['ADDON']['installmsg']['yrewrite'] = $I18N->msg('yrewrite_install_redaxo_version_problem', $REX['VERSION'] . '.' . $REX['SUBVERSION'], '4.5');

} elseif (OOAddon::isAvailable('xform') != 1 || version_compare(OOAddon::getVersion('xform'), '4.5', '<')) {
    $REX['ADDON']['install']['yrewrite'] = 0;
    $REX['ADDON']['installmsg']['yrewrite'] = $I18N->msg('yrewrite_install_xform_version_problem', '4.5');

} elseif (version_compare(PHP_VERSION, '5.3.0', '<')) {
    $REX['ADDON']['install']['yrewrite'] = 0;
    $REX['ADDON']['installmsg']['yrewrite'] = $I18N->msg('yrewrite_install_php_version_problem', '5.3.0', PHP_VERSION);

} else {
    $sql = rex_sql::factory();
    $sql->setQuery('ALTER TABLE `rex_article` ADD `yrewrite_url` VARCHAR( 255 ) NOT NULL ;');
    $sql->setQuery('ALTER TABLE `rex_article` ADD `yrewrite_priority` VARCHAR( 5 ) NOT NULL;');
    $sql->setQuery('ALTER TABLE `rex_article` ADD `yrewrite_changefreq` VARCHAR( 10 ) NOT NULL ;');
    $sql->setQuery('ALTER TABLE `rex_article` ADD `yrewrite_title` VARCHAR( 255 ) NOT NULL ;');
    $sql->setQuery('ALTER TABLE `rex_article` ADD `yrewrite_description` TEXT NOT NULL ;');

    $sql->setQuery('UPDATE `rex_article` set `yrewrite_priority` = ``;');

    $sql->setQuery('CREATE TABLE IF NOT EXISTS `rex_yrewrite_domain` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `domain` varchar(255) NOT NULL,
    `mount_id` int(11) NOT NULL,
    `start_id` int(11) NOT NULL,
    `notfound_id` int(11) NOT NULL,
    `alias_domain` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;');

    $sql->setQuery('CREATE TABLE IF NOT EXISTS `rex_yrewrite_forward` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `domain` varchar(255) NOT NULL,
    `status` int(11) NOT NULL,
    `url` varchar(255) NOT NULL,
    `type` varchar(255) NOT NULL,
    `article_id` int(11) NOT NULL,
    `clang` int(11) NOT NULL,
    `extern` varchar(255) NOT NULL,
    `media` varchar(255) NOT NULL,
    `movetype` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;');

    $sql->setQuery('ALTER TABLE `rex_yrewrite_domain` ADD `clangs` varchar(255) NOT NULL;');
    $sql->setQuery('ALTER TABLE `rex_yrewrite_domain` ADD `clang_start` varchar(255) NOT NULL;');
    $sql->setQuery('ALTER TABLE `rex_yrewrite_domain` DROP `clang`;');
    $sql->setQuery('ALTER TABLE `rex_yrewrite_domain` ADD `robots` TEXT NOT NULL ;');
    $sql->setQuery('ALTER TABLE `rex_yrewrite_domain` ADD `title_scheme` varchar(255) NOT NULL;');
    $sql->setQuery('ALTER TABLE `rex_yrewrite_domain` ADD `description` varchar(255) NOT NULL;');
}
