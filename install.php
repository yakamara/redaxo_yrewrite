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

rex_sql_table::get(rex::getTable('article'))
    ->ensureColumn(new rex_sql_column('yrewrite_url', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('yrewrite_canonical_url', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('yrewrite_priority', 'varchar(5)'))
    ->ensureColumn(new rex_sql_column('yrewrite_changefreq', 'varchar(10)'))
    ->ensureColumn(new rex_sql_column('yrewrite_title', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('yrewrite_description', 'text'))
    ->ensureColumn(new rex_sql_column('yrewrite_index', 'tinyint(1)'))
    ->alter()
;

$sql = rex_sql::factory();

$sql->setQuery('CREATE TABLE IF NOT EXISTS `'.rex::getTable('yrewrite_domain').'` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `domain` varchar(255) NOT NULL,
    `mount_id` int(11) NOT NULL,
    `start_id` int(11) NOT NULL,
    `notfound_id` int(11) NOT NULL,
    `clangs` varchar(255) NOT NULL,
    `clang_start` int(11) NOT NULL,
    `clang_start_hidden` tinyint(1) NOT NULL,
    `robots` TEXT NOT NULL,
    `title_scheme` varchar(255) NOT NULL,
    `description` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');

$sql->setQuery('CREATE TABLE IF NOT EXISTS `'.rex::getTable('yrewrite_alias').'` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `alias_domain` varchar(255) NOT NULL,
    `domain_id` int(11) NOT NULL,
    `clang_start` int(11) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');

$sql->setQuery('CREATE TABLE IF NOT EXISTS `'.rex::getTable('yrewrite_forward').'` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `domain_id` int(11) NOT NULL,
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

rex_sql_table::get(rex::getTable('yrewrite_domain'))
    ->ensureColumn(new rex_sql_column('clang_start_hidden', 'tinyint(1)'))
    ->alter()
;

rex_sql_table::get(rex::getTable('yrewrite_forward'))
    ->ensureColumn(new rex_sql_column('domain_id', 'int(11)'))
    ->alter()
;

rex_delete_cache();

rex_yrewrite_seo_visibility::install();
