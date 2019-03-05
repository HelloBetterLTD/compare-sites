<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 3/5/19
 * Time: 12:36 PM
 * To change this template use File | Settings | File Templates.
 */

namespace SilverStripers\CompareSites\Fetch;


use SilverStripers\CompareSites\Command\CompareCommand;
use SilverStripers\CompareSites\Helper\Cache;
use SilverStripers\CompareSites\Helper\CrawlPage;

class FetchPagesFromSite
{
	private $url = null;
	private $output = null;
	private $type = 'site';

	public function __construct($url, $output = null)
	{
		$this->url = $url;
		$this->output = $output;
	}

	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	public function run()
	{
		$this->processCache();
	}

	public function processCache()
	{
		$cache = Cache::get_cache();
		foreach ($cache as $url => $item) {
			$link = str_replace(CompareCommand::$against_base, CompareCommand::$site_base, $url);
			$crawlPage = new CrawlPage($link, $this->output, $this->type == 'against' ? CompareCommand::$against_base : CompareCommand::$site_base);
			Cache::set_fetched($url, 'site', $crawlPage);
		}
	}




}