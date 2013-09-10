<?php

/**
 * YREWRITE Addon
 * @author jan.kristinus@yakamara.de
 * @package redaxo4.5
 */

$showlist = true;
$data_id = rex_request('data_id', 'int', 0);
$func = rex_request('func', 'string');

if ($func != '') {

    $xform = new rex_xform;
    // $xform->setDebug(TRUE);
    $xform->setHiddenField('page', 'yrewrite');
    $xform->setHiddenField('subpage', 'alias_domains');
    $xform->setHiddenField('func', $func);
    $xform->setHiddenField('save', '1');

    $xform->setObjectparams('main_table', 'rex_yrewrite_domain');

    $xform->setValueField('text', array('domain', $I18N->msg('yrewrite_alias_domain_refersto')));
    $xform->setValueField('select_sql', array('alias_domain', $I18N->msg('yrewrite_domain_willbereferdto') . '', 'select domain as id,domain as name from rex_yrewrite_domain where alias_domain = ""'));

    $xform->setValidateField('empty', array('domain', $I18N->msg('yrewrite_no_domain_defined')));
    $xform->setValidateField('empty', array('alias_domain', $I18N->msg('yrewrite_no_domain_defined')));
    $xform->setValidateField('unique', array('domain,alias_domain', $I18N->msg('yrewrite_domain_already_defined')));

    if ($func == 'delete') {

        $d = rex_sql::factory();
        $d->setQuery('delete from rex_yrewrite_domain where id=' . $data_id);
        echo rex_info($I18N->msg('yrewrite_domain_deleted'));
        rex_yrewrite::deleteCache();

    } elseif ($func == 'edit') {

        $xform->setHiddenField('data_id', $data_id);
        $xform->setActionField('db', array('rex_yrewrite_domain', 'id=' . $data_id));
        $xform->setObjectparams('main_id', $data_id);
        $xform->setObjectparams('main_where', "id=$data_id");
        $xform->setObjectparams('getdata', true);
        $xform->setObjectparams('submit_btn_label', $I18N->msg('save'));
        $form = $xform->getForm();

        if ($xform->objparams['actions_executed']) {
            echo rex_info($I18N->msg('yrewrite_domain_updated'));
            rex_yrewrite::deleteCache();
        } else {
            $showlist = false;
            echo '<div class="rex-area">
                            <h3 class="rex-hl2">' . $I18N->msg('yrewrite_edit_domain') . '</h3>
                            <div class="rex-area-content">';
            echo $form;
            echo '</div></div>';
        }

    } elseif ($func == 'add') {

        $xform->setActionField('db', array('rex_yrewrite_domain'));
        $xform->setObjectparams('submit_btn_label', $I18N->msg('add'));
        $form = $xform->getForm();

        if ($xform->objparams['actions_executed']) {
            echo rex_info($I18N->msg('yrewrite_domain_added'));
            rex_yrewrite::deleteCache();
        } else {
            $showlist = false;
            echo '<div class="rex-area">
                            <h3 class="rex-hl2">' . $I18N->msg('yrewrite_add_domain') . '</h3>
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
    $list->addParam('page', 'yrewrite');
    $list->addParam('subpage', 'alias_domains');

    $header = '<a class="rex-i-element rex-i-generic-add" href="' . $list->getUrl(array('func' => 'add')) . '"><span class="rex-i-element-text">' . $I18N->msg('yrewrite_add_domain') . '</span></a>';
    $list->addColumn($header, '###id###', 0, array('<th class="rex-icon">###VALUE###</th>', '<td class="rex-small">###VALUE###</td>'));

    $list->setColumnParams('id', array('data_id' => '###id###', 'func' => 'edit' ));
    $list->setColumnSortable('id');

    $list->removeColumn('id');

    $list->setColumnLabel('domain', $I18N->msg('yrewrite_domain'));
    $list->setColumnLabel('alias_domain', $I18N->msg('yrewrite_alias_domain'));
    // $list->setColumnLabel("alias_domain",$I18N->msg("yrewrite_alias_domain"));
    // $list->removeColumn("alias_domain","alias_domain");

    $list->addColumn($I18N->msg('delete'), $I18N->msg('delete'));
    $list->setColumnParams($I18N->msg('delete'), array('data_id' => '###id###', 'func' => 'delete'));
    $list->addLinkAttribute($I18N->msg('delete'), 'onclick', 'return confirm(\' id=###id### ' . $I18N->msg('delete') . ' ?\')');

    $list->addColumn($I18N->msg('edit'), $I18N->msg('edit'));
    $list->setColumnParams($I18N->msg('edit'), array('data_id' => '###id###', 'func' => 'edit', 'start' => rex_request('start', 'string')));

    $list->removeColumn('clang');
    $list->removeColumn('mount_id');
    $list->removeColumn('start_id');
    $list->removeColumn('notfound_id');
    $list->removeColumn('robots', 'robots');
    $list->removeColumn('title_scheme', 'title_scheme');
    $list->removeColumn('description', 'description');

    echo $list->get();


}
