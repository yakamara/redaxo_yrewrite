<?php

/**
 *
 * @author blumbeet - web.studio
 * @author Thomas Blum
 * @author mail[at]blumbeet[dot]com
 *
 */

class url_control
{

    public static function init()
    {
        url_generate::init();
    }


    public static function debug($value, $exit = true)
    {
        echo '<pre style="text-align: left">';
        print_r($value);
        echo '</pre>';

        if ($exit) {
            exit();
        }

    }


    public static function extension_register_extensions()
    {
        global $REX;
        // refresh PathFile
        if ($REX['REDAXO']) {
            $extension_points = array(
                'CAT_ADDED',   'CAT_UPDATED',   'CAT_DELETED',
                'ART_ADDED',   'ART_UPDATED',   'ART_DELETED',
                'CLANG_ADDED', 'CLANG_UPDATED', 'CLANG_DELETED',
                'ALL_GENERATED',
                'XFORM_DATA_ADDED', 'XFORM_DATA_UPDATED'
            );

            foreach ($extension_points as $extension_point) {
                rex_register_extension($extension_point, 'url_generate::generatePathFile');
            }
        }
    }


    /**
     * REDAXO Artikel Id setzen
     *
     */
    public static function extension_rewriter_yrewrite()
    {
        global $REX;
        $params = url_manager::control();
        if (!$params) {
            $params = url_generate::getArticleParams();
        }

        if ((int) $params['article_id'] > 0) {
            $REX['ARTICLE_ID'] = $params['article_id'];
            $REX['CUR_CLANG']  = $params['clang'];
            return true;
        } else {
            return false;
        }
    }


    public static function extension_rewriter_rexseo()
    {
        $params = url_generate::getArticleParams();
        return $params;
    }


    public static function extension_rewriter_rexseo42()
    {
        $params = url_generate::getArticleParams();
        return $params;
    }



    /**
     * gibt den Urlpfad zurück
     *
     */
    public static function getUrlPath()
    {
        $url_path = urldecode($_SERVER['REQUEST_URI']);
        $url_path = ltrim($url_path, '/');
        $url_path = $_SERVER['SERVER_NAME'] . '/' . $url_path;

        // query löschen
        if (($pos = strpos($url_path, '?')) !== false) {
            $url_path = substr($url_path, 0, $pos);
        }

        // fragment löschen
        if (($pos = strpos($url_path, '#')) !== false) {
            $url_path = substr($url_path, 0, $pos);
        }

        return $url_path;
    }


    public static function getFullUrl()
    {
        $s = empty($_SERVER['HTTPS']) ? '' : ($_SERVER['HTTPS'] == 'on') ? 's' : '';
        $protocol = substr(strtolower($_SERVER['SERVER_PROTOCOL']), 0, strpos(strtolower($_SERVER['SERVER_PROTOCOL']), '/')) . $s;
        $port = ($_SERVER['SERVER_PORT'] == '80') ? '' : (':' . $_SERVER['SERVER_PORT']);

        return $protocol . '://' . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
    }



    /**
     * gibt einen sauberen Pfad zurück
     * der für alle Rewriter gleich erstellt wird
     *
     * @return
     * www.domain.de/kategorie/artikel/
     * domain.de/kategorie/artikel/
     *
     * so nicht:
     * http://www.domain.de/kategorie/artikel/
     * http://domain.de/kategorie/artikel/
     * kategorie/artikel/
     * /kategorie/artikel/
     */
    public static function getCleanPath($path)
    {
        global $REX;

        // html und Slashes am Anfang und Ende aus aktueller getUrl() löschen
        $path = trim(str_replace('.html', '', $path), '/') . '/';

        // kein Scheme vorhanden, dann setzen
        if (strpos($path, '://') === false) {

            $server = trim($REX['SERVER'], '/');
            if (strpos($server, '://') === false) {
                $server = 'http://' . $server . '/';
            }

            $path = $server . $path;
        }

        // nur Host und Path zurückgeben
        $parse = parse_url($path);
        $path  = $parse['host'] . $parse['path'];

        return $path;
    }



    /**
     *
     *
     */
    public static function getServer($ignore_scheme = false)
    {
        global $REX;
        $server = trim($REX['SERVER'], '/') . '/';
        if (strpos($server, '://') === false) {
            $scheme = 'http';
            if ($_SERVER['SERVER_PORT'] == 443) {
                $scheme .= 's';
            }
            $server = $scheme . '://' . $server;
        }

        if ($ignore_scheme) {
            $parse  = parse_url($server);
            $server = $parse['host'] . $parse['path'];
        }

        return $server;
    }
}
