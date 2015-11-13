<?php

/**
 * YREWRITE Addon.
 *
 * @author jan.kristinus@yakamara.de
 *
 * @package redaxo\yrewrite
 *
 * @var rex_addon $this
 */

rex_sql_table::get('rex_article')
    ->ensureColumn(new rex_sql_column('yrewrite_url', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('yrewrite_priority', 'varchar(5)'))
    ->ensureColumn(new rex_sql_column('yrewrite_changefreq', 'varchar(10)'))
    ->ensureColumn(new rex_sql_column('yrewrite_title', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('yrewrite_description', 'text'))
    ->ensureColumn(new rex_sql_column('yrewrite_index', 'tinyint(1)'))
    ->alter()
;

$sql = rex_sql::factory();

$sql->setQuery('CREATE TABLE IF NOT EXISTS `rex_yrewrite_domain` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `domain` varchar(255) NOT NULL,
    `mount_id` int(11) NOT NULL,
    `start_id` int(11) NOT NULL,
    `notfound_id` int(11) NOT NULL,
    `alias_domain` varchar(255) NOT NULL,
    `clangs` varchar(255) NOT NULL,
    `clang_start` int(11) NOT NULL,
    `robots` TEXT NOT NULL,
    `title_scheme` varchar(255) NOT NULL,
    `description` varchar(255) NOT NULL,
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

rex_delete_cache();
