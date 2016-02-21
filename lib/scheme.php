<?php

/**
 * YREWRITE Addon.
 *
 * @author gregor.harlan@redaxo.org
 *
 * @package redaxo\yrewrite
 */

class rex_yrewrite_scheme
{
    protected $suffix = '/';

    /**
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * @param string $suffix
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
    }

    /**
     * @param int                 $clang
     * @param rex_yrewrite_domain $domain
     *
     * @return string
     */
    public function getClang($clang, rex_yrewrite_domain $domain)
    {
        if (count($domain->getClangs()) <= 1) {
            return '';
        }

        return '/' . $this->normalize(rex_clang::get($clang)->getCode(), $clang);
    }

    /**
     * @param string              $path
     * @param rex_category        $cat
     * @param rex_yrewrite_domain $domain
     *
     * @return string
     */
    public function appendCategory($path, rex_category $cat, rex_yrewrite_domain $domain)
    {
        return $path . '/' . $this->normalize($cat->getName(), $cat->getClang());
    }

    /**
     * @param string              $path
     * @param rex_article         $art
     * @param rex_yrewrite_domain $domain
     *
     * @return string
     */
    public function appendArticle($path, rex_article $art, rex_yrewrite_domain $domain)
    {
        if ($art->isStartArticle() && $domain->getMountId() != $art->getId()) {
            return $path . $this->suffix;
        }
        return $path . '/' . $this->normalize($art->getName(), $art->getClang()) . $this->suffix;
    }

    /**
     * @param rex_article         $art
     * @param rex_yrewrite_domain $domain
     *
     * @return string|false
     */
    public function getCustomUrl(rex_article $art, rex_yrewrite_domain $domain)
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
     * @param rex_article         $art
     * @param rex_yrewrite_domain $domain
     *
     * @return rex_structure_element|false
     */
    public function getRedirection(rex_article $art, rex_yrewrite_domain $domain)
    {
        return false;
    }

    /**
     * @param string              $path
     * @param rex_yrewrite_domain $domain
     *
     * @return null|string|string[]
     */
    public function getAlternativeCandidates($path, rex_yrewrite_domain $domain)
    {
        if (!$this->suffix || substr($path, -strlen($this->suffix)) === $this->suffix) {
            return null;
        }

        return $path . $this->suffix;
    }

    /**
     * @param string $string
     * @param int    $clang
     *
     * @return string
     */
    public function normalize($string, $clang = 0)
    {
        $string = str_replace(
            ['Ä', 'Ö', 'Ü', 'ä', 'ö', 'ü', 'ß', '/'],
            ['Ae', 'Oe', 'Ue', 'ae', 'oe', 'ue', 'ss', '-'],
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
