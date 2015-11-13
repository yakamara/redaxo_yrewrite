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
    $xform->setHiddenField('page', 'yrewrite/domains');
    $xform->setHiddenField('func', $func);
    $xform->setHiddenField('save', '1');

    $xform->setObjectparams('main_table', 'rex_yrewrite_domain');

    $xform->setValueField('text', ['domain', $this->i18n('domain_info')]);
    $xform->setValidateField('empty', ['domain', $this->i18n('no_domain_defined')]);
    $xform->setValidateField('unique', ['domain', $this->i18n('domain_already_defined')]);

    $xform->setValueField('be_link', ['mount_id', $this->i18n('mount_id')]);

    $xform->setValueField('be_link', ['start_id', $this->i18n('start_id')]);
    $xform->setValidateField('empty', ['start_id', $this->i18n('no_start_id_defined')]);

    $xform->setValueField('be_link', ['notfound_id', $this->i18n('notfound_id')]);
    $xform->setValidateField('empty', ['notfound_id', $this->i18n('no_not_found_id_defined')]);

    if (rex_clang::count() == 0) {
        $xform->setValueField('hidden', ['clangs', '']);
        $xform->setValueField('hidden', ['clang_start', '']);
    } else {
        // TODO:
        // - checkbox (alle sprachen)
        //  - multiple oder checkbox liste
        //   - wenn mehrere angeklickt -> clang_start auswahl mit genau diesen sprachen

        $xform->setValueField('select_sql', ['clangs', $this->i18n('clangs'), 'select id,name from rex_clang', '', 1, 0, '', 1, rex_clang::count()]);
        $xform->setValueField('select_sql', ['clang_start', $this->i18n('clang_start'), 'select id,name from rex_clang order by id']);
    }

    function rex_yrewrite_domaincheck($field, $value, $xform)
    {
        $sql = 'select '.$field.' from '.$xform->objparams['main_table'].' where '.$field.'="'.mysql_real_escape_string($value).'" and alias_domain="" AND !('.$xform->objparams['main_where'].')';
        $a = rex_sql::factory();
        $result = $a->getArray($sql);
        if (count($result) > 0) {
            return true;
        }
        return false;
    }

    $xform->setValueField('fieldset', ['seo',$this->i18n('rewriter_seo')]);

    $xform->setValueField('text', ['title_scheme', $this->i18n('domain_title_scheme'),rex_yrewrite_seo::$title_scheme_default]);
    $xform->setValueField('textarea', ['description', $this->i18n('domain_description'),'','','short']);
    $xform->setValueField('textarea', ['robots', $this->i18n('domain_robots'),rex_yrewrite_seo::$robots_default,'','short']);

    ?>
<script>
  jQuery(document).ready(function () {
      jQuery("#xform-formular-title_scheme").append('<span style="display:block; margin-left:230px; font-size:10px"><?php echo $this->i18n('domain_title_scheme_info');
    ?></span>');
      jQuery("#xform-formular-description").append('<span style="display:block; margin-left:230px; font-size:10px;"></span>');
      jQuery("#xform-formular-description textarea").bind ("change input keyup keydown keypress mouseup mousedown cut copy paste",function (e) {
          var v = jQuery(this).val().replace(/(\r\n|\n|\r)/gm, "").length;
          jQuery("#xform-formular-description").find('span').html( v + ' <?php echo $this->i18n('domain_description_info');
    ?>');
      return true;
      }).trigger("keydown");
  });
</script><?php

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
    $sql = 'SELECT * FROM rex_yrewrite_domain where alias_domain = ""';

    $list = rex_list::factory($sql, 100);
    $list->setColumnFormat('id', 'Id');
    $list->addParam('page', 'yrewrite/domains');

    $header = '<a class="rex-i-element rex-i-generic-add" href="' . $list->getUrl(['func' => 'add']) . '"><span class="rex-i-element-text">' . $this->i18n('add_domain') . '</span></a>';
    $list->addColumn($header, '###id###', 0, ['<th class="rex-icon">###VALUE###</th>', '<td class="rex-small">###VALUE###</td>']);

    $list->setColumnParams('id', ['data_id' => '###id###', 'func' => 'edit']);
    $list->setColumnSortable('id');

    $list->removeColumn('id');

    $list->setColumnLabel('domain', $this->i18n('domain'));
    $list->setColumnLabel('mount_id', $this->i18n('mount_id'));
    $list->setColumnLabel('start_id', $this->i18n('start_id'));
    $list->setColumnLabel('notfound_id', $this->i18n('notfound_id'));

    if (rex_clang::count() > 0) {
        $list->setColumnLabel('clangs', $this->i18n('clangs'));
        $list->setColumnFormat('clangs', 'custom', function ($params) {
            $clangs = $params['subject'];
            if ($clangs == '') {
                $return = $this->i18n('alllangs');
            } else {
                $return = [];
                foreach (explode(',', $clangs) as $clang) {
                    $return[] = rex_clang::get($clang)->getName();
                }
                if (count($return) > 1) {
                    $return = implode(',', $return) . '<br />'.$this->i18n('clang_start').': '.rex_clang::get($params['list']->sql->getValue('clang_start'))->getName();
                } else {
                    $return = implode(',', $return);
                }
            }
            return $return;
        });

        $list->removeColumn('clang_start');
        /*
        function rex_yrewrite_list_clang_start($params)
        {
            $clangs = $params['subject'];
            if ($clangs == "") {
                return $this->i18n('alllangs');
            } else {
                $return = array();
                foreach(explode(",",$clangs) as $clang) {
                  $return[] = rex_clang::get($clang)->getName();
                }
                return implode(",", $return);
            }
        }
        $list->setColumnLabel('clang_start', $this->i18n('clang_start'));
        $list->setColumnFormat('clangs', 'custom', 'rex_yrewrite_list_clang_start');
        */
    }

    $list->addColumn(rex_i18n::msg('delete'), rex_i18n::msg('delete'));
    $list->setColumnParams(rex_i18n::msg('delete'), ['data_id' => '###id###', 'func' => 'delete']);
    $list->addLinkAttribute(rex_i18n::msg('delete'), 'onclick', 'return confirm(\' id=###id### ' . rex_i18n::msg('delete') . ' ?\')');

    $list->addColumn(rex_i18n::msg('edit'), rex_i18n::msg('edit'));
    $list->setColumnParams(rex_i18n::msg('edit'), ['data_id' => '###id###', 'func' => 'edit', 'start' => rex_request('start', 'string')]);

    $showArticle = function ($params) {
        $id = $params['list']->getValue($params['field']);
        if ($id == 0) {
            return $this->i18n('root');
        } else {
            if (($article = rex_article::get($id))) {
                if ($article->isStartArticle()) {
                    $link = 'index.php?page=structure&category_id='.$id.'&clang=0';
                } else {
                    $link = 'index.php?page=content&article_id='.$id.'&mode=edit&clang=0';
                }
                return $article->getName().' [<a href="'.$link.'">'.$id.'</a>]';
            }
        }
        return '['.$id.']';
    };

    $list->setColumnFormat('mount_id', 'custom', $showArticle, []);
    $list->setColumnFormat('start_id', 'custom', $showArticle, []);
    $list->setColumnFormat('notfound_id', 'custom', $showArticle, []);

    $list->removeColumn('clang');
    $list->removeColumn('alias_domain');
    $list->removeColumn('robots');
    $list->removeColumn('title_scheme');
    $list->removeColumn('description');

    echo $list->get();
}
