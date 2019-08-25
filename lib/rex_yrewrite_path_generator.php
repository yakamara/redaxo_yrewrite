<?php

/**
 * @internal
 */
class rex_yrewrite_path_generator
{
    /** @var rex_yrewrite_scheme */
    private $scheme;

    /** @var rex_yrewrite_domain[][] */
    private $domains;

    /** @var array */
    private $paths;

    /** @var array */
    private $redirections;

    public function __construct(rex_yrewrite_scheme $scheme, array $domains, array $paths, array $redirections)
    {
        $this->scheme = $scheme;
        $this->domains = $domains;
        $this->paths = $paths;
        $this->redirections = $redirections;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function getRedirections(): array
    {
        return $this->redirections;
    }

    public function generateAll()
    {
        $this->paths = [];
        $this->redirections = [];

        foreach (rex_clang::getAllIds() as $clangId) {
            $domain = $this->domains[0][$clangId];
            $path = $this->scheme->getClang($clangId, $domain);

            foreach (rex_category::getRootCategories(false, $clangId) as $cat) {
                $this->generatePaths($domain, $path, $cat);
            }

            foreach (rex_article::getRootArticles(false, $clangId) as $art) {
                $this->setPath($art, $domain, $path);
            }
        }
    }

    public function generate(rex_article $article)
    {
        $clangId = $article->getClangId();

        $domain = $this->domains[0][$clangId];
        $path = $this->scheme->getClang($clangId, $domain);

        $tree = $article->getParentTree();
        $category = null;

        if ($article->isStartArticle()) {
            $category = array_pop($tree);
        }

        foreach ($tree as $parent) {
            $path = $this->scheme->appendCategory($path, $parent, $domain);

            [$domain, $path] = $this->setDomain($parent, $domain, $path);

            $this->setPath(rex_article::get($parent->getId(), $clangId), $domain, $path);
        }

        if ($article->isStartArticle()) {
            $this->generatePaths($domain, $path, $category);
        } else {
            $this->setPath($article, $domain, $path);
        }
    }

    public function removeArticleId(int $articleId)
    {
        foreach ($this->paths as $domain => $c) {
            unset($this->paths[$domain][$articleId]);
        }

        unset($this->redirections[$articleId]);
    }

    private function setDomain(rex_structure_element $element, rex_yrewrite_domain $domain, string $path)
    {
        $id = $element->getId();
        $clang = $element->getClang();

        if (isset($this->domains[$id][$clang])) {
            $domain = $this->domains[$id][$clang];
            $path = $this->scheme->getClang($clang, $domain);
        }

        return [$domain, $path];
    }

    private function setPath(rex_article $article, rex_yrewrite_domain $domain, string $path)
    {
        [$domain, $path] = $this->setDomain($article, $domain, $path);

        $articleId = $article->getId();
        $clangId = $article->getClang();

        $redirection = $this->scheme->getRedirection($article, $domain);

        if ($redirection instanceof rex_structure_element) {
            $this->redirections[$articleId][$clangId] = [
                'id' => $redirection->getId(),
                'clang' => $redirection->getClang(),
            ];

            unset($this->paths[$domain->getName()][$articleId][$clangId]);

            return;
        }

        unset($this->redirections[$articleId][$clangId]);

        $url = $this->scheme->getCustomUrl($article, $domain);

        if (!is_string($url)) {
            $url = $this->scheme->appendArticle($path, $article, $domain);
        }

        $this->paths[$domain->getName()][$articleId][$clangId] = ltrim($url, '/');
    }

    private function generatePaths(rex_yrewrite_domain $domain, $path, rex_category $category)
    {
        $path = $this->scheme->appendCategory($path, $category, $domain);

        [$domain, $path] = $this->setDomain($category, $domain, $path);

        foreach ($category->getChildren() as $child) {
            $this->generatePaths($domain, $path, $child);
        }

        foreach ($category->getArticles() as $article) {
            $this->setPath($article, $domain, $path);
        }
    }
}
