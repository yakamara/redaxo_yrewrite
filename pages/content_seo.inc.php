<?php

/**
 * YREWRITE Addon
 * @author jan.kristinus@yakamara.de
 * @package redaxo4.5
 */

$article_id = $params['article_id'];
$clang = $params['clang'];
$ctype = rex_request('ctype');

$yrewrite_title = rex_request('yrewrite_title','string');
$yrewrite_description = rex_request('yrewrite_description','string');
$yrewrite_keywords = rex_request('yrewrite_keywords','string');
$yrewrite_priority = rex_request('yrewrite_priority','string');
$yrewrite_changefreq = rex_request('yrewrite_changefreq','string');

$sel_priority = new rex_select();
$sel_priority->setSize(1);
$sel_priority->setName('yrewrite_priority');

foreach(rex_yrewrite_seo::$priority as $priority) {
    $sel_priority->addOption($I18N->msg("yrewrite_priority_".str_replace(".","_",$priority)),$priority);
}

$sel_changefreq = new rex_select();
$sel_changefreq->setSize(1);
$sel_changefreq->setName('yrewrite_changefreq');
foreach(rex_yrewrite_seo::$changefreq as $changefreq) {
    $sel_changefreq->addOption($I18N->msg('yrewrite_changefreq_'.$changefreq),$changefreq);
}


$sql = rex_sql::factory();
// $sql->debugsql = 1;
$data = $sql->getArray('SELECT * FROM ' . $REX['TABLE_PREFIX'] . 'article WHERE id=' . $article_id . ' AND clang=' . $clang);
$data = $data[0];

if (rex_post('save', 'boolean') == 1) {

    $sql = rex_sql::factory();
    $sql->setTable($REX['TABLE_PREFIX'] . 'article');
    // $sql->debugsql = 1;
    $sql->setWhere('id=' . $article_id . ' AND clang=' . $clang);
    $sql->setValue('yrewrite_title', $yrewrite_title);
    $sql->setValue('yrewrite_description', $yrewrite_description);
    $sql->setValue('yrewrite_keywords', $yrewrite_keywords);
    $sql->setValue('yrewrite_priority', $yrewrite_priority);
    $sql->setValue('yrewrite_changefreq', $yrewrite_changefreq);
    if ($sql->update()) {
        echo rex_info($I18N->msg("yrewrite_seoupdated") );
        rex_deleteCacheArticle($article_id, $clang);
    } else {
        echo rex_warning($I18N->msg("yrewrite_seoupdate_failed") );
    }

} else {

    $yrewrite_title = $data['yrewrite_title'];
    $yrewrite_description = $data['yrewrite_description'];
    $yrewrite_keywords = $data['yrewrite_keywords'];
    $yrewrite_priority = $data['yrewrite_priority'];
    $yrewrite_changefreq = $data['yrewrite_changefreq'];

    if ($yrewrite_changefreq == "") {
        $yrewrite_changefreq = rex_yrewrite_seo::$changefreq_default;
    }

    if ($yrewrite_priority == "") {
        $yrewrite_priority = rex_yrewrite_seo::$priority_default;
    }

}

$sel_priority->setSelected($yrewrite_priority);
$sel_changefreq->setSelected($yrewrite_changefreq);


  echo '
<div class="rex-content-body" id="yrewrite-contentpage">
    <div class="rex-content-body-2">
        <div class="rex-form" id="rex-form-content-metamode">
            <form action="index.php" method="post" enctype="multipart/form-data" id="yrewrite-form" name="yrewrite-form">
                <input type="hidden" name="page" value="content" />
                <input type="hidden" name="article_id" value="' . $article_id . '" />
                <input type="hidden" name="mode" value="yrewrite_seo" />
                <input type="hidden" name="save" value="1" />
                <input type="hidden" name="clang" value="' . $clang . '" />
                <input type="hidden" name="ctype" value="' . $ctype . '" />

                <fieldset class="rex-form-col-1">
                  <legend>' . $I18N->msg('yrewrite_rewriter') . '</legend>

                  <div class="rex-form-wrapper">

                        <div class="rex-form-row">
                            <p class="rex-form-text" style="margin-bottom: -3px;">
                            <label for="custom-seotitle">' . $I18N->msg('yrewrite_seotitle') . '</label>
                            <input type="text" value="' . htmlspecialchars($yrewrite_title) . '" name="yrewrite_title" id="custom-seotitle" class="rex-form-text">
                            </p>
                        </div>

                        <div class="rex-form-row">
                            <p class="rex-form-textarea" style="margin-bottom: -3px;">
                            <label for="custom-seodescription">' . $I18N->msg('yrewrite_seodescription') . '</label>
                            <textarea rows="5" cols="50" name="yrewrite_description" id="custom-seodescription" class="rex-form-textarea">' . htmlspecialchars($yrewrite_description) . '</textarea>
                            </p>
                        </div>

                        <div class="rex-form-row">
                            <p class="rex-form-textarea" style="margin-bottom: -3px;">
                            <label for="custom-seokeywords">' . $I18N->msg('yrewrite_seokeywords') . '</label>
                            <textarea rows="5" cols="50" name="yrewrite_keywords" id="custom-seokeywords" class="rex-form-textarea">' . htmlspecialchars($yrewrite_keywords) . '</textarea>
                            </p>
                        </div>

                        <div class="rex-form-row">
                            <p class="rex-form-text" style="margin-bottom: -3px;">
                            <label for="custom-url">' . $I18N->msg('yrewrite_changefreq') . '</label>
                            '.$sel_changefreq->get().'
                            </p>
                         </div>

                        <div class="rex-form-row"><p class="rex-form-text" style="margin-bottom: -3px;">
                            <label for="custom-url">' . $I18N->msg('yrewrite_priority') . '</label>
                            '.$sel_priority->get().'
                            </p>
                        </div>

                        <div class="rex-form-row">
                            <p class="rex-form-col-a rex-form-submit">
                                <input type="submit" value="' . $I18N->msg('update') . '" name="save" class="rex-form-submit">
                                <br/><br/>
                            </p>
                        </div>
                        <div class="rex-clearer"></div>
              </div>

          </fieldset>

            </form>
        </div>
    </div>
</div>';
  ?>
  <script type="text/javascript">
    jQuery(document).ready(function() {

    });

  </script> <?php

