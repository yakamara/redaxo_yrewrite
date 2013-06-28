<?php

$showlist = true;
$data_id = rex_request('data_id', 'int', 0);
$func = rex_request('func', 'string');

// $I18N->msg("yrewrite")
// <div class="rex-addon-output"><h2 class="rex-hl2">Folgende Anfragen stehen an</h2><!-- <div class="rex-addon-content"> </div> --></div>

/* TODO:

// VALIDATES bei den domains
// - es muss mount_id, start_id und notfound_id
// - Domain ohne http://

*/


if ($func != '') {

    $xform = new rex_xform;
    // $xform->setDebug(TRUE);
    $xform->setHiddenField('page', 'yrewrite');
    $xform->setHiddenField('subpage', '');
    $xform->setHiddenField('func', $func);
    $xform->setHiddenField('save', '1');

    $xform->setObjectparams('main_table', 'rex_yrewrite_domain');

    $xform->setValueField('text', array('domain', $I18N->msg('yrewrite_domain_info')));
    $xform->setValueField('be_link', array('mount_id', $I18N->msg('yrewrite_mount_id')));
    $xform->setValueField('be_link', array('start_id', $I18N->msg('yrewrite_start_id')));
    $xform->setValueField('be_link', array('notfound_id', $I18N->msg('yrewrite_notfound_id')));
    // $xform->setValueField("text",array("clang","clang"));

    $xform->setValidateField('unique', array('domain', $I18N->msg('yrewrite_domain_already_defined')));
    $xform->setValidateField('empty', array('domain', $I18N->msg('yrewrite_no_domain_defined')));
    $xform->setValidateField('empty', array('mount_id', $I18N->msg('yrewrite_no_mount_id_defined')));
    $xform->setValidateField('empty', array('start_id', $I18N->msg('yrewrite_no_start_id_defined')));
    $xform->setValidateField('empty', array('notfound_id', $I18N->msg('yrewrite_no_not_found_id_defined')));

    if ($func == 'delete') {

        $d = rex_sql::factory();
        $d->setQuery('delete from rex_yrewrite_domain where id=' . $data_id);
        echo rex_info($I18N->msg('yrewrite_domain_deleted'));
        $info = rex_generateAll();

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
            $info = rex_generateAll();
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
            $info = rex_generateAll();
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

    $sql = 'SELECT * FROM rex_yrewrite_domain where alias_domain = ""';

    $list = rex_list::factory($sql, 100);
    $list->setColumnFormat('id', 'Id');
    $list->addParam('page', 'yrewrite');
    $list->addParam('subpage', '');


    $header = '<a class="rex-i-element rex-i-generic-add" href="' . $list->getUrl(array('func' => 'add')) . '"><span class="rex-i-element-text">' . $I18N->msg('yrewrite_add_domain') . '</span></a>';
    $list->addColumn($header, '###id###', 0, array('<th class="rex-icon">###VALUE###</th>', '<td class="rex-small">###VALUE###</td>'));

    $list->setColumnParams('id', array('data_id' => '###id###', 'func' => 'edit' ));
    $list->setColumnSortable('id');

    $list->removeColumn('id');

    $list->setColumnLabel('domain', $I18N->msg('yrewrite_domain'));
    $list->setColumnLabel('mount_id', $I18N->msg('yrewrite_mount_id'));
    $list->setColumnLabel('start_id', $I18N->msg('yrewrite_start_id'));
    $list->setColumnLabel('notfound_id', $I18N->msg('yrewrite_notfound_id'));

        /*
        $list->setColumnFormat(
                        "mount_id",
                        'custom',
                            array('rex_xform_be_link', 'getListValue'),
                            array('field' => 'mount_id', 'fields' => $fields)
                        );*/

        /*
        $list->setColumnFormat(
                        $field["f1"],
                        'custom',
                        array('rex_xform_'.$field['type_name'], 'getListValue'),
                        array('field' => $field, 'fields' => $fields));



                if(method_exists('rex_xform_'.$field['type_name'],'getListValue')) {
                    $list->setColumnFormat(
                        $field["f1"],
                        'custom',
                        array('rex_xform_'.$field['type_name'], 'getListValue'),
                        array('field' => $field, 'fields' => $fields));
                }
            }

            if($field["type_id"] == "value") {
                if($field["list_hidden"] == 1) {
                    $list->removeColumn($field["f1"]);
                }else {
                    $list->setColumnSortable($field["f1"]);
                    $list->setColumnLabel($field["f1"],$field["f2"]);
                }
            }
        }

        $list->addColumn($I18N->msg('edit'),$I18N->msg('edit'));
        $list->setColumnParams($I18N->msg('edit'), array("data_id"=>"###id###","func"=>"edit","start"=>rex_request("start","string")));

        */

    $list->addColumn($I18N->msg('delete'), $I18N->msg('delete'));
    $list->setColumnParams($I18N->msg('delete'), array('data_id' => '###id###', 'func' => 'delete'));
    $list->addLinkAttribute($I18N->msg('delete'), 'onclick', 'return confirm(\' id=###id### ' . $I18N->msg('delete') . ' ?\')');

    $list->addColumn($I18N->msg('edit'), $I18N->msg('edit'));
    $list->setColumnParams($I18N->msg('edit'), array('data_id' => '###id###', 'func' => 'edit', 'start' => rex_request('start', 'string')));

    $list->removeColumn('clang');
    $list->removeColumn('alias_domain', 'alias_domain');

    echo $list->get();

}
