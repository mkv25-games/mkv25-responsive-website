<?php

function startsWith($haystack, $needle)
{
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

function endsWith($haystack, $needle)
{
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

function removeNonAlphaNumericCharactersFrom($string)
{
	return preg_replace("/[^A-Za-z0-9 ]/", '', $string);
}

function reportExecutionTime()
{
	return microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
}