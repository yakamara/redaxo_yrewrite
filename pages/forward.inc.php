<?php

$showlist = true;
$data_id = rex_request('data_id', 'int', 0);
$func = rex_request('func', 'string');

rex_yrewrite_forward::init();

if ($func != '') {

    $xform = new rex_xform;
    // $xform->setDebug(TRUE);
    $xform->setHiddenField('page', 'yrewrite');
    $xform->setHiddenField('subpage', 'forward');
    $xform->setHiddenField('func', $func);
    $xform->setHiddenField('save', '1');

    $xform->setObjectparams('main_table', 'rex_yrewrite_forward');

    $xform->setValueField('select', array('status', $I18N->msg('yrewrite_forward_status'),''.$I18N->msg("yrewrite_forward_active").'=1,'.$I18N->msg("yrewrite_forward_inactive").'=0'));
    $xform->setValueField('select_sql', array('domain', $I18N->msg('yrewrite_domain') . '', 'select domain as id,domain as name from rex_yrewrite_domain where alias_domain = ""'));
    $xform->setValidateField('preg_match', array('url', '/^[%_\.+\-a-zA-Z0-9]+$/', $I18N->msg('yrewrite_warning_chars')));
    // $I18N->msg('yrewrite_warning_noslash')
    $xform->setValidateField('size_range', array('url', 1, 255, $I18N->msg('yrewrite_warning_nottolong')));
    $xform->setValidateField('empty', array('url', $I18N->msg('yrewrite_forward_enter_url')));
    $xform->setValueField('text', array('url', $I18N->msg('yrewrite_forward_url')));
    $xform->setValidateField('unique', array('domain,url', $I18N->msg('yrewrite_forward_domainurl_already_defined')));
    $xform->setValueField('select', array('movetype', $I18N->msg('yrewrite_forward_move_method'), $I18N->msg("yrewrite_forward_301").'=301,'.$I18N->msg("yrewrite_forward_303").'=303,'.$I18N->msg("yrewrite_forward_307").'=307','','303'));
    $xform->setValueField('select', array('type', $I18N->msg('yrewrite_forward_type'),''.$I18N->msg("yrewrite_forward_type_article").'=article,'.$I18N->msg("yrewrite_forward_type_extern").'=extern,'.$I18N->msg("yrewrite_forward_type_media").'=media'));

    $xform->setValueField('html', array('','<div id="rex-yrewrite-forward-article">'));
    $xform->setValueField('be_link', array('article_id', $I18N->msg('yrewrite_forward_article_id')));
    $xform->setValueField('select_sql', array('clang', $I18N->msg('yrewrite_forward_clang') . '', 'select id, name from rex_clang'));
    $xform->setValueField('html', array('', '</div>'));

    $xform->setValueField('html', array('','<div id="rex-yrewrite-forward-extern">'));
    $xform->setValueField('text', array('extern', $I18N->msg('yrewrite_forward_extern')));
    $xform->setValueField('html', array('', '</div>'));

    $xform->setValueField('html', array('','<div id="rex-yrewrite-forward-media">'));
    $xform->setValueField('be_mediapool', array('media', $I18N->msg('yrewrite_forward_media')));
    $xform->setValueField('html', array('', '</div>'));


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
        echo rex_info($I18N->msg('yrewrite_forward_deleted'));
        $info = rex_yrewrite_forward::generatePathFile();

    } elseif ($func == 'edit') {

        $xform->setHiddenField('data_id', $data_id);
        $xform->setActionField('db', array('rex_yrewrite_forward', 'id=' . $data_id));
        $xform->setObjectparams('main_id', $data_id);
        $xform->setObjectparams('main_where', "id=$data_id");
        $xform->setObjectparams('getdata', true);
        $xform->setObjectparams('submit_btn_label', $I18N->msg('save'));
        $form = $xform->getForm();

        if ($xform->objparams['actions_executed']) {
            echo rex_info($I18N->msg('yrewrite_forward_updated'));
            $info = rex_yrewrite_forward::generatePathFile();
            
        } else {
            $showlist = false;
            echo '<div class="rex-area">
                            <h3 class="rex-hl2">' . $I18N->msg('yrewrite_forward_edit') . '</h3>
                            <div class="rex-area-content">';
            echo $form;
            echo '</div></div>';
        }

    } elseif ($func == 'add') {

        $xform->setActionField('db', array('rex_yrewrite_forward'));
        $xform->setObjectparams('submit_btn_label', $I18N->msg('add'));
        $form = $xform->getForm();

        if ($xform->objparams['actions_executed']) {
            echo rex_info($I18N->msg('yrewrite_forward_added'));
            $info = rex_yrewrite_forward::generatePathFile();
        } else {
            $showlist = false;
            echo '<div class="rex-area">
                            <h3 class="rex-hl2">' . $I18N->msg('yrewrite_forward_add') . '</h3>
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
    $list->addParam('page', 'yrewrite');
    $list->addParam('subpage', 'forward');


    $header = '<a class="rex-i-element rex-i-generic-add" href="' . $list->getUrl(array('func' => 'add')) . '"><span class="rex-i-element-text">' . $I18N->msg('yrewrite_add_domain') . '</span></a>';
    $list->addColumn($header, '###id###', 0, array('<th class="rex-icon">###VALUE###</th>', '<td class="rex-small">###VALUE###</td>'));

    $list->setColumnParams('id', array('data_id' => '###id###', 'func' => 'edit' ));
    $list->setColumnSortable('id');

    $list->setColumnLabel('domain', $I18N->msg('yrewrite_domain'));
    $list->setColumnLabel('status', $I18N->msg('yrewrite_forward_status'));
    $list->setColumnLabel('url', $I18N->msg('yrewrite_forward_url'));
    $list->setColumnLabel('type', $I18N->msg('yrewrite_forward_type'));

    $list->removeColumn('id');
    $list->removeColumn('article_id');
    $list->removeColumn('clang');
    $list->removeColumn('extern');
    $list->removeColumn('media');
    $list->removeColumn('movetype');

    // $list->setColumnLabel('status', $I18N->msg('b_function'));
    $list->setColumnParams('status', array('func' => 'status', 'oid' => '###id###'));
    $list->setColumnLayout('status', array('<th>###VALUE###</th>', '<td style="text-align:left;">###VALUE###</td>'));
    $list->setColumnFormat('status', 'custom',
        create_function(
            '$params',
            'global $I18N;
$list = $params["list"];
if ($list->getValue("status") == 1)
$str = "<span class=\"rex-online\">".$I18N->msg("yrewrite_forward_active")."</span>";
else
$str = "<span class=\"rex-offline\">".$I18N->msg("yrewrite_forward_inactive")."</span>";
return $str;'
        )
    );

    $list->addColumn($I18N->msg('delete'), $I18N->msg('delete'));
    $list->setColumnParams($I18N->msg('delete'), array('data_id' => '###id###', 'func' => 'delete'));
    $list->addLinkAttribute($I18N->msg('delete'), 'onclick', 'return confirm(\' id=###id### ' . $I18N->msg('delete') . ' ?\')');

    $list->addColumn($I18N->msg('edit'), $I18N->msg('edit'));
    $list->setColumnParams($I18N->msg('edit'), array('data_id' => '###id###', 'func' => 'edit', 'start' => rex_request('start', 'string')));

    echo $list->get();

}
