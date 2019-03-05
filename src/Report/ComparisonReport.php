<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 3/5/19
 * Time: 12:41 PM
 * To change this template use File | Settings | File Templates.
 */

namespace SilverStripers\CompareSites\Report;


use BigV\ImageCompare;
use SilverStripers\CompareSites\Command\CompareCommand;
use SilverStripers\CompareSites\Helper\Cache;
use SilverStripers\CompareSites\Helper\CrawlPage;
use Spatie\Browsershot\Browsershot;

class ComparisonReport
{

	private function siteImgPath($url)
	{
		return CompareCommand::$path . DIRECTORY_SEPARATOR . 'img' .DIRECTORY_SEPARATOR . $this->siteImg($url);
	}

	private function siteImg($url)
	{
		return md5($url . '__site') . '.jpg';
	}

	private function againstImgPath($url)
	{
		return CompareCommand::$path . DIRECTORY_SEPARATOR . 'img' .DIRECTORY_SEPARATOR . $this->againstImg($url);
	}

	private function againstImg($url)
	{
		return md5($url . '__against') . '.jpg';
	}

	function makeReport()
	{
		$errors = [];
		$notfounds = [];
		$warnings = [];
		$success = [];

		$cache = Cache::get_cache();
		foreach ($cache as $url => $item) {

			/**
			 * @var $againstCP CrawlPage
			 * @var $siteCP CrawlPage
			 */
			$againstCP = $item['against'];
			$siteCP = $item['site'];
			if($siteCP->getResponseCode() == 404) {
				$notfounds[$url] = $item;
			}
			else if($siteCP->getResponseCode() == 200) {
				$siteImgPath = $this->siteImgPath($url);
				$againstImgPath = $this->againstImgPath($url);

				Browsershot::html($siteCP->getBody())
					->fullPage()
					->setDelay(2000)
					->save($siteImgPath);
				Browsershot::html($againstCP->getBody())
					->fullPage()
					->setDelay(2000)
					->save($againstImgPath);

				$comparisonResult = 0;

				if(file_exists($siteImgPath) && file_exists($againstImgPath)) {
					$image = new ImageCompare();
					$comparisonResult = $image->compare($siteImgPath, $againstImgPath);
				}

				if($comparisonResult == 0 || $comparisonResult == '~0') {
					$success[$url] = $item;
				}
				else {
					$warnings[$url] = $item;
				}

			}
			else {
				$errors[] = $item;
			}
		}

		$iErrors = count($errors);
		$iNotFounds = count($notfounds);
		$iWarnings = count($warnings);
		$iSuccess = count($success);

		$errorTable = $this->generateErrorsTable($errors);
		$successTable = $this->generateSuccessTable($success);
		$notFoundTable = $this->generateNotFoundTable($notfounds);
		$warningTable = $this->generateWarningsTable($warnings);

		$html = <<<HTML
<html>
<head>
	<style>
		body {
			font-size: 100%;
    		font-family: arial;	
		}
		h1 {
			font-size: 25px;
		}
		
		.error {
			color: #cc4b37;
		}
		.error-bg,
		.error-bg a {
			color: white;
			background: #cc4b37;
		}
		.success {
			color: #3adb76;
		}
		.success-bg,
		.success-bg a {
			color: white;
			background: #3adb76;
		}
		.warning {
			color: #ffae00;
		}
		.warning-bg,
		.warning-bg a {
			color: white;
			background: #ffae00;
		}
		.not-found {
			color: #8a8a8a;
		}
		.not-found-bg
		.not-found-bg a {
			color: white;
			background: #8a8a8a;
		}
		table {
			border-top: 1px solid #e6e6e6;
			border-left: 1px solid #e6e6e6;
			width: 100%;
		}
		td {
			border-bottom: 1px solid #e6e6e6;
			border-right: 1px solid #e6e6e6;
			padding: 3px 5px;
			vertical-align: top;
		}
		
		img {
			width: 100%;
		}
	</style>
</head>
<body>
<div class="container">
	<table>
		<tr>
			<td class="success-bg"><a href="#success">{$iSuccess} Pages passed</a></td>
		</tr>
		<tr>
			<td class="error-bg"><a href="#errors">{$iErrors} Errors found</a></td>
		</tr>
		<tr>
			<td class="warning-bg"><a href="#warnings">{$iWarnings} Warnings</a></td>
		</tr>
		<tr>
			<td class="not-found-bg"><a href="#not-founds">{$iNotFounds} Pages not found</a></td>
		</tr>
	</table>

	{$errorTable}
	{$warningTable}
	{$notFoundTable}
	{$successTable}
</div>


</body>
</html>
HTML;

		$reportPath = CompareCommand::$path . DIRECTORY_SEPARATOR . 'report.html';
		file_put_contents($reportPath, $html);
		return $reportPath;
	}


	public function generateErrorsTable($items)
	{
		$html = '';
		if(count($items) !== 0) {
			$html = <<<HTML
<table id="errors">
	<tr>
		<th>URL</th>
		<th>Error Code</th>
		<th>Output</th>
	</tr>
HTML;
			foreach ($items as $url => $item) {
				/**
				 * @var $againstCP CrawlPage
				 * @var $siteCP CrawlPage
				 */
				$againstCP = $item['against'];
				$siteCP = $item['site'];
				$code = $siteCP->getResponseCode();
				$body = htmlspecialchars($siteCP->getBody());
				$html .= <<<HTML
<tr>
	<td>{$url}</td>
	<td>{$code}</td>
	<td>
		<pre>
			{$body}
		</pre>
	</td>
</tr>
HTML;
			}
			$html .= '</table>';
		}
		return $html;
	}

	public function generateSuccessTable($items)
	{
		$html = '';
		if(count($items) !== 0) {
			$html = <<<HTML
<table id="success">
	<tr>
		<th>URL</th>
		<th>Original Image</th>
		<th>Site Image</th>
	</tr>
HTML;
			foreach ($items as $url => $item) {

				$againstImg = $this->againstImg($url);
				$siteImage = $this->siteImg($url);
				$html .= <<<HTML
<tr>
	<td>{$url}</td>
	<td><img src="./img/{$againstImg}"></td>
	<td><img src="./img/{$siteImage}"></td>
</tr>
HTML;
			}
			$html .= '</table>';
		}
		return $html;
	}

	public function generateNotFoundTable($items)
	{
		$html = '';
		if(count($items) !== 0) {
			$html = <<<HTML
<table id="not-founds">
	<tr>
		<th>Original URL</th>
		<th>Site URL</th>
	</tr>
HTML;
			foreach ($items as $url => $item) {
				$siteURL = str_replace(CompareCommand::$against_base, CompareCommand::$site_base, $url);
				$html .= <<<HTML
<tr>
	<td>{$url}</td>
	<td>{$siteURL}</td>
</tr>
HTML;
			}
			$html .= '</table>';
		}
		return $html;
	}

	public function generateWarningsTable($items)
	{
		$html = '';
		if(count($items) !== 0) {
			$html = <<<HTML
<table id="warnings">
	<tr>
		<th>URL</th>
		<th>Original Image</th>
		<th>Site Image</th>
	</tr>
HTML;
			foreach ($items as $url => $item) {

				$againstImg = $this->againstImg($url);
				$siteImage = $this->siteImg($url);
				$html .= <<<HTML
<tr>
	<td>{$url}</td>
	<td><img src="./img/{$againstImg}"></td>
	<td><img src="./img/{$siteImage}"></td>
</tr>
HTML;
			}
			$html .= '</table>';
		}
		return $html;
	}


}