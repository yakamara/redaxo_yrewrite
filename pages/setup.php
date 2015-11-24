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

            <h3>' . $this->i18n('info_seo') . '</h3>
            <p>' . rex_i18n::rawMsg('yrewrite_info_seo_text') . '

            <br /><br />'.highlight_string('<?php
  $seo = new rex_yrewrite_seo();
  echo $seo->getTitleTag();
  echo $seo->getDescriptionTag();
  echo $seo->getRobotsTag();

?>', true).'
            </p>
            ';

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('setup'));
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');




$domains = [];

foreach (rex_yrewrite::$domainsByName as $name => $val) {
    if ($name != 'undefined') {
        $domains[] = '<tr><td><a href="http://'.$name.'">'.htmlspecialchars($name).'</a></td><td><a href="http://'.$name.'/sitemap.xml">sitemap.xml</a></td><td><a href="http://'.$name.'/robots.txt">robots.txt</a></td></tr>';
    }
}


$tables = '<table>
            <tr>
                <th>Domain</th><th>Sitemap</th><th>robots.txt</th></tr>
            '.implode('', $domains).'
            </table>';

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('info_sitemaprobots'));
$fragment->setVar('body', $tables, false);
echo $fragment->parse('core/page/section.php');