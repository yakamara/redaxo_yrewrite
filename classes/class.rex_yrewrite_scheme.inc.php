<?php

/**
 * YREWRITE Addon
 * @author gregor.harlan@redaxo.org
 * @package redaxo4.5
 */

class rex_yrewrite_scheme
{
    protected $suffix = '.html';

    /**
     * @param int                 $clang
     * @param rex_yrewrite_domain $domain
     * @return string
     */
    public function getClang($clang, rex_yrewrite_domain $domain)
    {
        global $REX;
        if (count($domain->getClangs()) <= 1) {
            return '';
        }
        return '/' . $this->normalize($REX['CLANG'][$clang], $clang);
    }

    /**
     * @param string              $path
     * @param OOCategory          $cat
     * @param rex_yrewrite_domain $domain
     * @return string
     */
    public function appendCategory($path, OOCategory $cat, rex_yrewrite_domain $domain)
    {
        return $path . '/' . $this->normalize($cat->getName(), $cat->getClang());
    }

    /**
     * @param string              $path
     * @param OOArticle           $art
     * @param rex_yrewrite_domain $domain
     * @return string
     */
    public function appendArticle($path, OOArticle $art, rex_yrewrite_domain $domain)
    {
        if ($art->isStartArticle() && $domain->getMountId() != $art->getId()) {
            return $path . $this->suffix;
        }
        return $path . '/' . $this->normalize($art->getName(), $art->getClang()) . $this->suffix;
    }

    /**
     * @param OOArticle           $art
     * @param rex_yrewrite_domain $domain
     * @return string|false
     */
    public function getCustomUrl(OOArticle $art, rex_yrewrite_domain $domain)
    {
        if ($domain->getStartId() == $art->getId()) {
            if ($domain->getStartClang() == $art->getClang()) {
                return '/';
            }
            return $this->getClang($art->getClang(), $domain) . '/';
        }
        if ($url = $art->getValue('yrewrite_url')) {
            return $url;
        }
        return false;
    }

    /**
     * @param OOArticle           $art
     * @param rex_yrewrite_domain $domain
     * @return OORedaxo|false
     */
    public function getRedirection(OOArticle $art, rex_yrewrite_domain $domain)
    {
        return false;
    }

    /**
     * @param string $string
     * @param int    $clang
     * @return string
     */
    protected function normalize($string, $clang = 0)
    {
        $string = str_replace(
            array('Ä', 'Ö', 'Ü', 'ä', 'ö', 'ü', 'ß'),
            array('Ae', 'Oe', 'Ue', 'ae', 'oe', 'ue', 'ss'),
            $string
        );
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        $string = preg_replace('/[^\w -]+/', '', $string);
        $string = strtolower(trim($string));
        $string = urlencode($string);
        $string = preg_replace('/[+-]+/', '-', $string);
        return $string;
    }
}
