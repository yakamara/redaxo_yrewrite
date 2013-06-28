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

rex_register_extension('REX_FORM_SAVED', 'url_generate::generatePathFile');

if ($func == '') {

    $query = '  SELECT      `id`,
                            `article_id`,
                            `clang`,
                            `url`,
                            `table`,
                            `table_parameters`
                FROM        ' . $REX['TABLE_PREFIX'] . 'url_control_generate';

    $list = rex_list::factory($query, 30, 'url_control_generate');
//    $list->debug = true;
    $list->setNoRowsMessage($I18N->msg('b_no_results'));
    $list->setCaption($I18N->msg('b_tables'));
    $list->addTableAttribute('summary', $I18N->msg('b_tables'));

    $list->addTableColumnGroup(array(40, '*', 150, 80, 80, '153'));

    $header = '<a class="rex-i-element rex-i-generic-add" href="' . $list->getUrl(array('func' => 'add')) . '"><span class="rex-i-element-text">' . $I18N->msg('b_add_entry', $I18N->msg('b_table')) . '</span></a>';
    $list->addColumn($header, '###id###', 0, array('<th class="rex-icon">###VALUE###</th>', '<td class="rex-small">###VALUE###</td>'));

    $list->removeColumn('id');
    $list->removeColumn('clang');
    $list->removeColumn('url');
    $list->removeColumn('table_parameters');

    $list->setColumnLabel('article_id', $I18N->msg('b_article'));
    $list->setColumnFormat('article_id', 'custom',
        create_function(
            '$params',
            'global $I18N;
             $list = $params["list"];
             $a = OOArticle::getArticleById($list->getValue("article_id"), $list->getValue("clang"));

             $str = $a->getValue("name");
             $str .= " [";
             $str .= "<a href=\"index.php?article_id=".$list->getValue("article_id")."&amp;clang=".$list->getValue("clang")."\">Backend</a>";
             $str .= " | ";
             $str .= "<a href=\"".rex_getUrl($list->getValue("article_id"), $list->getValue("clang"))."\">Frontend</a>";
             $str .= "]";
             return $str;'
        )
    );

    $list->setColumnLabel('table', $I18N->msg('b_table'));

    $list->addColumn('url', '');
    $list->setColumnLabel('url', $I18N->msg('b_url'));
    $list->setColumnFormat('url', 'custom',
        create_function(
            '$params',
            'global $I18N;
             $list = $params["list"];

             $params = unserialize($list->getValue("table_parameters"));
             return $params[$list->getValue("table")][$list->getValue("table")."_name"];'
        )
    );

    $list->addColumn('id', '');
    $list->setColumnLabel('id', $I18N->msg('b_id'));
    $list->setColumnFormat('id', 'custom',
        create_function(
            '$params',
            'global $I18N;
             $list = $params["list"];

             $params = unserialize($list->getValue("table_parameters"));
             return $params[$list->getValue("table")][$list->getValue("table")."_id"];'
        )
    );

    $list->addColumn($I18N->msg('b_function'), $I18N->msg('b_edit'));
    $list->setColumnParams($I18N->msg('b_function'), array('func' => 'edit', 'oid' => '###id###'));

    $echo = $list->get();

}


if ($func == 'add' || $func == 'edit') {

    $legend = $func == 'edit' ? $I18N->msg('b_edit') : $I18N->msg('b_add');

    $form = new rex_form($REX['TABLE_PREFIX'] . 'url_control_generate', $I18N->msg('b_table') . ' ' . $legend, 'id=' . $oid, 'post', false);
    //$form->debug = true;

    if ($func == 'edit') {
        $form->addParam('oid', $oid);
    }

    $field = & $form->addLinkmapField('article_id');
    $field->setLabel($I18N->msg('b_article'));


    if (count($REX['CLANG']) >= 2) {
        $field = & $form->addSelectField('clang');
        $field->setLabel($I18N->msg('b_language'));
        $field->setAttribute('style', 'width: 200px;');
        $select = & $field->getSelect();
        $select->setSize(1);

        foreach ($REX['CLANG'] as $key => $value) {
            $select->addOption($value, $key);
        }

    }


    $field = & $form->addSelectField('table');
    $field->setLabel($I18N->msg('b_table'));
    $field->setAttribute('onchange', 'url_generate_table(this);');
    $field->setAttribute('style', 'width: 200px;');
    $select = & $field->getSelect();
    $select->setSize(1);
    $select->addOption($I18N->msg('b_no_table_selected'), '');

    $fields = array();
    $tables = rex_sql::showTables();
    foreach ($tables as $table) {
        $select->addOption($table, $table);

        $columns = rex_sql::showColumns($table);
        foreach ($columns as $column) {
            $fields[$table][] = $column['name'];
        }
    }

    $table_id = $field->getAttribute('id');


    $fieldContainer = & $form->addContainerField('table_parameters');
    $fieldContainer->setAttribute('style', 'display: none');



    if (count($fields > 0)) {
        foreach ($fields as $table => $columns) {
            $group      = $table;
            $type       = 'select';
            $options    = $columns;

            $name       = $table . '_name';

            $f1 = & $fieldContainer->addGroupedField($group, $type, $name, $value, $attributes = array());
            $f1->setLabel($I18N->msg('b_url'));
            $f1->setAttribute('style', 'width: 200px;');
            $f1->setNotice($I18N->msg('b_url_control_generate_notice_name'));
            $select = & $f1->getSelect();
            $select->setSize(1);
            $select->addOptions($options, true);



            $name       = $table . '_id';

            $f2 = & $fieldContainer->addGroupedField($group, $type, $name, $value, $attributes = array());
            $f2->setLabel($I18N->msg('b_id'));
            $f2->setAttribute('style', 'width: 200px;');
            $f2->setNotice($I18N->msg('b_url_control_generate_notice_id'));
            $select = & $f2->getSelect();
            $select->setSize(1);
            $select->addOptions($options, true);

        }
    }

    $echo = $form->get();

}

require_once $REX['INCLUDE_PATH'] . '/layout/top.php';
rex_title($addon . ' :: ' . $I18N->msg('b_url_control_generate_title'), $REX['ADDON']['pages'][$addon]);
echo $echo;
require_once $REX['INCLUDE_PATH'] . '/layout/bottom.php';

?>

<script type="text/javascript">

    jQuery(document).ready(function($) {

        var $currentShown = null;
        $("#<?php echo $table_id; ?>").change(function() {
            if($currentShown) {
                $currentShown.hide();
            }

            var $table_id = "#rex-"+ jQuery(this).val();
            $currentShown = $($table_id);
            $currentShown.show();
        }).change();
    });

</script>
