<?php

/**
 *
 * @author blumbeet - web.studio
 * @author Thomas Blum
 * @author http://www.blumbeet.com
 * @author mail[at]blumbeet[dot]com
 *
 */

$myself = 'url_control';
$addon  = $REX['ADDON'][$myself]['addon'];

$oid  = rex_request('oid', 'int');
$func = rex_request('func', 'string');
$echo = '';

if ($func == '') {

    $query = '  SELECT      `id`,
                            `url`,
                            `method`,
                            `method_parameters`,
                            `status`
                FROM        ' . $REX['TABLE_PREFIX'] . 'url_control_manager';

    $list = rex_list::factory($query, 30, 'url_control_manager');
//	$list->debug = true;
    $list->setNoRowsMessage($I18N->msg('b_no_results'));
    $list->setCaption($I18N->msg('b_tables'));
    $list->addTableAttribute('summary', $I18N->msg('b_tables'));

    //$list->addTableColumnGroup(array(40, '*', 150, 80, 80, '153'));

    $header = '<a class="rex-i-element rex-i-generic-add" href="'. $list->getUrl(array('func' => 'add')) .'"><span class="rex-i-element-text">'. $I18N->msg('b_add_entry', $I18N->msg('b_table')) .'</span></a>';
    $list->addColumn($header, '###id###', 0, array('<th class="rex-icon">###VALUE###</th>','<td class="rex-small">###VALUE###</td>'));

    $list->removeColumn('id');
    $list->removeColumn('status');
    $list->removeColumn('method');
    $list->removeColumn('method_parameters');

    $list->setColumnLabel('url', $I18N->msg('b_url_control_manager_requested_url'));

    $list->addColumn('target', '');
    $list->setColumnLabel('target', $I18N->msg('b_url_control_manager_target'));
    $list->setColumnFormat('target', 'custom',
        create_function(
            '$params',
            'global $I18N;
             $list = $params["list"];
             $params = unserialize($list->getValue("method_parameters"));
             $str = "";
             if ($list->getValue("method") == "target_url" && $params["target_url"]["url"] != "") {
                $str = "<a href=\"" . $params["target_url"]["url"] . "\">" . $params["target_url"]["url"] . "</a>";

             } elseif ($list->getValue("method") == "article" && $params["article"]["article_id"] > 0) {
                $a = OOArticle::getArticleById((int)$params["article"]["article_id"], $clang = (int)$params["article"]["clang"]);
                $str = $a->getValue("name");
                $str .= " [";
                $str .= "<a href=\"index.php?article_id=" . $a->getId() . "&amp;clang=" . $a->getClang() . "\">Backend</a>";
                $str .= " | ";
                $str .= "<a href=\"" .  $a->getUrl() . "\">Frontend</a>";
                $str .= "]";

             }
             return $str;
             '
        )
    );

    $list->addColumn('action', '');
    $list->setColumnLabel('action', $I18N->msg('b_action'));
    $list->setColumnFormat('action', 'custom',
        create_function(
            '$params',
            'global $I18N;
             $list = $params["list"];
             $params = unserialize($list->getValue("method_parameters"));

             $str = $I18N->msg("b_url_control_manager_action_redirect") . " :: " . $I18N->msg("b_url_control_manager_http_type_" .  $params["http_type"]["code"]);
             if ($list->getValue("method") == "article" && $params["article"]["action"] == "view") {
                $str = $I18N->msg("b_url_control_manager_action_view");
             }
             return $str;
             '
        )
    );


    $list->addColumn('status', '');
    $list->setColumnLabel('status', $I18N->msg('b_function'));
    $list->setColumnParams('status', array('func'=>'status', 'oid'=>'###id###'));
    $list->setColumnLayout('status', array('<th colspan="2">###VALUE###</th>','<td style="text-align:center;">###VALUE###</td>'));
    $list->setColumnFormat('status', 'custom',
        create_function(
            '$params',
            'global $I18N;
             $list = $params["list"];
             if ($list->getValue("status") == 1)
               $str = "<span class=\"rex-online\">".$I18N->msg("b_active")."</span>";
             else
               $str = "<span class=\"rex-offline\">".$I18N->msg("b_inactive")."</span>";
             return $str;'
        )
    );

    $list->addColumn($I18N->msg('b_function'), $I18N->msg('b_edit'), -1, array('','<td style="text-align:center;">###VALUE###</td>'));
    $list->setColumnParams($I18N->msg('b_function'), array('func' => 'edit', 'oid' => '###id###'));

    $echo = $list->get();

}


if ($func == 'add' || $func == 'edit') {

    $legend = $func == 'edit' ? $I18N->msg('b_edit') : $I18N->msg('b_add');

    $form = new rex_form($REX['TABLE_PREFIX'] . 'url_control_manager', $I18N->msg('b_url') . ' ' . $legend, 'id=' . $oid, 'post', false);
	//$form->debug = true;

    if($func == 'edit') {
        $form->addParam('oid', $oid);
    }


    $field =& $form->addSelectField('status');
    $field->setLabel($I18N->msg('b_status'));
    $select =& $field->getSelect();
    $select->setSize(1);
    $select->addOption($I18N->msg('b_active'), '1');
    $select->addOption($I18N->msg('b_inactive'), '0');

    $field =& $form->addTextField('url');
    $field->setLabel($I18N->msg('b_url_control_manager_requested_url'));
    $field->setNotice($I18N->msg('b_url_control_manager_requested_url_notice'));


    $field =& $form->addSelectField('method');
    $field->setLabel($I18N->msg('b_method'));
    $select_method_id = $field->getAttribute('id');
    $select =& $field->getSelect();
    $select->setSize(1);
    $select->addOption($I18N->msg('b_url_control_manager_legend_1'), 'article');
    $select->addOption($I18N->msg('b_url_control_manager_legend_2'), 'target_url');


    $form->addFieldset($I18N->msg('b_url_control_manager_method'));


    $fieldContainer =& $form->addContainerField('method_parameters');
    $fieldContainer->setAttribute('style', 'display: none');

    // Group -------------------------------------------------------------------
    $group = 'article';

    $type  = 'link';
    $name  = 'article_id';
    $value = '';
    $field =& $fieldContainer->addGroupedField($group, $type, $name, $value, $attributes = array());
    $field->setLabel($I18N->msg('b_article'));


    if (count($REX['CLANG']) >= 2) {
        $type  = 'select';
        $name  = 'clang';
        $value = '';
        $field =& $fieldContainer->addGroupedField($group, $type, $name, $value, $attributes = array());
        $field->setLabel($I18N->msg('b_language'));
        $field->setAttribute('style', 'width: 200px;');
        $clang_id = $field->getAttribute('id');
        $select =& $field->getSelect();
        $select->setSize(1);

        foreach ($REX['CLANG'] as $key => $val) {
            $select->addOption($val, $key);
        }
    } else {
        $type  = 'hidden';
        $name  = 'clang';
        $value = '0';
        $field =& $fieldContainer->addGroupedField($group, $type, $name, $value, $attributes = array());
        $clang_id = $field->getAttribute('id');
    }

    $type  = 'select';
    $name  = 'action';
    $value = '';
    $field =& $fieldContainer->addGroupedField($group, $type, $name, $value, $attributes = array());
    $field->setLabel($I18N->msg('b_url_control_manager_action'));
    $http_type_id_a = $field->getAttribute('id');
    $select =& $field->getSelect();
    $select->setSize(1);
    $select->addOption($I18N->msg('b_url_control_manager_action_view'), 'view');
    $select->addOption($I18N->msg('b_url_control_manager_action_redirect'), 'redirect');


    // Group -------------------------------------------------------------------
    $group = 'target_url';

    $type  = 'text';
    $name  = 'url';
    $value = '';
    $field =& $fieldContainer->addGroupedField($group, $type, $name, $value, $attributes = array());
    $field->setLabel($I18N->msg('b_url_control_manager_own_url'));
    $field->setNotice($I18N->msg('b_url_control_manager_own_url_notice'));
    $http_type_id_b = $field->getAttribute('id');


    // Group -------------------------------------------------------------------
    $group = 'http_type';
    $http_type_id = 'rex-http_type';

    $type  = 'select';
    $name  = 'code';
    $value = '';
    $field =& $fieldContainer->addGroupedField($group, $type, $name, $value, $attributes = array());
    $field->setLabel($I18N->msg('b_url_control_manager_http_type'));
    $field->setNotice($I18N->msg('b_url_control_manager_http_type_notice'));
    $select =& $field->getSelect();
    $select->setSize(1);
    $select->addOption($I18N->msg('b_url_control_manager_http_type_301'), '301');
    $select->addOption($I18N->msg('b_url_control_manager_http_type_303'), '303');
    $select->addOption($I18N->msg('b_url_control_manager_http_type_307'), '307');

    $echo = $form->get();

}

require_once $REX['INCLUDE_PATH'] . '/layout/top.php';
rex_title($addon . ' :: ' . $I18N->msg('b_url_control_manager_title'), $REX['ADDON']['pages'][$addon]);
echo $echo;
require_once $REX['INCLUDE_PATH'] . '/layout/bottom.php';

?>

<script type="text/javascript">

    jQuery(document).ready(function($) {

        var $currentShown = null;
        $('#<?php echo $select_method_id; ?>').change(function() {
            if($currentShown) {
                $currentShown.hide();
            }

            var $id = '#rex-'+ jQuery(this).val();
            $currentShown = $($id);
            $currentShown.show();
            $('#<?php echo $http_type_id_a; ?>').change();
        }).change();


        var $load = true;
        var $idHttpType = $('#<?php echo $http_type_id; ?>');

        if ($load) {
            $idHttpType.hide();
        }
        $('#<?php echo $http_type_id_a; ?>').change(function() {

            if ($(this).is(":visible")) {

                if($idHttpType) {
                    $idHttpType.hide();
                }

                var $value = jQuery(this).val();
                if ($value == 'redirect') {
                    $idHttpType.show();
                }
            }
        }).change();


        $('#<?php echo $http_type_id_b; ?>').keyup(function() {
            if ($(this).is(":visible")) {
                if($idHttpType) {
                    $idHttpType.hide();
                }

                var $value = jQuery(this).val();
                if ($value !== '') {
                    $idHttpType.show();
                }
            }
        }).keyup();

        var $idClang = $('#<?php echo $clang_id; ?>');
        if($idClang.is(':hidden')) {
            $($idClang).closest('.rex-form-row').hide();
        }
    });
</script>
