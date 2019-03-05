<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 3/5/19
 * Time: 10:28 AM
 * To change this template use File | Settings | File Templates.
 */
namespace SilverStripers\CompareSites\Command;


use SilverStripers\CompareSites\Fetch\FetchPages;
use SilverStripers\CompareSites\Fetch\FetchPagesFromSite;
use SilverStripers\CompareSites\Report\ComparisonReport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompareCommand extends Command
{

	protected static $defaultName = 'run';
	public static $against_base = null;
	public static $site_base = null;
	public static $path = '';

	protected function configure()
	{
		$this
			->addArgument('site', InputArgument::REQUIRED, 'Site your are testing')
			->addArgument('against', InputArgument::REQUIRED, 'Site your are testing against')
			->addArgument('path', InputArgument::REQUIRED, 'Path to generate the report to')
			->addArgument('depth', InputArgument::OPTIONAL, 'Depth of links to check')
			->setDescription('Compare two sites.')
			->setHelp('php bin/console run')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$site = $input->getArgument('site');
		$against = $input->getArgument('against');
		$path = $input->getArgument('path');
		$depth = $input->getArgument('depth') ? : null;

		self::$against_base = $against;
		self::$site_base = $site;

		if(!file_exists($path) || !is_dir($path)){
			$output->writeln($path . ' cannot be found');
			return;
		}

		$parseURL = parse_url($site);
		$host = $parseURL['host'];
		$date = date('Y-m-d_H-i-s');

		$folder = $host . '__' . substr(md5($date), 0, 5);
		$reportPath = $path . DIRECTORY_SEPARATOR . $folder;
		mkdir($reportPath);
		mkdir($reportPath . DIRECTORY_SEPARATOR . 'img');
		self::$path = $reportPath;

		$output->writeln([
			'Starting',
			'============',
			'Generating reports on ' . $folder
		]);

		$fetcher = new FetchPages($against, $output);
		$fetcher->setDepth($depth);
		$fetcher->run();
		$fetcher = new FetchPagesFromSite($against, $output);
		$fetcher->run();

		$report = new ComparisonReport();
		$path = $report->makeReport();

		$output->writeln('Your Report is saved at: ' . $path);




	}

}