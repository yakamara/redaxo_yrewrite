<?php

/** @var rex_addon $this */

require __DIR__ . '/install.php';

if (rex_string::versionCompare($this->getVersion(), '2.1', '<=')) {
    rex_sql::factory()->setQuery(sprintf(
        'UPDATE `%s` f SET domain_id = (SELECT id FROM `%s` d WHERE d.domain = f.domain)',
        rex::getTable('yrewrite_forward'),
        rex::getTable('yrewrite_domain')
    ));

    rex_sql_table::get(rex::getTable('yrewrite_forward'))
        ->removeColumn('domain')
        ->alter();

    rex_delete_cache();
}

