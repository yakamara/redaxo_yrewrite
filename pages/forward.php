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

rex_yrewrite_forward::init();

if ($func != '') {
    $yform = new rex_yform();
    // $yform->setDebug(TRUE);
    $yform->setHiddenField('page', 'yrewrite/forward');
    $yform->setHiddenField('func', $func);
    $yform->setHiddenField('save', '1');

    $yform->setObjectparams('main_table', 'rex_yrewrite_forward');
    $yform->setObjectparams('main_table', 'rex_yrewrite_forward');

    $yform->setValueField('select', ['status', $this->i18n('forward_status'),''.$this->i18n('forward_active').'=1,'.$this->i18n('forward_inactive').'=0']);
    $yform->setValueField('select_sql', ['domain', $this->i18n('domain') . '', 'select domain as id,domain as name from rex_yrewrite_domain where alias_domain = ""']);
    $yform->setValueField('text', ['url', $this->i18n('forward_url')]);
    //$yform->setValidateField('preg_match', array('url', '@^(?<!\/)[%_\./+\-a-zA-Z0-9]+(?!\/)$@', $this->i18n('warning_chars')));
    $yform->setValidateField('preg_match', ['url', '@^([%_\.+\-a-zA-Z0-9]){1}[/%_\.+\-a-zA-Z0-9]+([%_\.+\-a-zA-Z0-9]){1}$@', $this->i18n('warning_chars')]);
    // $this->i18n('warning_noslash')
    $yform->setValidateField('size_range', ['url', 1, 255, $this->i18n('warning_nottolong')]);
    $yform->setValidateField('empty', ['url', $this->i18n('forward_enter_url')]);
    $yform->setValidateField('unique', ['domain,url', $this->i18n('forward_domainurl_already_defined')]);
    $yform->setValueField('select', ['movetype', $this->i18n('forward_move_method'), $this->i18n('forward_301').'=301,'.$this->i18n('forward_303').'=303,'.$this->i18n('forward_307').'=307','','303']);
    $yform->setValueField('select', ['type', $this->i18n('forward_type'),''.$this->i18n('forward_type_article').'=article,'.$this->i18n('forward_type_extern').'=extern,'.$this->i18n('forward_type_media').'=media']);

    $yform->setValueField('html', ['','<div id="rex-yrewrite-forward-article">']);
    $yform->setValueField('be_link', ['article_id', $this->i18n('forward_article_id')]);
    $yform->setValueField('select_sql', ['clang', $this->i18n('forward_clang') . '', 'select id, name from rex_clang']);
    $yform->setValueField('html', ['', '</div>']);

    $yform->setValueField('html', ['','<div id="rex-yrewrite-forward-extern">']);
    $yform->setValueField('text', ['extern', $this->i18n('forward_extern')]);
    $yform->setValueField('html', ['', '</div>']);

    $yform->setValueField('html', ['','<div id="rex-yrewrite-forward-media">']);
    $yform->setValueField('be_media', ['media', $this->i18n('forward_media')]);
    $yform->setValueField('html', ['', '</div>']);

    echo '<script>

jQuery(document).ready(function() {
  function rex_yrewrite_update_form() {
    jQuery("#rex-yrewrite-forward-article").hide();
    jQuery("#rex-yrewrite-forward-extern").hide();
    jQuery("#rex-yrewrite-forward-media").hide();
    jQuery("#rex-yrewrite-forward-"+jQuery("#yform-formular-type select").val()).show();
  }

  jQuery("#yform-formular-type select").on("change loaded", function(){
    rex_yrewrite_update_form();
  })

  rex_yrewrite_update_form();

});


</script>';

    if ($func == 'delete') {
        $d = rex_sql::factory();
        $d->setQuery('delete from rex_yrewrite_forward where id=' . $data_id);
        echo rex_view::success($this->i18n('forward_deleted'));
        rex_yrewrite_forward::generatePathFile();

    } else if ($func == 'edit') {
        $yform->setHiddenField('data_id', $data_id);
        $yform->setActionField('db', ['rex_yrewrite_forward', 'id=' . $data_id]);
        $yform->setObjectparams('main_id', $data_id);
        $yform->setObjectparams('main_where', "id=$data_id");
        $yform->setObjectparams('getdata', true);
        $yform->setObjectparams('submit_btn_label', rex_i18n::msg('save'));
        $form = $yform->getForm();

        if ($yform->objparams['actions_executed']) {
            echo rex_view::success($this->i18n('forward_updated'));
            rex_yrewrite_forward::generatePathFile();

        } else {

            $showlist = false;
            $fragment = new rex_fragment();
            $fragment->setVar('class', 'edit', false);
            $fragment->setVar('title', rex_i18n::msg('forward_edit'));
            $fragment->setVar('body', $form, false);
            echo $fragment->parse('core/page/section.php');

        }

    } else if ($func == 'add') {
        $yform->setActionField('db', ['rex_yrewrite_forward']);
        $yform->setObjectparams('submit_btn_label', rex_i18n::msg('add'));
        $form = $yform->getForm();

        if ($yform->objparams['actions_executed']) {
            echo rex_view::success($this->i18n('forward_added'));
            rex_yrewrite_forward::generatePathFile();

        } else {
            $showlist = false;

            $fragment = new rex_fragment();
            $fragment->setVar('class', 'edit', false);
            $fragment->setVar('title', $this->i18n('forward_add'));
            $fragment->setVar('body', $form, false);
            echo $fragment->parse('core/page/section.php');

        }
    }
}

if ($showlist) {
    $sql = 'SELECT * FROM rex_yrewrite_forward';

    $list = rex_list::factory($sql, 100);
    $list->setColumnFormat('id', 'Id');
    $list->addParam('page', 'yrewrite/forward');

    $tdIcon = '<i class="fa fa-sitemap"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '"' . rex::getAccesskey($this->i18n('forward_add'), 'add') . '><i class="rex-icon rex-icon-add"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'data_id' => '###id###']);

    $list->setColumnParams('id', ['data_id' => '###id###', 'func' => 'edit']);
    $list->setColumnSortable('id');

    $list->setColumnLabel('domain', $this->i18n('domain'));
    $list->setColumnLabel('status', $this->i18n('forward_status'));
    $list->setColumnLabel('url', $this->i18n('forward_url'));
    $list->setColumnLabel('type', $this->i18n('forward_type'));

    $list->removeColumn('id');
    $list->removeColumn('article_id');
    $list->removeColumn('clang');
    $list->removeColumn('extern');
    $list->removeColumn('media');
    $list->removeColumn('movetype');

    // $list->setColumnLabel('status', rex_i18n::msg('b_function'));
    $list->setColumnParams('status', ['func' => 'status', 'oid' => '###id###']);
    $list->setColumnLayout('status', ['<th>###VALUE###</th>', '<td>###VALUE###</td>']);
    $list->setColumnFormat('status', 'custom',
        create_function(
            '$params',
            'global $I18N;
$list = $params["list"];
if ($list->getValue("status") == 1)
$str = "<span class=\"rex-online\">".rex_i18n::msg("yrewrite_forward_active")."</span>";
else
$str = "<span class=\"rex-offline\">".rex_i18n::msg("yrewrite_forward_inactive")."</span>";
return $str;'
        )
    );

    $list->addColumn(rex_i18n::msg('function'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('function'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('function'), ['data_id' => '###id###', 'func' => 'edit', 'start' => rex_request('start', 'string')]);

    $list->addColumn(rex_i18n::msg('delete'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
    $list->setColumnLayout(rex_i18n::msg('delete'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('delete'), ['data_id' => '###id###', 'func' => 'delete']);
    $list->addLinkAttribute(rex_i18n::msg('delete'), 'onclick', 'return confirm(\' id=###id### ' . rex_i18n::msg('delete') . ' ?\')');


    $content = $list->get();


    $fragment = new rex_fragment();
    $fragment->setVar('title', $this->i18n('forward'));
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}
