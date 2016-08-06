<?php

/**
 * YREWRITE Addon.
 *
 * @author jan.kristinus@yakamara.de
 *
 * @package redaxo\yrewrite
 */

class rex_yrewrite_forward
{
    public static $pathfile = '';
    public static $paths = [];

    public static $movetypes = [
        '301' => '301 - Moved Permanently',
        '303' => '303 - See Other',
        '307' => '307 - Temporary Redirect',
    ];

    public static function init()
    {
        self::$pathfile = rex_path::addonCache('yrewrite', 'forward_pathlist.php');
        self::readPathFile();
    }

    // ------------------------------

    public static function getForward($params)
    {
        // Url wurde von einer anderen Extension bereits gesetzt
        if (isset($params['subject']) && $params['subject'] != '') {
            return $params['subject'];
        }

        self::init();

        $domain = rtrim($params['domain']->getUrl(), '/');
        if ($domain == 'default') {
            $domain = '';
        }
        $url = $params['url'];

        foreach (self::$paths as $p) {
            if (rtrim($p['domain'], '/') == $domain && ($p['url'] == $url || $p['url'] . '/' == $url)) {
                $forward_url = '';
                if ($p['type'] == 'article' && ($art = rex_article::get($p['article_id'], $p['clang']))) {
                    $forward_url = rex_getUrl($p['article_id'], $p['clang']);
                } elseif ($p['type'] == 'media' && ($media = rex_media::get($p['media']))) {
                    $forward_url = '/files/'.$p['media'];
                } elseif ($p['type'] == 'extern' && $p['extern'] != '') {
                    $forward_url = $p['extern'];
                }

                if ($forward_url != '') {
                    header('HTTP/1.1 '.self::$movetypes[$p['movetype']]);
                    header('Location: ' . $forward_url);
                    exit;
                }
            }
        }
        return false;
    }

    // ------------------------------

    public static function readPathFile()
    {
        if (!file_exists(self::$pathfile)) {
            self::generatePathFile();
        } else {
            $content = file_get_contents(self::$pathfile);
            self::$paths = json_decode($content, true);
        }
    }

    public static function generatePathFile()
    {
        $gc = rex_sql::factory();
        $content = $gc->getArray('select * from '.rex::getTable('yrewrite_forward'));
        rex_file::put(self::$pathfile, json_encode($content));
    }
}
