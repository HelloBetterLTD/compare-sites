<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 3/6/19
 * Time: 1:52 PM
 * To change this template use File | Settings | File Templates.
 */

namespace SilverStripers\CompareSites\Report;


use SilverStripers\CompareSites\Helper\Cache;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\FilesystemLoader;

class Report
{

	protected function getTemplatePath()
	{
		return __DIR__ . '/../../templates/';
	}

	protected function getTemplateFile()
	{
		return 'report/' . $this->shortName() . '.html';
	}

	public function shortName()
	{
		$parts = explode('\\', static::class);
		return end($parts);
	}

	public function getReportPath()
	{
		return Cache::get_report_path() . DIRECTORY_SEPARATOR . 'report.html';
	}

	protected function render($data)
	{
		$loader = new FilesystemLoader($this->getTemplatePath());
		$twig = new Environment($loader);
		return $twig->render($this->getTemplateFile(), $data);
	}

}