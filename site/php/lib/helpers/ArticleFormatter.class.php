<?php

class ArticleFormatter
{
	public static function renderLinksAsIcons($articles)
	{
		$SITE_ROOT_URL = Environment::get('SITE_ROOT_URL');
		$SHOWCASE_ICON_URL = Environment::get('SHOWCASE_ICON_URL');

		ob_start();

		echo "<iconlist>";
		
		foreach ($articles as $key=>$article)
		{
			$iconUrl = $SHOWCASE_ICON_URL . $article->displayIcon;
			$articleUrl = $SITE_ROOT_URL . 'scrapbook/' . $article->urlname;
			$articleTitle = $article->name;

			echo <<<END
<a href="$articleUrl" title="$articleTitle"><icon style="background-image: url('$iconUrl')"></icon></a>
END;
		}

		echo "</iconlist>";

		return ob_get_clean();
	}

	public static function renderFlashContent($contentUrl, $contentId='flash', $width="100%", $height=400)
	{
		// Remove non-alpha numeric characters from ID
		$contentId = 'flash-' . removeNonAlphaNumericCharactersFrom($contentId);

		$width = "100%";
		$height = (is_numeric($height)) ? $height . 'px' : $height;

		ob_start();
		echo <<<END
		<object id="$contentId" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="$width" height="$height">
	        <param name="movie" value="$contentUrl" />
	        <!--[if !IE]>-->
	        <object type="application/x-shockwave-flash" data="$contentUrl" width="$width" height="$height">
	        <!--<![endif]-->
	        	<p>Alternative content</p>
	        <!--[if !IE]>-->
	        </object>
	        <!--<![endif]-->
     	</object>
    	
    	<script type="text/javascript">
    		swfobject.registerObject("$contentId", "9.0.115", "site/scripts/expressInstall.swf");
    	</script>
END;
		return ob_get_clean();
	}

	private static function renderIFrameContent($contentUrl, $width="100%", $height=400)
	{
		$width = (is_numeric($width)) ? $width . 'px' : $width;
		$height = (is_numeric($height)) ? $height . 'px' : $height;

		ob_start();
		echo <<<END
		<div class="media">
			<iframe src="$contentUrl" frameborder="0" scrolling="no" style="width: $width; height: $height;">
				<p>You need to activate IFRAMEs to view this content.</p> 
			</iframe>
		</div>
END;
		return ob_get_clean();
	}

	private static function buildUrl($contentUrl)
	{
		$SHOWCASE_BASE_URL = Environment::get('SHOWCASE_BASE_URL');

		if($contentUrl && !startsWith($contentUrl, 'http'))
		{
			$contentUrl = $SHOWCASE_BASE_URL . $contentUrl;
		}

		return $contentUrl;
	}

	public static function renderArticle($name, $type, $width, $height, $url, $urlname, $category, $description, $postdate=false, $simpleMode=false, $icon_url=false)
	{
		ob_start();
		$description = stripslashes($description);
		$posttime = strtotime($postdate);

		$localUrl = ArticleFormatter::buildUrl($url);

		echo '<block class="right">';

		if($icon_url)
		{
			echo <<<END
			<icon style="background: url('//mkv25.net/site/icons/$icon_url') no-repeat center center;"></icon>
			<br/>
END;
		}
		
		if($posttime)
		{
			$formattedDate = date('Y/m/d H:m:s', $posttime);
			echo "<date>$formattedDate</date>";
		}

		echo '</block>';
		
		echo <<<END
			<heading><a class="permalink" href="scrapbook/$urlname">$name</a></heading>
END;

		$articleContent = BBCodeOutput::process($description, $simpleMode);
		echo <<<END
		<div class="article content">
			$articleContent
		</div>
END;

		if($type == 'iframe')
		{
			$width = ($width == 0) ? $width = '100%' : $width;
			$height = ($height == 0) ? $height = 400 : $height;

			if(endsWith($url, '.swf'))
			{
				echo ArticleFormatter::renderFlashContent($localUrl, $name, $width, $height);
			}
			else
			{
				echo ArticleFormatter::renderIFrameContent($localUrl, $width, $height);
			}
		}
		else if($type == 'applet')
		{
			echo <<<END
			<div class="media">
				<applet code="$url" archive="//mkv25.net/applets/$url/$url.jar" width="$width" height="$height">
				<p>You need to have Java installed for this applet to work correctly.</p>
				</applet>
			</div>
			
			<p class="content links">
				<a class="sourcecode" href="//mkv25.net/applets/$url/$url.pde" target="_blank"><b>Source code</b></a>
				<a class="newwindow" href="//mkv25.net/applets/$url" target="_blank"><b>Open content in new tab</b></a>
			</p>
END;
		}
		else if($type == 'image')
		{
			if($width > 0 && $height > 0)
			{
				echo <<<END
				<div class="media"><img src="$localUrl" width="$width" height="$height" /></div> 
END;
			}
			else
			{
				echo <<<END
				<div class="media"><img src="$localUrl" /></div> 
END;
			}
		}
		
		if($localUrl)
		{
			echo <<<END
			<p class="content links"><a class="newwindow" href="$localUrl" target="_blank"><b>Open content in new tab</b></a></p> 
END;
		}

		return ob_get_clean();
	}
}