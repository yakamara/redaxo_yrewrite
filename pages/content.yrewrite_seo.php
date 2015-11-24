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

$content = '';

$addon = rex_addon::get('yrewrite');

$select_priority = [];
$select_priority[] = $addon->i18n('priority_auto').'=';
foreach (rex_yrewrite_seo::$priority as $priority) {
    $select_priority[] = $addon->i18n('priority_'.str_replace('.', '_', $priority)).'='.$priority;
}

$select_changefreq = [];
foreach (rex_yrewrite_seo::$changefreq as $changefreq) {
    $select_changefreq[] = $addon->i18n('changefreq_'.$changefreq).'='.$changefreq;
}

$index_setting = [];
$index_setting[] = $addon->i18n('index_status').'=0';
$index_setting[] = $addon->i18n('index_index').'=1';
$index_setting[] = $addon->i18n('index_noindex').'=-1';

$yform = new rex_yform();
$yform->setObjectparams('form_action', $context->getUrl());
$yform->setHiddenField('save', '1');

$yform->setObjectparams('form_showformafterupdate', 1);

$yform->setObjectparams('main_table', rex::getTable('article'));
$yform->setObjectparams('main_id', $article_id);
$yform->setObjectparams('main_where', 'id='.$article_id.' and clang_id='.$clang);
$yform->setObjectparams('getdata', true);

$yform->setValueField('text', ['yrewrite_title', $addon->i18n('seotitle')]);
$yform->setValueField('textarea', ['yrewrite_description', $addon->i18n('seodescription'),'','','short']);

$yform->setValueField('select', ['yrewrite_changefreq', $addon->i18n('changefreq'), implode(',', $select_changefreq), '', rex_yrewrite_seo::$changefreq_default]);
$yform->setValueField('select', ['yrewrite_priority', $addon->i18n('priority'), implode(',', $select_priority), '', rex_yrewrite_seo::$priority_default]);

$yform->setValueField('select', ['yrewrite_index', $addon->i18n('index'), implode(',', $index_setting), '', rex_yrewrite_seo::$index_setting_default]);

$yform->setActionField('db', [rex::getTable('article'), 'id=' . $article_id.' and clang_id='.$clang]);
$yform->setObjectparams('submit_btn_label', rex_i18n::msg('update'));
$form = $yform->getForm();

if ($yform->objparams['actions_executed']) {
    $form = rex_view::success($addon->i18n('seoupdated')) . $form;
    rex_article_cache::delete($article_id, $clang);
} else {
}

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('rewriter_seo'));
$fragment->setVar('body', $form, false);
return $fragment->parse('core/page/section.php');


