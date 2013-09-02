<?php

/**
 * YREWRITE Addon
 * @author jan.kristinus@yakamara.de
 * @package redaxo4.5
 */

$article_id = $params['article_id'];
$clang = $params['clang'];
$ctype = rex_request('ctype');

$yrewrite_seotitle = rex_request('yrewrite_seotitle','string');
$yrewrite_seodescription = rex_request('yrewrite_seodescription','string');
$yrewrite_seokeywords = rex_request('yrewrite_seokeywords','string');
$yrewrite_seopriority = rex_request('yrewrite_seopriority','string');
$yrewrite_seochangefreq = rex_request('yrewrite_seochangefreq','string');

$sel_priority = new rex_select();
$sel_priority->setSize(1);
$sel_priority->setName('yrewrite_seopriority');

foreach(rex_yrewrite_seo::$priority as $priority) {
  $sel_priority->addOption($I18N->msg("yrewrite_seopriority_".str_replace(".","_",$priority)),$priority);
}

$sel_changefreq = new rex_select();
$sel_changefreq->setSize(1);
$sel_changefreq->setName('yrewrite_seochangefreq');
foreach(rex_yrewrite_seo::$changefreq as $changefreq) {
  $sel_changefreq->addOption($I18N->msg('yrewrite_seochangefreq_'.$changefreq),$changefreq);
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
    $sql->setValue('yrewrite_seotitle', $yrewrite_seotitle);
    $sql->setValue('yrewrite_seodescription', $yrewrite_seodescription);
    $sql->setValue('yrewrite_seokeywords', $yrewrite_seokeywords);
    $sql->setValue('yrewrite_seopriority', $yrewrite_seopriority);
    $sql->setValue('yrewrite_seochangefreq', $yrewrite_seochangefreq);
    if ($sql->update()) {
        echo rex_info($I18N->msg("yrewrite_seoupdated") );
        rex_deleteCacheArticle($article_id, $clang);
    } else {
        echo rex_warning($I18N->msg("yrewrite_seoupdate_failed") );
    }

} else {

  $yrewrite_seotitle = $data['yrewrite_seotitle'];
  $yrewrite_seodescription = $data['yrewrite_seodescription'];
  $yrewrite_seokeywords = $data['yrewrite_seokeywords'];
  $yrewrite_seopriority = $data['yrewrite_seopriority'];
  $yrewrite_seochangefreq = $data['yrewrite_seochangefreq'];

  if($yrewrite_seochangefreq == "") {
    $yrewrite_seochangefreq = rex_yrewrite_seo::$changefreq_default;
  }
  if($yrewrite_seopriority == "") {
    $yrewrite_seopriority = rex_yrewrite_seo::$priority_default;
  }

}

$sel_priority->setSelected($yrewrite_seopriority);
$sel_changefreq->setSelected($yrewrite_seochangefreq);


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
                            <input type="text" value="' . htmlspecialchars($yrewrite_seotitle) . '" name="yrewrite_seotitle" id="custom-seotitle" class="rex-form-text">
                            </p>
                        </div>

                        <div class="rex-form-row">
                            <p class="rex-form-textarea" style="margin-bottom: -3px;">
                            <label for="custom-seodescription">' . $I18N->msg('yrewrite_seodescription') . '</label>
                            <textarea rows="5" cols="50" name="yrewrite_seodescription" id="custom-seodescription" class="rex-form-textarea">' . htmlspecialchars($yrewrite_seodescription) . '</textarea>
                            </p>
                        </div>

                        <div class="rex-form-row">
                            <p class="rex-form-textarea" style="margin-bottom: -3px;">
                            <label for="custom-seokeywords">' . $I18N->msg('yrewrite_seokeywords') . '</label>
                            <textarea rows="5" cols="50" name="yrewrite_seokeywords" id="custom-seokeywords" class="rex-form-textarea">' . htmlspecialchars($yrewrite_seokeywords) . '</textarea>
                            </p>
                        </div>

                        <div class="rex-form-row">
                            <p class="rex-form-text" style="margin-bottom: -3px;">
                            <label for="custom-url">' . $I18N->msg('yrewrite_seochangefreq') . '</label>
                            '.$sel_changefreq->get().'
                            </p>
                         </div>

                        <div class="rex-form-row"><p class="rex-form-text" style="margin-bottom: -3px;">
                            <label for="custom-url">' . $I18N->msg('yrewrite_seopriority') . '</label>
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

