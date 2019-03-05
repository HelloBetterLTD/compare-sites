<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 3/5/19
 * Time: 11:04 AM
 * To change this template use File | Settings | File Templates.
 */

namespace SilverStripers\CompareSites\Helper;


class Cache
{

	/*
	 * $data = [
	 * 	'url' => [
	 *  	'against' => CrawlPage
	 *      'site' => CrawlPage
	 *  ]
	 * ]
	 */
	private static $data = [];

	/**
	 * @return array
	 */
	public static function get_cache()
	{
		return self::$data;
	}

	public static function has_fetched($link, $type = 'against')
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
				'against' => null,
				'site' => null
			];
		}

		self::$data[$link][$type] = $cp;
	}



}