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
    ->removeColumn('yrewrite_url')
    ->removeColumn('yrewrite_priority')
    ->removeColumn('yrewrite_changefreq')
    ->removeColumn('yrewrite_title')
    ->removeColumn('yrewrite_description')
    ->removeColumn('yrewrite_index')
    ->alter()
;

$sql = rex_sql::factory();

$sql->setQuery('DROP TABLE IF EXISTS `rex_yrewrite_domain`;');

$sql->setQuery('DROP TABLE IF EXISTS `rex_yrewrite_forward`;');

rex_delete_cache();
