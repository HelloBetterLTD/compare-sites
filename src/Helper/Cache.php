<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 3/5/19
 * Time: 11:04 AM
 * To change this template use File | Settings | File Templates.
 */

namespace SilverStripers\CompareSites\Helper;


use SilverStripers\CompareSites\Fetch\FetchPages;

class Cache
{

	private static $domain = null;
	private static $mirror_domain = null;
	private static $report_path = null;

	/*
	 * $data = [
	 * 	'url' => [
	 *  	'against' => CrawlPage
	 *      'site' => CrawlPage
	 *  ]
	 * ]
	 */
	private static $data = [];


	public static function set_domain($domain)
	{
		self::$domain = $domain;
	}

	public static function get_domain()
	{
		return self::$domain;
	}

	public static function set_mirror_domain($domain)
	{
		self::$mirror_domain = $domain;
	}

	public static function get_mirror_domain()
	{
		return self::$mirror_domain;
	}

	public static function set_report_path($path)
	{
		if(!file_exists($path) || !is_dir($path)){
			throw new \Exception($path . ' is not a valid folder');
		}
		$parseURL = parse_url(self::get_domain());
		$host = $parseURL['host'];
		$date = date('Y-m-d_H-i-s');
		$folder = $host . '__' . substr(md5($date), 0, 5);
		$reportPath = $path . DIRECTORY_SEPARATOR . $folder;
		mkdir($reportPath);
		mkdir($reportPath . DIRECTORY_SEPARATOR . 'img');
		self::$report_path = $reportPath;
		return self::$report_path;
	}

	public static function get_report_path()
	{
		return self::$report_path;
	}

	/**
	 * @return array
	 */
	public static function get_cache()
	{
		return self::$data;
	}

	public static function has_fetched($link, $type = FetchPages::MIRROR)
	{
		return array_key_exists($link, self::$data) && empty(self::$data[$link][$type]);
	}

	public static function has_touched($link)
	{
		return array_key_exists($link, self::$data);
	}

	public static function set_fetched($link, $type, CrawlPage $cp)
	{
		if(!array_key_exists($link, self::$data)) {
			self::$data[$link] = [
				FetchPages::MIRROR => null,
				FetchPages::SITE => null
			];
		}

		self::$data[$link][$type] = $cp;
	}



}