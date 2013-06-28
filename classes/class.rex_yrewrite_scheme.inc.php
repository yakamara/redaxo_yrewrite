<?php

class rex_yrewrite_scheme
{
    protected $suffix = '.html';

    public function getClang($clang)
    {
        global $REX;
        if (count($REX['CLANG']) <= 1) {
            return '';
        }
        return '/' . $this->normalize($REX['CLANG'][$clang], $clang);
    }

    public function appendCategory($path, OOCategory $cat)
    {
        return $path . '/' . $this->normalize($cat->getName(), $cat->getClang());
    }

    public function appendArticle($path, OOArticle $art)
    {
        if ($art->isStartArticle() && !rex_yrewrite::isDomainMountpoint($art->getId())) {
            return $path . $this->suffix;
        }
        return $path . '/' . $this->normalize($art->getName(), $art->getClang()) . $this->suffix;
    }

    public function getCustomUrl(OOArticle $art)
    {
        if (rex_yrewrite::isDomainStartarticle($art->getId())) {
            return 0 == $art->getClang() ? '/' : $this->getClang($art->getClang()) . '/';
        }
        if ($url = $art->getValue('yrewrite_url')) {
            return $url;
        }
        return false;
    }

    protected function normalize($string, $clang = 0)
    {
        $string = str_replace(
            array('Ä', 'Ö', 'Ü', 'ä', 'ö', 'ü', 'ß'),
            array('Ae', 'Oe', 'Ue', 'ae', 'oe', 'ue', 'ss'),
            $string
        );
        $string = preg_replace('/[^\w -]+/', '', $string);
        $string = strtolower(trim($string));
        $string = urlencode($string);
        $string = preg_replace('/[+-]+/', '-', $string);
        return $string;
    }
}
