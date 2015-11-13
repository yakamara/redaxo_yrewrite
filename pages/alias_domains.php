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

$showlist = true;
$data_id = rex_request('data_id', 'int', 0);
$func = rex_request('func', 'string');

if ($func != '') {
    $xform = new rex_yform();
    // $xform->setDebug(TRUE);
    $xform->setHiddenField('page', 'yrewrite/alias_domains');
    $xform->setHiddenField('func', $func);
    $xform->setHiddenField('save', '1');

    $xform->setObjectparams('main_table', 'rex_yrewrite_domain');

    $xform->setValueField('text', ['domain', $this->i18n('alias_domain_refersto')]);
    $xform->setValueField('select_sql', ['alias_domain', $this->i18n('domain_willbereferdto') . '', 'select domain as id,domain as name from rex_yrewrite_domain where alias_domain = ""']);
    if (rex_clang::count() > 1) {
        $xform->setValueField('select_sql', ['clang_start', $this->i18n('clang_start'), 'select id,name from rex_clang order by id']);
    }

    $xform->setValidateField('empty', ['domain', $this->i18n('no_domain_defined')]);
    $xform->setValidateField('empty', ['alias_domain', $this->i18n('no_domain_defined')]);
    $xform->setValidateField('unique', ['domain', $this->i18n('domain_already_defined')]);

    if ($func == 'delete') {
        $d = rex_sql::factory();
        $d->setQuery('delete from rex_yrewrite_domain where id=' . $data_id);
        echo rex_view::info($this->i18n('domain_deleted'));
        rex_yrewrite::deleteCache();
    } elseif ($func == 'edit') {
        $xform->setHiddenField('data_id', $data_id);
        $xform->setActionField('db', ['rex_yrewrite_domain', 'id=' . $data_id]);
        $xform->setObjectparams('main_id', $data_id);
        $xform->setObjectparams('main_where', "id=$data_id");
        $xform->setObjectparams('getdata', true);
        $xform->setObjectparams('submit_btn_label', rex_i18n::msg('save'));
        $form = $xform->getForm();

        if ($xform->objparams['actions_executed']) {
            echo rex_view::info($this->i18n('domain_updated'));
            rex_yrewrite::deleteCache();
        } else {
            $showlist = false;
            echo '<div class="rex-area">
                            <h3 class="rex-hl2">' . $this->i18n('edit_domain') . '</h3>
                            <div class="rex-area-content">';
            echo $form;
            echo '</div></div>';
        }
    } elseif ($func == 'add') {
        $xform->setActionField('db', ['rex_yrewrite_domain']);
        $xform->setObjectparams('submit_btn_label', rex_i18n::msg('add'));
        $form = $xform->getForm();

        if ($xform->objparams['actions_executed']) {
            echo rex_view::info($this->i18n('domain_added'));
            rex_yrewrite::deleteCache();
        } else {
            $showlist = false;
            echo '<div class="rex-area">
                            <h3 class="rex-hl2">' . $this->i18n('add_domain') . '</h3>
                            <div class="rex-area-content">';
            echo $form;
            echo '</div></div>';
        }
    }
}

if ($showlist) {
    $sql = 'SELECT * FROM rex_yrewrite_domain where alias_domain <> ""';

    $list = rex_list::factory($sql, 100);
    $list->setColumnFormat('id', 'Id');
    $list->addParam('page', 'yrewrite/alias_domains');

    $header = '<a class="rex-i-element rex-i-generic-add" href="' . $list->getUrl(['func' => 'add']) . '"><span class="rex-i-element-text">' . $this->i18n('add_domain') . '</span></a>';
    $list->addColumn($header, '###id###', 0, ['<th class="rex-icon">###VALUE###</th>', '<td class="rex-small">###VALUE###</td>']);

    $list->setColumnParams('id', ['data_id' => '###id###', 'func' => 'edit']);
    $list->setColumnSortable('id');

    $list->removeColumn('id');
    $list->removeColumn('clangs');
    $list->removeColumn('clang_start');

    $list->setColumnLabel('domain', $this->i18n('domain'));
    $list->setColumnLabel('alias_domain', $this->i18n('alias_domain'));
    // $list->setColumnLabel("alias_domain",$this->i18n("alias_domain"));
    // $list->removeColumn("alias_domain","alias_domain");

    $list->addColumn(rex_i18n::msg('delete'), rex_i18n::msg('delete'));
    $list->setColumnParams(rex_i18n::msg('delete'), ['data_id' => '###id###', 'func' => 'delete']);
    $list->addLinkAttribute(rex_i18n::msg('delete'), 'onclick', 'return confirm(\' id=###id### ' . rex_i18n::msg('delete') . ' ?\')');

    $list->addColumn(rex_i18n::msg('edit'), rex_i18n::msg('edit'));
    $list->setColumnParams(rex_i18n::msg('edit'), ['data_id' => '###id###', 'func' => 'edit', 'start' => rex_request('start', 'string')]);

    $list->removeColumn('clang');
    $list->removeColumn('mount_id');
    $list->removeColumn('start_id');
    $list->removeColumn('notfound_id');
    $list->removeColumn('robots', 'robots');
    $list->removeColumn('title_scheme', 'title_scheme');
    $list->removeColumn('description', 'description');

    echo $list->get();
}
