<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 3/6/19
 * Time: 2:23 PM
 * To change this template use File | Settings | File Templates.
 */

namespace SilverStripers\CompareSites\Report;


use SilverStripers\CompareSites\Fetch\FetchPages;
use SilverStripers\CompareSites\Helper\Cache;
use SilverStripers\CompareSites\Helper\CrawlPage;

class ErrorPagesReport extends Report
{

	function makeReport()
	{
		$errors = [];
		$success = [];

		$cache = Cache::get_cache();
		foreach ($cache as $url => $item) {

			/**
			 * @var $siteCP CrawlPage
			 */
			$siteCP = $item[FetchPages::SITE];
			if($siteCP->getResponseCode() == 404 || $siteCP->getResponseCode() == -1) {
				$errors[] = $item;
			}
			else {
				$success[] = $item;
			}
		}

		$reportPath = $this->getReportPath();
		$html = $this->render([
			'Errors' => $errors,
			'Success' => $success
		]);

		file_put_contents($reportPath, $html);
		return $reportPath;
	}


}