<?php

/**
 * YREWRITE Addon
 * @author jan.kristinus@yakamara.de
 * @package redaxo4.5
 */
 
$article_id = $params['article_id'];
$clang = $params['clang'];
$ctype = rex_request('ctype');
$yrewrite_url = stripslashes(rex_request('yrewrite_url'));
$domain = rex_yrewrite::getDomainByArticleId($article_id);

$sql = rex_sql::factory();
$data = $sql->getArray('SELECT * FROM '. $REX['TABLE_PREFIX'] .'article WHERE id=' . $article_id . ' AND clang=' . $clang);
$data = $data[0];

if (rex_post('save', 'boolean') == 1) {

  if(substr($yrewrite_url,0,1) == "/" or substr($yrewrite_url,-1) == "/") {
  
    echo rex_warning('Bitte in der URL kein / am Anfang und am Ende');
  
  } else if (strlen($yrewrite_url) > 250) {
  
    echo rex_warning('Die URL darf nicht länger als 250 Zeichen sein');

  } else if (!preg_match('/^[%_\.+\-\/a-zA-Z0-9]+$/', $yrewrite_url)) {
  
    echo rex_warning('Die URL ist nicht korrekt. Schreibweise beachten. Nur a-z 0-9 -');

  } else if ( ($aid = rex_yrewrite::getArticleIdByUrl($domain,$yrewrite_url)) && $aid !=  $article_id) {

    echo rex_warning('Diese URL existiert bereits');

  } else {
  
    $sql = rex_sql::factory();
    $sql->setTable($REX['TABLE_PREFIX'] . "article");
    // $sql->debugsql = 1;
    $sql->setWhere("id=" . $article_id . " AND clang=" . $clang);
    $sql->setValue('yrewrite_url', $yrewrite_url);
    if ($sql->update()) {
    	
    	rex_yrewrite::generatePathFile(array(
    	  'id' => $article_id,
    	  'clang' => $clang,
    	  'extension_point' => 'ART_UPDATED'
    	));
    	
    	echo rex_info('Url wurde aktualisiert');

    }
  
  }

} else {

  $yrewrite_url = $data["yrewrite_url"];
}

echo '
<div class="rex-content-body" id="yrewrite-contentpage">
	<div class="rex-content-body-2">
		<div class="rex-form" id="rex-form-content-metamode">
			<form action="index.php" method="post" enctype="multipart/form-data" id="yrewrite-form" name="yrewrite-form">
				<input type="hidden" name="page" value="content" />
				<input type="hidden" name="article_id" value="' . $article_id . '" />
				<input type="hidden" name="mode" value="yrewrite" />
				<input type="hidden" name="save" value="1" />
				<input type="hidden" name="clang" value="' . $clang . '" />
				<input type="hidden" name="ctype" value="' . $ctype . '" />

				<fieldset class="rex-form-col-1">
				  <legend>'.$I18N->msg("yrewrite_rewriter").'</legend>
				  
				  <div class="rex-form-wrapper">

    				<div class="rex-form-row"><p class="rex-form-text" style="margin-bottom: -3px;">
    					<label for="custom-url">'.$I18N->msg("yrewrite_customurl").'</label>
    					<input type="text" value="' . htmlspecialchars($yrewrite_url) . '" name="yrewrite_url" id="custom-url" class="rex-form-text">
    					</p>
    
    					<div style="display: inline-block;margin-left: 158px; margin-top: 12px; line-height: 25px;	margin-top: 10px;" id="custom-url-preview"></div>
    				</div>

      			<div class="rex-form-row">
      				<p class="rex-form-col-a rex-form-submit">
      					<input type="submit" value="'.$I18N->msg("yrewrite_update").'" name="save" class="rex-form-submit">
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

	jQuery('#custom-url').keyup(function() {
		updateCustomUrlPreview();
	});

	updateCustomUrlPreview();
	
});

function updateCustomUrlPreview() {
	var base = 'http[s]://<?php echo $domain; ?>/';
	var autoUrl = '<?php echo rex_getUrl($REX["ARTICLE_ID"], $REX["CUR_CLANG"]); ?>';
	var customUrl = jQuery('#custom-url').val();
	var curUrl = '';

	if (customUrl !== '') {
		curUrl = base + customUrl;
	} else {
		curUrl = base + autoUrl;
	}

	jQuery('#custom-url-preview').html(curUrl);
}

</script>

