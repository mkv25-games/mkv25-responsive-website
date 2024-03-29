<?php

class Scrapbook
{
	var $reader;

	public function render($request)
	{
		$this->reader = new ArticleReader();

		$response = false;

		if($request->page == 'scrapbook' || !$request->page)
		{
			$response = $this->renderArticleList($request);
		}
		else
		{
			// Get article from database
			$article = $this->reader->getArticleByUrlName($request->page);

			if($article)
			{
				$response = $this->renderArticle($article, $request);
			}
			else
			{
				$response = $this->render404Response($request);
			}
		}

		return $response;
	}

	private function renderArticle($article, $request)
	{
		$linkedArticles = $this->reader->getArticlesForReferences($article->id, $article->linkedArticles);

		$view = new TemplateView();
		$view->baseUrl($request->base);
		$view->title($article->name);
		$view->eyecatch($article->name, $article->keywords);
		$view->banner('scrapbook short');

		$breadcrumbs = array(
			'Scrapbook' => 'scrapbook',
			$article->name => ''
		);

		$breadcrumbTrail = BreadcrumbFormatter::renderBreadcrumbTrail($breadcrumbs);

		$view->addSingleColumn($breadcrumbTrail);

		$content = $article->renderFullArticle();
		$view->addSingleColumn($content);

		if(is_array($linkedArticles))
		{
			$linkedContent = "<heading>Related</heading>";
			$linkedContent .= ArticleFormatter::renderLinksAsIcons($linkedArticles);

			$view->addSingleColumn($linkedContent);
		}

		return $view->render();
	}

	public function renderArticleList($request)
	{
		$view = new TemplateView();
		$view->baseUrl($request->base);
		$view->title("Scrapbook");
		$view->eyecatch("Scrapbook", "scrapbook html javascript flash artwork index");
		$view->banner('scrapbook short');
        
        $view->addSingleColumn('Various artwork and experiments from June 2002 until present day, present time.');

		// Get articles from database
		$articles = $this->reader->getAllArticles();

		// Save articles as physical files
		if ($articles)
		{
			$newFiles = ArticleIO::writeArticlesToFileSystem($articles);
			if($newFiles)
			{
				$view->addSingleColumn($newFiles);
			}

			$iconList = ArticleFormatter::renderLinksAsIcons($articles);
			$view->addSingleColumn($iconList);
		}
		else
		{
			$view->addSingleColumn("No articles found.");
		}

		return $view->render();
	}

	private function render404Response($request)
	{
		$view = new DefaultView();
		$view->responseCode(404, 'Article not found: ' . $request->page);
		$view->routeInfo();

		return $view->render();
	}
}
