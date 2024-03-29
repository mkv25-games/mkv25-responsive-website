<?php

class Router
{
	public static function handleRouting()
	{
		Environment::initialise();

		$siteRootUrl = Environment::getEnvironmentVariable('MKV25_SITE_BASE', '/');

		$request = new Request($siteRootUrl);
		$section = $request->section;
		$path = $request->path ? $request->path : 'home';
        $folder = $request->folder;

		Environment::register('REQUEST', $request);
		Environment::register('SITE_ROOT_URL', $siteRootUrl);
		if(Routes::isRouteConfigured($path))
		{
			Router::renderPath($path, $request);
		}
		else if(Routes::isRouteConfigured($folder))
		{
			Router::renderPath($folder, $request);
		}
		else if(Routes::isRouteConfigured($section))
		{
			Router::renderPath($section, $request);
		}
		else
		{
			$view = new DefaultView();
			$view->responseCode(404, 'File not found, no route set');
			$view->routeInfo();

			print $view->render();
		}
	}

	private static function renderPath($route, $request)
	{
		// Buffer command to capture stray print and echo statements
		ob_start();

		session_start();
		$auth = new Auth();

		try
		{
			$controller = Routes::getControllerForRoute($route);

			$pageContent = $controller->render($request);

			// Add additional page headers after render has completed
			PageStatsView::addPageStats();
		}
		catch (Exception $exception)
		{
			$view = new DefaultView();
			$view->responseCode(501, 'Error creating controller for route: ' . $route);
			$view->displayException($exception);
			$view->routeInfo();

			$pageContent = $view->render();
		}

		$echoedContent = ob_get_clean();

		// Return the page content to the user
		print $pageContent;

		// Include stray content as a comment at end of response
		if ($echoedContent)
		{
			print "<!-- $echoedContent -->";
		}
	}
}
