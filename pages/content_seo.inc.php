<?php

/**
 * YREWRITE Addon
 * @author jan.kristinus@yakamara.de
 * @package redaxo4.5
 */

$article_id = $params['article_id'];
$clang = $params['clang'];
$ctype = rex_request('ctype');

$select_priority = array();
foreach(rex_yrewrite_seo::$priority as $priority) {
  $select_priority[] = $I18N->msg("yrewrite_priority_".str_replace(".","_",$priority)).'='.$priority;
}

$select_changefreq = array();
foreach(rex_yrewrite_seo::$changefreq as $changefreq) {
  $select_changefreq[] = $I18N->msg("yrewrite_changefreq_".$changefreq).'='.$changefreq;

}

$xform = new rex_xform;
// $xform->setDebug(TRUE);
$xform->setHiddenField('page', 'content');
$xform->setHiddenField('mode', 'yrewrite_seo');
$xform->setHiddenField('save', '1');
$xform->setHiddenField('article_id', $article_id);
$xform->setHiddenField('clang', $clang);
$xform->setHiddenField('ctype', $ctype);

$xform->setObjectparams('form_showformafterupdate', 1);

$xform->setObjectparams('main_table', $REX['TABLE_PREFIX'] . 'article');
$xform->setObjectparams('main_id', $article_id);
$xform->setObjectparams('main_where', 'id='.$article_id.' and clang='.$clang);
$xform->setObjectparams('getdata', true);

$xform->setValueField('text', array('yrewrite_title', $I18N->msg('yrewrite_seotitle')));
$xform->setValueField('textarea', array('yrewrite_description', $I18N->msg('yrewrite_seodescription'),'','','short'));

$xform->setValueField('select', array('yrewrite_changefreq', $I18N->msg('yrewrite_changefreq'), implode(",",$select_changefreq), '', rex_yrewrite_seo::$changefreq_default));
$xform->setValueField('select', array('yrewrite_priority', $I18N->msg('yrewrite_priority'), implode(",",$select_priority), '', rex_yrewrite_seo::$priority_default));

$xform->setActionField('db', array($REX['TABLE_PREFIX'] . 'article', 'id=' . $article_id));
$xform->setObjectparams('submit_btn_label', $I18N->msg('update'));
$form = $xform->getForm();

if ($xform->objparams['actions_executed']) {

  echo rex_info($I18N->msg("yrewrite_seoupdated") );
  rex_deleteCacheArticle($article_id, $clang);

} else {

}

echo '<div class="clearer"></div>
<div class="rex-addon-output" >
<h3 class="rex-hl2">' . $I18N->msg('yrewrite_rewriter_seo') . '</h3>
<div class="rex-addon-content" >';

echo $form;

echo '</div></div>';
