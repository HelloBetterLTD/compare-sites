<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 3/5/19
 * Time: 12:36 PM
 * To change this template use File | Settings | File Templates.
 */

namespace SilverStripers\CompareSites\Fetch;


use SilverStripers\CompareSites\Helper\Cache;
use SilverStripers\CompareSites\Helper\CrawlPage;

class FetchPagesFromSite
{
	private $output = null;
	private $type = 'site';

	public function __construct($output = null)
	{
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
			$link = str_replace(Cache::get_mirror_domain(), Cache::get_domain(), $url);
			$crawlPage = new CrawlPage($link, $this->output, $this->type == FetchPages::MIRROR ? Cache::get_mirror_domain() : Cache::get_domain());
			Cache::set_fetched($url, 'site', $crawlPage);
		}
	}




}