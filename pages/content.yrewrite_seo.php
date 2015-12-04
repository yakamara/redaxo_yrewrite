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

$article_id = $params['article_id'];
$clang = $params['clang'];
$ctype = $params['ctype'];


$select_priority = [];
$select_priority[] = rex_i18n::msg('yrewrite_priority_auto').'=';
foreach (rex_yrewrite_seo::$priority as $priority) {
    $select_priority[] = rex_i18n::msg('yrewrite_priority_'.str_replace('.', '_', $priority)).'='.$priority;
}

$select_changefreq = [];
foreach (rex_yrewrite_seo::$changefreq as $changefreq) {
    $select_changefreq[] = rex_i18n::msg('yrewrite_changefreq_'.$changefreq).'='.$changefreq;
}

$index_setting = [];
$index_setting[] = rex_i18n::msg('yrewrite_index_status').'=0';
$index_setting[] = rex_i18n::msg('yrewrite_index_index').'=1';
$index_setting[] = rex_i18n::msg('yrewrite_index_noindex').'=-1';

$yform = new rex_yform();
$yform->setObjectparams('form_action', rex_url::backendController(['page' => 'content/edit', 'article_id' => $article_id, 'clang' => $clang, 'ctype' => $ctype], false));
$yform->setObjectparams('form_id', 'yrewrite-seo');
$yform->setObjectparams('form_name', 'yrewrite-seo');
$yform->setHiddenField('save', '1');

$yform->setObjectparams('form_showformafterupdate', 1);

$yform->setObjectparams('main_table', rex::getTable('article'));
$yform->setObjectparams('main_id', $article_id);
$yform->setObjectparams('main_where', 'id='.$article_id.' and clang_id='.$clang);
$yform->setObjectparams('getdata', true);

$yform->setValueField('text', ['yrewrite_title', rex_i18n::msg('yrewrite_seotitle')]);
$yform->setValueField('textarea', ['yrewrite_description', rex_i18n::msg('yrewrite_seodescription'),'','','short']);

$yform->setValueField('select', ['yrewrite_changefreq', rex_i18n::msg('yrewrite_changefreq'), implode(',', $select_changefreq), '', rex_yrewrite_seo::$changefreq_default]);
$yform->setValueField('select', ['yrewrite_priority', rex_i18n::msg('yrewrite_priority'), implode(',', $select_priority), '', rex_yrewrite_seo::$priority_default]);

$yform->setValueField('select', ['yrewrite_index', rex_i18n::msg('yrewrite_index'), implode(',', $index_setting), '', rex_yrewrite_seo::$index_setting_default]);

$yform->setActionField('db', [rex::getTable('article'), 'id=' . $article_id.' and clang_id='.$clang]);
$yform->setObjectparams('submit_btn_label', rex_i18n::msg('yrewrite_update'));
$form = $yform->getForm();

if ($yform->objparams['actions_executed']) {
    $form = rex_view::success(rex_i18n::msg('yrewrite_seoupdated')) . $form;
    rex_article_cache::delete($article_id, $clang);
} else {
}

$form = '<section id="rex-js-page-main-meta-yerwrite" data-pjax-container="#rex-js-page-main-meta-yerwrite" data-pjax-no-history="1">'.$form.'</section>';

return $form;


$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', rex_i18n::msg('yrewrite_rewriter_seo'));
$fragment->setVar('body', $form, false);
return $fragment->parse('core/page/section.php');


