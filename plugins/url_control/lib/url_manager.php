<?php

/**
 *
 * @author blumbeet - web.studio
 * @author Thomas Blum
 * @author mail[at]blumbeet[dot]com
 *
 */

class url_manager extends url_control
{
    public static function control()
    {
        global $REX;
        // http://www.domain.de/kategorie/artikel.html
        $url_full = parent::getFullUrl();

        // www.domain.de/kategorie/artikel.html
        $url_path = parent::getUrlPath();
        // /kategorie/artikel.html
        $url_path = substr($url_path, strpos($url_path, '/'));

        $sql = rex_sql::factory();
//        $sql->debugsql = true;
        $sql->setQuery('SELECT  *
                        FROM    ' . $REX['TABLE_PREFIX'] . 'url_control_manager
                        WHERE   status = "1"
                            AND (
                                url = "' . mysql_real_escape_string($url_full) . '"
                                OR
                                url = "' . mysql_real_escape_string($url_path) . '"
                            )
                    ');
        if ($sql->getRows() == 1) {
            $method = $sql->getValue('method');
            $params = unserialize($sql->getValue('method_parameters'));
            switch ($method) {

                case 'article':
                    if ($params['article']['action'] == 'view') {
                        return array(
                            'article_id' => (int) $params['article']['article_id'],
                            'clang'      => (int) $params['article']['clang'],
                        );
                    } elseif ($params['article']['action'] == 'redirect') {
                        $url = rex_getUrl((int) $params['article']['article_id'], (int) $params['article']['clang']);
                        self::redirect($url, $params['http_type']['code']);
                    }
                    break;

                case 'target_url':
                    $url = $params['target_url']['url'];
                    self::redirect($url, $params['http_type']['code']);
                    break;
            }
        }
    }


    public static function redirect($url, $code)
    {
        global $REX;
        header('Location: ' . trim($url), true, $code);
        header('Content-Type: text/html');
        echo '
<!DOCTYPE html>
<html>
    <head>
        <title>' . $REX['SERVERNAME'] . '</title>
        <meta charset="utf-8" />
    </head>
    <body>
        <p style="display: block; font-size:14px; text-align: left;">This page has moved to <a href="' . trim($url) . '">' . str_replace('&', '&amp;', trim($url)) . '</a>.</p>
    </body>
</html>
';
        exit();
    }
}
