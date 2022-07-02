<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('docker')
    ->exclude('lang')
    ->exclude('fonts')
    ->exclude('chat')
    ->exclude('images')
    ->exclude('light')
    ->exclude('scripts')
    ->exclude('js')
    ->exclude('vendor')
    ->notPath('tmp')
    ->notPath('lib/font')
    ->notPath('tests/_support/_generated')
    ->notPath('src/Lib/Flourish')
    ->notPath('cache')
    ->notPath('client')
    ->in(__DIR__)
;

$config = new PhpCsFixer\Config();
$config->setRules([
	'@Symfony' => true,
	'concat_space' => ['spacing' => 'one'],
	'cast_spaces' => ['space' => 'none'],
	'phpdoc_align' => ['tags' => []],
	'trailing_comma_in_multiline' => false,
	'yoda_style' => [
		'equal' => null,
		'identical' => null,
	],
	'single_line_comment_spacing' => [],
])
	->setIndent("\t")
	->setFinder($finder);
return $config;
?>



