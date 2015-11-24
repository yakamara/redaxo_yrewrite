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
    $yform = new rex_yform();
    // $yform->setDebug(TRUE);
    $yform->setHiddenField('page', 'yrewrite/alias_domains');
    $yform->setHiddenField('func', $func);
    $yform->setHiddenField('save', '1');

    $yform->setObjectparams('main_table', 'rex_yrewrite_domain');

    $yform->setValueField('text', ['domain', $this->i18n('alias_domain_refersto')]);
    $yform->setValueField('select_sql', ['alias_domain', $this->i18n('domain_willbereferdto') . '', 'select domain as id,domain as name from rex_yrewrite_domain where alias_domain = ""']);
    if (rex_clang::count() > 1) {
        $yform->setValueField('select_sql', ['clang_start', $this->i18n('clang_start'), 'select id,name from rex_clang order by id']);
    }

    $yform->setValidateField('empty', ['domain', $this->i18n('no_domain_defined')]);
    $yform->setValidateField('empty', ['alias_domain', $this->i18n('no_domain_defined')]);
    $yform->setValidateField('unique', ['domain', $this->i18n('domain_already_defined')]);

    if ($func == 'delete') {
        $d = rex_sql::factory();
        $d->setQuery('delete from rex_yrewrite_domain where id=' . $data_id);
        echo rex_view::success($this->i18n('domain_deleted'));
        rex_yrewrite::deleteCache();

    } elseif ($func == 'edit') {
        $yform->setHiddenField('data_id', $data_id);
        $yform->setActionField('db', ['rex_yrewrite_domain', 'id=' . $data_id]);
        $yform->setObjectparams('main_id', $data_id);
        $yform->setObjectparams('main_where', "id=$data_id");
        $yform->setObjectparams('getdata', true);
        $yform->setObjectparams('submit_btn_label', rex_i18n::msg('save'));
        $form = $yform->getForm();

        if ($yform->objparams['actions_executed']) {
            echo rex_view::success($this->i18n('domain_updated'));
            rex_yrewrite::deleteCache();

        } else {

            $showlist = false;
            $fragment = new rex_fragment();
            $fragment->setVar('class', 'edit', false);
            $fragment->setVar('title', $this->i18n('edit_domain'));
            $fragment->setVar('body', $form, false);
            echo $fragment->parse('core/page/section.php');

        }

    } else if ($func == 'add') {

        $yform->setActionField('db', ['rex_yrewrite_domain']);
        $yform->setObjectparams('submit_btn_label', rex_i18n::msg('add'));
        $form = $yform->getForm();

        if ($yform->objparams['actions_executed']) {
            echo rex_view::success($this->i18n('domain_added'));
            rex_yrewrite::deleteCache();

        } else {
            $showlist = false;
            $fragment = new rex_fragment();
            $fragment->setVar('class', 'edit', false);
            $fragment->setVar('title', $this->i18n('add_domain'));
            $fragment->setVar('body', $form, false);
            echo $fragment->parse('core/page/section.php');

        }
    }
}

if ($showlist) {

    $sql = 'SELECT * FROM rex_yrewrite_domain where alias_domain <> ""';

    $list = rex_list::factory($sql, 100);
    $list->setColumnFormat('id', 'Id');
    $list->addParam('page', 'yrewrite/alias_domains');

    $tdIcon = '<i class="fa fa-sitemap"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '"' . rex::getAccesskey($this->i18n('add_domain'), 'add') . '><i class="rex-icon rex-icon-add"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'data_id' => '###id###']);

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
