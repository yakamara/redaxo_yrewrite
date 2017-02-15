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

$func = rex_request('func', 'string');

if ($func != '') {
    if ($func == 'htaccess') {
        rex_yrewrite::copyHtaccess();
        echo rex_view::success($this->i18n('htaccess_hasbeenset'));
    }
}

$content = '

            <h3>' . $this->i18n('htaccess_set') . '</h3>
            <p>' . rex_i18n::rawMsg('yrewrite_htaccess_info') . '</p>
            <p><a class="btn btn-primary" href="'.rex_url::currentBackendPage(['func' => 'htaccess']).'">' . $this->i18n('yrewrite_htaccess_set') . '</a></p>

            <h3>' . $this->i18n('info_headline') . '</h3>
            <p>' . rex_i18n::rawMsg('yrewrite_info_text') . '</p>


            <h3>' . $this->i18n('info_tipps') . '</h3>
            <p>' . rex_i18n::rawMsg('yrewrite_info_tipps_text') . '    
            
            
            <h3>' . $this->i18n('info_seo') . '</h3>
            <p>' . rex_i18n::rawMsg('yrewrite_info_seo_text') . '
            
            <br /><br />'.highlight_string('<?php
	  $seo = new rex_yrewrite_seo();
	  echo $seo->getTitleTag().PHP_EOL;
	  echo $seo->getDescriptionTag().PHP_EOL;
	  echo $seo->getRobotsTag().PHP_EOL;
	  echo $seo->getHreflangTags().PHP_EOL;
	  echo $seo->getCanonicalUrlTag().PHP_EOL;
?>', true).'
            </p>
            ';

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('setup'));
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

/**
 * Process and display visibility settings form
 */
echo yrewrite_seo_visibility::processFormPost();
echo yrewrite_seo_visibility::getForm();

$domains = [];

foreach (rex_yrewrite::getDomains() as $name => $domain) {
    if ($name != 'default') {
        $domains[] = '<tr><td><a href="'.$domain->getUrl().'">'.htmlspecialchars($name).'</a></td><td><a href="'.$domain->getUrl().'sitemap.xml">sitemap.xml</a></td><td><a href="'.$domain->getUrl().'robots.txt">robots.txt</a></td></tr>';
    }
}


$tables = '<table class="table table-hover">
            <tr>
                <th>Domain</th><th>Sitemap</th><th>robots.txt</th></tr>
            '.implode('', $domains).'
            </table>';

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('info_sitemaprobots'));
$fragment->setVar('content', $tables, false);
echo $fragment->parse('core/page/section.php');
