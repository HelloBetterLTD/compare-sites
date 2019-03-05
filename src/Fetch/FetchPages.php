<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 3/5/19
 * Time: 10:44 AM
 * To change this template use File | Settings | File Templates.
 */

namespace SilverStripers\CompareSites\Fetch;


use SilverStripers\CompareSites\Command\CompareCommand;
use SilverStripers\CompareSites\Helper\Cache;
use SilverStripers\CompareSites\Helper\CrawlPage;

class FetchPages
{

	private $url = null;
	private $output = null;
	private $depth = null;
	private $type = 'against';

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

	public function setDepth($depth)
	{
		$this->depth = $depth;
		return $this;
	}

	public function run()
	{
		$this->processURL($this->url);
	}

	public function processURL($link, $d = 0)
	{
		if($this->depth && $d > $this->depth) {
			return;
		}

		if($this->output) {
			$this->output->writeln('Fetching ' . $link);
		}
		if(!Cache::has_fetched($link, 'against')) {
			$crawlPage = new CrawlPage($link, $this->output, $this->type == 'against' ? CompareCommand::$against_base : CompareCommand::$site_base);
			Cache::set_fetched($link, 'against', $crawlPage);
			if ($links = $crawlPage->getLinks()) {
				$childD = $d + 1;
				foreach ($links as $childLink) {
					if(!Cache::has_touched($childLink)) {
						$this->processURL($childLink, $childD);
					}
				}
			}

		}
	}

}