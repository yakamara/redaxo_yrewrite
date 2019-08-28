<?php

/**
 * YRewrite.
 *
 * @author jan.kristinus@yakamara.de
 * @package redaxo\yrewrite
 *
 * @var rex_addon $this
 */

rex_sql_table::get(rex::getTable('article'))
    ->ensureColumn(new rex_sql_column('yrewrite_url', 'text'))
    ->ensureColumn(new rex_sql_column('yrewrite_canonical_url', 'text'))
    ->ensureColumn(new rex_sql_column('yrewrite_priority', 'varchar(5)'))
    ->ensureColumn(new rex_sql_column('yrewrite_changefreq', 'varchar(10)'))
    ->ensureColumn(new rex_sql_column('yrewrite_title', 'varchar(191)'))
    ->ensureColumn(new rex_sql_column('yrewrite_description', 'text'))
    ->ensureColumn(new rex_sql_column('yrewrite_index', 'tinyint(1)'))
    ->alter()
;

$table = rex_sql_table::get(rex::getTable('yrewrite_domain'));
$table
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('domain', 'varchar(191)'))
    ->ensureColumn(new rex_sql_column('mount_id', 'int(11)'))
    ->ensureColumn(new rex_sql_column('start_id', 'int(11)'))
    ->ensureColumn(new rex_sql_column('notfound_id', 'int(11)'))
    ->ensureColumn(new rex_sql_column('clangs', 'varchar(191)'))
    ->ensureColumn(new rex_sql_column('clang_start', 'int(11)'))
    ->ensureColumn(new rex_sql_column('clang_start_hidden', 'tinyint(1)'))
    ->ensureColumn(new rex_sql_column('robots', 'text'))
    ->ensureColumn(new rex_sql_column('title_scheme', 'varchar(191)'))
    ->ensureColumn(new rex_sql_column('description', 'varchar(191)'))
    ->ensureColumn(new rex_sql_column('auto_redirect', 'tinyint(1)'))
    ->ensureColumn(new rex_sql_column('auto_redirect_days', 'int(3)'))
    ->ensure();


$table = rex_sql_table::get(rex::getTable('yrewrite_alias'));
$table
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('alias_domain', 'varchar(191)'))
    ->ensureColumn(new rex_sql_column('domain_id', 'int(11)'))
    ->ensureColumn(new rex_sql_column('clang_start', 'int(11)'))
    ->ensure();

$table = rex_sql_table::get(rex::getTable('yrewrite_forward'));
$table
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('domain_id', 'int(11)'))
    ->ensureColumn(new rex_sql_column('status', 'int(11)'))
    ->ensureColumn(new rex_sql_column('url', 'varchar(191)'))
    ->ensureColumn(new rex_sql_column('type', 'varchar(191)'))
    ->ensureColumn(new rex_sql_column('article_id', 'int(11)'))
    ->ensureColumn(new rex_sql_column('clang', 'int(11)'))
    ->ensureColumn(new rex_sql_column('extern', 'varchar(191)'))
    ->ensureColumn(new rex_sql_column('media', 'varchar(191)'))
    ->ensureColumn(new rex_sql_column('movetype', 'varchar(191)'))
    ->ensureColumn(new rex_sql_column('expiry_date', 'date'))
    ->ensure();

$c = rex_sql::factory();
$c->setQuery('ALTER TABLE `' . rex::getTable('yrewrite_domain') . '` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
$c->setQuery('ALTER TABLE `' . rex::getTable('yrewrite_alias') . '` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
$c->setQuery('ALTER TABLE `' . rex::getTable('yrewrite_forward') . '` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');

rex_delete_cache();
