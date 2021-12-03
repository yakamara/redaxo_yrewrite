<?php

/** @var rex_addon $this */

require __DIR__ . '/install.php';

if (rex_string::versionCompare($this->getVersion(), '2.1', '<=')) {
    $sql = rex_sql::factory();

    $sql->setQuery(sprintf(
        'INSERT INTO `%s` (domain_id, alias_domain, clang_start) 
            SELECT d.id, a.domain, a.clang_start FROM `%s` a INNER JOIN `%2$s` d ON d.domain = a.alias_domain AND d.alias_domain = "" WHERE a.alias_domain != ""',
        rex::getTable('yrewrite_alias'),
        rex::getTable('yrewrite_domain')
    ));

    $sql->setQuery(sprintf(
        'DELETE FROM `%s` WHERE alias_domain != ""',
        rex::getTable('yrewrite_domain')
    ));

    $sql->setQuery(sprintf(
        'UPDATE `%s` f SET domain_id = (SELECT id FROM `%s` d WHERE d.domain = f.domain)',
        rex::getTable('yrewrite_forward'),
        rex::getTable('yrewrite_domain')
    ));

    rex_sql_table::get(rex::getTable('yrewrite_domain'))
        ->removeColumn('alias_domain')
        ->ensureColumn(new rex_sql_column('auto_redirect', 'tinyint(1)'))
        ->ensureColumn(new rex_sql_column('auto_redirect_days', 'int(3)'))
        ->alter();

    rex_sql_table::get(rex::getTable('yrewrite_forward'))
        ->removeColumn('domain')
        ->ensureColumn(new rex_sql_column('expiry_date', 'date'))
        ->alter();

    rex_delete_cache();
}

if (rex_string::versionCompare($this->getVersion(), '2.7-dev', '<=')) {
    $where = 'clangs NOT LIKE "%,%"';
    if (rex_clang::count() > 1) {
        $where = 'clangs != "" AND '.$where;
    }

    rex_sql::factory()
        ->setTable(rex::getTable('yrewrite_domain'))
        ->setWhere($where)
        ->setValue('clang_start_hidden', 1)
        ->update();

    rex_yrewrite::deleteCache();
}

