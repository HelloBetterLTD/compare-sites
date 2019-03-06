<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 3/6/19
 * Time: 1:27 PM
 * To change this template use File | Settings | File Templates.
 */

namespace SilverStripers\CompareSites\Command;


use SilverStripers\CompareSites\Fetch\FetchPages;
use SilverStripers\CompareSites\Helper\Cache;
use SilverStripers\CompareSites\Report\ErrorPagesReport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckErrorPagesCommand extends Command
{

	protected static $defaultName = 'test-urls';

	protected function configure()
	{
		$this
			->addArgument('domain', InputArgument::REQUIRED, 'Domain you are testing')
			->addArgument('path', InputArgument::REQUIRED, 'Path to generate the report to')
			->addArgument('depth', InputArgument::OPTIONAL, 'Depth of links to check')
			->setDescription('Check for any pages with 500 errors.')
			->setHelp('php bin/console test-urls')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$domain = $input->getArgument('domain');
		$path = $input->getArgument('path');
		$depth = $input->getArgument('depth');
		Cache::set_domain($domain);

		try {
			$folder = Cache::set_report_path($path);
		} catch (\Exception $e) {
			$output->writeln($e->getMessage());
			return;
		}

		$output->writeln([
			'Starting',
			'============',
			'Generating and saving reports at ' . $folder
		]);

		$fetcher = new FetchPages($domain, $output);
		$fetcher->setType(FetchPages::SITE)->setDepth($depth)->run();

		$report = new ErrorPagesReport();
		$path = $report->makeReport();
		$output->writeln('Your Report is saved at: ' . $path);
	}

}