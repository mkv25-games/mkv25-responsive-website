<?php

class Home
{
	public function render($request)
	{
		$TITLE = Environment::get('TITLE');

		$view = new TemplateView();
		$view->baseUrl($request->base);
		$view->title($TITLE);
		$view->eyecatch('Coding at the core', 'Making games, discussing software, sharing source.');
		$view->banner('home short');
		$view->description('mkv25.net is a game and software development site - turning bits and bytes into logical blocks, in order to produce interesting, meaningful user experiences.');

		$homeCodingAtTheCore = Content::load('content/home/coding-at-the-core.content.md');
		$view->addSingleColumn($homeCodingAtTheCore);

		$homeMakingGames = Content::load('content/home/making-games.content.md');
		$view->addSingleColumn($homeMakingGames);

		$firstFeature = Content::load('content/features/featured-game-1.content.md');
		$secondFeature = Content::load('content/features/featured-game-2.content.md');
		$thirdFeature = Content::load('content/features/featured-game-3.content.md');
		$fourthFeature = Content::load('content/features/featured-game-4.content.md');
		$view->addColumns($firstFeature, $secondFeature, $thirdFeature, $fourthFeature);

		// Get news item from twitter
		$twitterReader = new TwitterReader();
		$tweets = $twitterReader->getTweets();

		$numberOfTweets = 0;

		if (is_array($tweets))
		{
			$tweets = News::filterPersonalTweets($tweets);
			$tweet = $tweets[0];
			if(isset($tweet->text))
			{
				$tweetHtml = TwitterFormatter::renderTweet($tweet);
				$view->addSingleColumn('<heading>Latest News</heading>' . $tweetHtml);
			}
		}

		$homeDiscussingSoftware = Content::load('content/home/discussing-software.content.md');
		$view->addSingleColumn($homeDiscussingSoftware);

		$homeSharingSource = Content::load('content/home/sharing-source.content.md');
		$view->addSingleColumn($homeSharingSource);

		return $view->render();
	}
}
