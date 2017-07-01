<?php
namespace Proverbius\Service;

class GenericFunction
{
	private $app;
	
	public function __construct($app)
	{
		$this->app = $app;
	}
	
	public function getUniqCleanNameForFile($file)
	{
		$file = preg_replace('/[^A-Za-z0-9 _\-.]/', '', $file->getClientOriginalName());
		return uniqid()."_".$file;
	}
	
	public function file_get_contents_proxy($url, $proxy)
	{
		$cu = curl_init();

		curl_setopt($cu, CURLOPT_URL, $url);
		curl_setopt($cu, CURLOPT_PROXY, $proxy);
		curl_setopt($cu, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($cu, CURLOPT_HEADER, 0);

		$curl_scraped_page = curl_exec($cu);

		curl_close($cu);

		return $curl_scraped_page;
	}

	public static function slugify($text, $max_size = null)
	{
		$text = html_entity_decode($text, ENT_QUOTES);

		// replace non letter or digits by -
		$text = preg_replace('~[^\pL\d]+~u', '-', $text);

		// transliterate
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);

		// trim
		$text = trim($text, '-');

		// remove duplicate -
		$text = preg_replace('~-+~', '-', $text);

		// lowercase
		$text = strtolower($text);

		if (empty($text)) {
			return 'n-a';
		}
		
		

		if(!empty($max_size))
			return trim(substr($text, 0, $max_size), "-");

		return $text;
	}
}