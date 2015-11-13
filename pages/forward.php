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
    $xform = new rex_yform();
    // $xform->setDebug(TRUE);
    $xform->setHiddenField('page', 'yrewrite/forward');
    $xform->setHiddenField('func', $func);
    $xform->setHiddenField('save', '1');

    $xform->setObjectparams('main_table', 'rex_yrewrite_forward');

    $xform->setValueField('select', ['status', $this->i18n('forward_status'),''.$this->i18n('forward_active').'=1,'.$this->i18n('forward_inactive').'=0']);
    $xform->setValueField('select_sql', ['domain', $this->i18n('domain') . '', 'select domain as id,domain as name from rex_yrewrite_domain where alias_domain = ""']);
    //$xform->setValidateField('preg_match', array('url', '@^(?<!\/)[%_\./+\-a-zA-Z0-9]+(?!\/)$@', $this->i18n('warning_chars')));
    $xform->setValidateField('preg_match', ['url', '@^([%_\.+\-a-zA-Z0-9]){1}[/%_\.+\-a-zA-Z0-9]+([%_\.+\-a-zA-Z0-9]){1}$@', $this->i18n('warning_chars')]);
    // $this->i18n('warning_noslash')
    $xform->setValidateField('size_range', ['url', 1, 255, $this->i18n('warning_nottolong')]);
    $xform->setValidateField('empty', ['url', $this->i18n('forward_enter_url')]);
    $xform->setValueField('text', ['url', $this->i18n('forward_url')]);
    $xform->setValidateField('unique', ['domain,url', $this->i18n('forward_domainurl_already_defined')]);
    $xform->setValueField('select', ['movetype', $this->i18n('forward_move_method'), $this->i18n('forward_301').'=301,'.$this->i18n('forward_303').'=303,'.$this->i18n('forward_307').'=307','','303']);
    $xform->setValueField('select', ['type', $this->i18n('forward_type'),''.$this->i18n('forward_type_article').'=article,'.$this->i18n('forward_type_extern').'=extern,'.$this->i18n('forward_type_media').'=media']);

    $xform->setValueField('html', ['','<div id="rex-yrewrite-forward-article">']);
    $xform->setValueField('be_link', ['article_id', $this->i18n('forward_article_id')]);
    $xform->setValueField('select_sql', ['clang', $this->i18n('forward_clang') . '', 'select id, name from rex_clang']);
    $xform->setValueField('html', ['', '</div>']);

    $xform->setValueField('html', ['','<div id="rex-yrewrite-forward-extern">']);
    $xform->setValueField('text', ['extern', $this->i18n('forward_extern')]);
    $xform->setValueField('html', ['', '</div>']);

    $xform->setValueField('html', ['','<div id="rex-yrewrite-forward-media">']);
    $xform->setValueField('be_media', ['media', $this->i18n('forward_media')]);
    $xform->setValueField('html', ['', '</div>']);

    echo '<script>

jQuery(document).ready(function()
{

  function rex_yrewrite_update_form()
  {
    jQuery("#rex-yrewrite-forward-article").hide();
    jQuery("#rex-yrewrite-forward-extern").hide();
    jQuery("#rex-yrewrite-forward-media").hide();
    jQuery("#rex-yrewrite-forward-"+jQuery("#xform-formular-type select").val()).show();
  }

  jQuery("#xform-formular-type select").on("change loaded", function(){
    rex_yrewrite_update_form();
  })

  rex_yrewrite_update_form();

});


</script>';

    if ($func == 'delete') {
        $d = rex_sql::factory();
        $d->setQuery('delete from rex_yrewrite_forward where id=' . $data_id);
        echo rex_view::info($this->i18n('forward_deleted'));
        rex_yrewrite_forward::generatePathFile();
    } elseif ($func == 'edit') {
        $xform->setHiddenField('data_id', $data_id);
        $xform->setActionField('db', ['rex_yrewrite_forward', 'id=' . $data_id]);
        $xform->setObjectparams('main_id', $data_id);
        $xform->setObjectparams('main_where', "id=$data_id");
        $xform->setObjectparams('getdata', true);
        $xform->setObjectparams('submit_btn_label', rex_i18n::msg('save'));
        $form = $xform->getForm();

        if ($xform->objparams['actions_executed']) {
            echo rex_view::info($this->i18n('forward_updated'));
            rex_yrewrite_forward::generatePathFile();
        } else {
            $showlist = false;
            echo '<div class="rex-area">
                            <h3 class="rex-hl2">' . $this->i18n('forward_edit') . '</h3>
                            <div class="rex-area-content">';
            echo $form;
            echo '</div></div>';
        }
    } elseif ($func == 'add') {
        $xform->setActionField('db', ['rex_yrewrite_forward']);
        $xform->setObjectparams('submit_btn_label', rex_i18n::msg('add'));
        $form = $xform->getForm();

        if ($xform->objparams['actions_executed']) {
            echo rex_view::info($this->i18n('forward_added'));
            rex_yrewrite_forward::generatePathFile();
        } else {
            $showlist = false;
            echo '<div class="rex-area">
                            <h3 class="rex-hl2">' . $this->i18n('forward_add') . '</h3>
                            <div class="rex-area-content">';
            echo $form;
            echo '</div></div>';
        }
    }
}

if ($showlist) {
    $sql = 'SELECT * FROM rex_yrewrite_forward';

    $list = rex_list::factory($sql, 100);
    $list->setColumnFormat('id', 'Id');
    $list->addParam('page', 'yrewrite/forward');

    $header = '<a class="rex-i-element rex-i-generic-add" href="' . $list->getUrl(['func' => 'add']) . '"><span class="rex-i-element-text">' . $this->i18n('add_domain') . '</span></a>';
    $list->addColumn($header, '###id###', 0, ['<th class="rex-icon">###VALUE###</th>', '<td class="rex-small">###VALUE###</td>']);

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
    $list->setColumnLayout('status', ['<th>###VALUE###</th>', '<td style="text-align:left;">###VALUE###</td>']);
    $list->setColumnFormat('status', 'custom',
        create_function(
            '$params',
            'global $I18N;
$list = $params["list"];
if ($list->getValue("status") == 1)
$str = "<span class=\"rex-online\">".$this->i18n("forward_active")."</span>";
else
$str = "<span class=\"rex-offline\">".$this->i18n("forward_inactive")."</span>";
return $str;'
        )
    );

    $list->addColumn(rex_i18n::msg('delete'), rex_i18n::msg('delete'));
    $list->setColumnParams(rex_i18n::msg('delete'), ['data_id' => '###id###', 'func' => 'delete']);
    $list->addLinkAttribute(rex_i18n::msg('delete'), 'onclick', 'return confirm(\' id=###id### ' . rex_i18n::msg('delete') . ' ?\')');

    $list->addColumn(rex_i18n::msg('edit'), rex_i18n::msg('edit'));
    $list->setColumnParams(rex_i18n::msg('edit'), ['data_id' => '###id###', 'func' => 'edit', 'start' => rex_request('start', 'string')]);

    echo $list->get();
}
