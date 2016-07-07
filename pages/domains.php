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
    $yform->setHiddenField('page', 'yrewrite/domains');
    $yform->setHiddenField('func', $func);
    $yform->setHiddenField('save', '1');

    $yform->setObjectparams('main_table', rex::getTable('yrewrite_domain'));

    $yform->setValueField('text', ['domain', $this->i18n('domain'), 'notice' => '<small>'.$this->i18n('domain_info').'</small>']);
    $yform->setValidateField('empty', ['domain', $this->i18n('no_domain_defined')]);
    $yform->setValidateField('unique', ['domain', $this->i18n('domain_already_defined')]);

    $yform->setValueField('be_link', ['mount_id', $this->i18n('mount_id'), 'notice' => '<small>'.$this->i18n('mount_info').'</small>']);

    $yform->setValueField('be_link', ['start_id', $this->i18n('start_id'), 'notice' => '<small>'.$this->i18n('start_info').'</small>']);
    $yform->setValidateField('empty', ['start_id', $this->i18n('no_start_id_defined')]);

    $yform->setValueField('be_link', ['notfound_id', $this->i18n('notfound_id'), 'notice' => '<small>'.$this->i18n('notfound_info').'</small>']);
    $yform->setValidateField('empty', ['notfound_id', $this->i18n('no_not_found_id_defined')]);

    if (rex_clang::count() == 0) {
        $yform->setValueField('hidden', ['clangs', '']);
        $yform->setValueField('hidden', ['clang_start', '']);

    } else {
        $yform->setValueField('select_sql', ['clangs', $this->i18n('clangs'), 'select id,name from '.rex::getTable('clang'), '', 1, 0, '', 1, rex_clang::count(), 'notice' => '<small>'.$this->i18n('clangs_info').'</small>']);
        $yform->setValueField('select_sql', ['clang_start', $this->i18n('clang_start'), 'select id,name from '.rex::getTable('clang').' order by id', 'notice' => '<small>'.$this->i18n('clang_start_info').'</small>']);
    }

    function rex_yrewrite_domaincheck($field, $value, $yform)
    {
        $sql = 'select '.$field.' from '.$yform->objparams['main_table'].' where '.$field.'="'.mysql_real_escape_string($value).'" and alias_domain="" AND !('.$yform->objparams['main_where'].')';
        $a = rex_sql::factory();
        $result = $a->getArray($sql);
        if (count($result) > 0) {
            return true;
        }
        return false;
    }

    $yform->setValueField('fieldset', ['seo',$this->i18n('rewriter_seo')]);

    $yform->setValueField('text', ['title_scheme', $this->i18n('domain_title_scheme'),rex_yrewrite_seo::$title_scheme_default, 'notice' => '<small>'.$this->i18n('domain_title_scheme_info').'</small>'] );

    if ($func == 'delete') {
        $d = rex_sql::factory();
        $d->setQuery('delete from '.rex::getTable('yrewrite_domain').' where id=' . $data_id);
        echo rex_view::success($this->i18n('domain_deleted'));
        rex_yrewrite::deleteCache();

    } else if ($func == 'edit') {

        $yform->setValueField('textarea', ['robots', $this->i18n('domain_robots'),'','','short']);
        $yform->setHiddenField('data_id', $data_id);
        $yform->setActionField('db', [rex::getTable('yrewrite_domain'), 'id=' . $data_id]);
        $yform->setObjectparams('main_id', $data_id);
        $yform->setObjectparams('main_where', "id=$data_id");
        $yform->setObjectparams('getdata', true);
        $yform->setObjectparams('submit_btn_label', $this->i18n('save'));
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

    } elseif ($func == 'add') {

        $yform->setValueField('textarea', ['robots', $this->i18n('domain_robots'),rex_yrewrite_seo::$robots_default,'','short']);
        $yform->setActionField('db', [rex::getTable('yrewrite_domain')]);
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

    $sql = 'SELECT * FROM '.rex::getTable('yrewrite_domain').' where alias_domain = ""';

    $list = rex_list::factory($sql, 100);
    $list->setColumnFormat('id', 'Id');
    $list->addParam('page', 'yrewrite/domains');

    $tdIcon = '<i class="fa fa-sitemap"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '"' . rex::getAccesskey($this->i18n('add_domain'), 'add') . '><i class="rex-icon rex-icon-add"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'data_id' => '###id###']);

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
                    $return = implode(',', $return) . '<br />'.$this->i18n('clang_start').': '.rex_clang::get($params['list']->getValue('clang_start'))->getName();
                } else {
                    $return = implode(',', $return);
                }
            }
            return $return;
        });

        $list->removeColumn('clang_start');

    }

    $list->addColumn(rex_i18n::msg('function'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('function'), ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('function'), ['data_id' => '###id###', 'func' => 'edit', 'start' => rex_request('start', 'string')]);

    $list->addColumn(rex_i18n::msg('delete'), '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
    $list->setColumnLayout(rex_i18n::msg('delete'), ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('delete'), ['data_id' => '###id###', 'func' => 'delete']);
    $list->addLinkAttribute(rex_i18n::msg('delete'), 'onclick', 'return confirm(\' id=###id### ' . rex_i18n::msg('delete') . ' ?\')');

    $showArticle = function ($params) {
        $id = $params['list']->getValue($params['field']);
        if ($id == 0) {
            return $this->i18n('root');
        } else {
            if (($article = rex_article::get($id))) {
                if ($article->isStartArticle()) {
                    $link = 'index.php?page=structure&category_id='.$id.'&clang=1';
                } else {
                    $link = 'index.php?page=content&article_id='.$id.'&mode=edit&clang=1';
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

    $content = $list->get();

    $fragment = new rex_fragment();
    $fragment->setVar('title', $this->i18n('domains'));
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}
