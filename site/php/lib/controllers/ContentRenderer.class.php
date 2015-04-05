<?php

class ContentRenderer
{
	public function __construct($request)
	{
		$route = $request->path;
		$file = sprintf("%s.content.md", $route);

		if(Content::exists($file))
		{
			$content = Content::load($file);
			
			$title = $request->page;
			$title = str_replace("-", " ", $title);
			$title = ucfirst($title);

			$view = new TemplateView();
			$view->title($title);
			$view->eyecatch($title, '');
			$view->banner('scrapbook short');

			$view->addSingleColumn($content);

			echo $view->render();
		}
		else
		{
			$view = new DefaultView();
			$view->responseCode(404, "Content not found for $route");
			$view->routeInfo();
		}
	}
}