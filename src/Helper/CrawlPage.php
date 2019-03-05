<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 3/5/19
 * Time: 10:49 AM
 * To change this template use File | Settings | File Templates.
 */

namespace SilverStripers\CompareSites\Helper;


use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class CrawlPage
{

	/**
	 * @var $url String
	 * @var $input OutputInterface
	 * @var $response ResponseInterface
	 */
	private $url = null;
	private $output = null;
	private $response = null;
	private $base = '';
	private $body = null;

	public function __construct($url, $output, $base )
	{
		$this->url = $url;
		$this->output = $output;
		$this->base = $base;
		$this->crawl();
	}

	public function crawl()
	{
		$client = new Client();
		$this->response = $client->get($this->url);
		if($this->response->getStatusCode() == 200) {
			$this->body = $this->response->getBody()->getContents();
		}
		return $this;

	}

	public function getResponse()
	{
		return $this->response;
	}

	public function getBody()
	{
		return $this->body;
	}

	public function getResponseCode()
	{
		return $this->response->getStatusCode();
	}

	public function getLinks()
	{
		$links = [];
		if($this->body) {
			$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>"; // using a regex expression here
			if(preg_match_all("/$regexp/siU", $this->body, $matches, PREG_SET_ORDER)) {
				foreach($matches as $match) {
					$link = $match[2];
					$link = str_replace('\'', '', $link);
					if($link &&
						$link != '#'
						&& strpos($link, '#') !== 0
						&& $this->isLinkInternal($link)) {
						$links[] = $this->makeAbsolute($link);
					}
				}
			}
		}
		return $links;
	}

	private function isLinkInternal($link)
	{
		if($this->isRelativeLink($link)) {
			return $link;
		}
		// not a relative link, check the host names are the same then.
		$parseURL = parse_url($link);
		$against = parse_url($this->url);
		return !empty($parseURL['host']) && !empty($against['host']) && $parseURL['host'] == $against['host'];
	}

	private function isRelativeLink($link)
	{
		return !$this->isAbsoluteLink($link);
	}

	private function isAbsoluteLink($link)
	{
		// remove queries and hashes
		if(($queryPosition = strpos($link, '?')) !== false) {
			$link = substr($link, 0, $queryPosition-1);
		}
		if(($hashPosition = strpos($link, '#')) !== false) {
			$link = substr($link, 0, $hashPosition-1);
		}

		$colonPosition = strpos($link, ':');
		$slashPosition = strpos($link, '/');
		return (
			parse_url($link, PHP_URL_HOST)
			|| preg_match('%^\s*/{2,}%', $link)
			|| (
				$colonPosition !== FALSE
				&& ($slashPosition === FALSE || $colonPosition < $slashPosition)
			)
		);
	}

	private function makeAbsolute($link)
	{
		if(!$this->isAbsoluteLink($link)) {
			return $this->base . $link;
		}
		return $link;
	}



}