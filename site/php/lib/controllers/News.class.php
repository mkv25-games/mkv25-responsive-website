<?php

class News
{
	public function __construct($request)
	{
		$view = new TemplateView();
		$view->baseUrl($request->base);
		$view->title('News');
		$view->eyecatch('News', 'A blog about game development, software, and technology.');
		$view->banner('blog short');

		// Get articles from database
		$articleReader = new ArticleReader();
		$articles = $articleReader->getManyArticles();

		// Get news from twitter
		$twitterReader = new TwitterReader();
		$tweets = $twitterReader->getTweets();

		foreach($tweets as $key=>$tweet)
		{
			if(isset($tweet->text))
			{
				$tweetHtml = News::formatTweet($tweet);
				$view->addSingleHTMLColumn($tweetHtml);
			}
		}

		foreach($articles as $key=>$article)
		{
			$content = $article->renderFullArticle();
			$view->addSingleHTMLColumn($content);
		}
		
		$view->render();
	}

	public static function formatTweet($tweet)
	{
		ob_start();

		$content = $tweet->text;
		$postTime = strtotime($tweet->created_at);
		$postDate = date("Y/m/d h:i:s", $postTime);
		$rawTweet = print_r($tweet, true);

		$parsedown = new Parsedown();
		$content = News::preserveHashTags($content);
		$content = $parsedown->text($content);
		$content = News::reverseHashTags($content);

		$media = News::renderMediaForTweet($tweet);

		echo <<<END
			<heading>Twitter</heading>
			<tweet>
				<date>$postDate</date>
				<p>$content</p>
				$media
			</tweet>
END;

		return ob_get_clean();

	}

	public static function renderMediaForTweet($tweet)
	{
		ob_start();

		if(isset($tweet->extended_entities))
		{
			$mediaEntities = $tweet->extended_entities->media;
			foreach($mediaEntities as $key=>$mediaItem)
			{
				echo <<<END
					<media><img src="$mediaItem->media_url_https" /></media>
END;
			}
		}

		return ob_get_clean();
	}

	private static function preserveHashTags($content) {
		return str_replace("#", "[HASH]", $content);
	}

	private static function reverseHashTags($content) {
		return str_replace("[HASH]", "#", $content);
	}
}