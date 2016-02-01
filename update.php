<?php

rex_sql_table::get(rex::getTable('article'))
->ensureColumn(new rex_sql_column('yrewrite_url', 'varchar(255)'))
->ensureColumn(new rex_sql_column('yrewrite_canonical_url', 'varchar(255)'))
->ensureColumn(new rex_sql_column('yrewrite_priority', 'varchar(5)'))
->ensureColumn(new rex_sql_column('yrewrite_changefreq', 'varchar(10)'))
->ensureColumn(new rex_sql_column('yrewrite_title', 'varchar(255)'))
->ensureColumn(new rex_sql_column('yrewrite_description', 'text'))
->ensureColumn(new rex_sql_column('yrewrite_index', 'tinyint(1)'))
->alter();

// always delete cache after update
rex_yrewrite::deleteCache();
