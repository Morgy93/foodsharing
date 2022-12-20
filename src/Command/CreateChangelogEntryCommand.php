<?php

namespace Foodsharing\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
	name: "foodsharing:changelog:create-entry",
	description: "Creates a changelog entry",
)]
final class CreateChangelogEntryCommand extends Command
{
	public function __construct()
	{
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$changelogDirectory = __DIR__.'/../../changelog/';
		$changelogConfig = Yaml::parseFile($changelogDirectory . 'config.yaml');
		$changelogDirectoryForNewEntries = $changelogDirectory . $changelogConfig['changelog_subdirectory_for_new_entries'] . '/';
		$helper = $this->getHelper('question');
		$typesOfChanges = $changelogConfig['types_of_change'] ?? [];

		$descriptionQuestion = new Question('<question>Describe your change</question>'.PHP_EOL, '');
		$description = $helper->ask($input, $output, $descriptionQuestion);

		$typeOfChangeQuestionText = '<question>Select the type(s) of change</question>'.PHP_EOL.
									'<comment>Multiselect allowed | Example: 1,2</comment>'.PHP_EOL;
		$typeOfChangeQuestion = new ChoiceQuestion(
			$typeOfChangeQuestionText,
			$typesOfChanges,
			'uncategorized'
		);
		$typeOfChangeQuestion->setMultiselect(true);
		$typeOfChange = $helper->ask($input, $output, $typeOfChangeQuestion);

		$mergeRequestIdsQuestionText = '<question>Enter merge request id(s)</question>'
			.PHP_EOL.'<comment>Without ! | Multiple allowed | Example: 1234 OR 1234,5543</comment>'.PHP_EOL;

		$mergeRequestIdsQuestion = new Question($mergeRequestIdsQuestionText, '');
		$mergeRequestIds = $helper->ask($input, $output, $mergeRequestIdsQuestion);
		$mergeRequestIds = explode(',', $mergeRequestIds);

		$issueIdsQuestionText = '<question>Enter gitlab issue-id(s)</question>'
			.PHP_EOL.'<comment>Without # | Multiple allowed | Example: 4532 OR 3452,3222</comment>'.PHP_EOL;

		$issueIdsQuestion = new Question($issueIdsQuestionText, '');
		$issueIds = $helper->ask($input, $output, $issueIdsQuestion);
		$issueIds = explode(',', $issueIds);

		$authorsQuestionText = '<question>Enter gitlab username(s) of authors</question>'
			.PHP_EOL.'<comment>Without @ | Multiple allowed | Example: martincodes-de OR martincodes-de,chriswalg</comment>'.PHP_EOL;

		$authorsQuestion = new Question($authorsQuestionText, '');
		$authors = $helper->ask($input, $output, $authorsQuestion);
		$authors = explode(',', $authors);

		$fileNameQuestionText = '<question>Enter short filename for this entry</question>'
			.PHP_EOL.'<comment>do not use spaces | Example: added-changelog-entry-command</comment>'.PHP_EOL;
		$fileNameQuestion = new Question($fileNameQuestionText, '');
		$fileNameQuestion->setValidator(function ($filename) use ($changelogDirectoryForNewEntries) {
			if (empty($filename)) {
				throw new RuntimeException('Filename must not be empty.');
			}

			$lowercaseFilename = strtolower($filename);
			$filenameWithExtension =  $lowercaseFilename . '.yaml';
			if (file_exists($changelogDirectoryForNewEntries . $filenameWithExtension)) {
				throw new RuntimeException($filenameWithExtension . ' exists already inside ' . $changelogDirectoryForNewEntries);
			}

			return $filenameWithExtension;
		});
		$fileName = $helper->ask($input, $output, $fileNameQuestion);
		$fileContent = Yaml::dump([
			'description' => $description,
			'types_of_change' => $typeOfChange,
			'merge_request_ids' => $mergeRequestIds,
			'issue_ids' => $issueIds,
			'authors' => $authors,
		]);

		$pathForFile = $changelogDirectoryForNewEntries . $fileName;

		file_put_contents($pathForFile, $fileContent);

		$formatter = $this->getHelper('formatter');
		$fileCreatedMessages = ['Changelog Entry File created!', 'It is saved here: ' . $pathForFile];
		$formattedBlock = $formatter->formatBlock($fileCreatedMessages, 'info', true);
		$output->writeln($formattedBlock);

		return Command::SUCCESS;
	}
}
