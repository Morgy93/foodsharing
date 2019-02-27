<?php

class SanitizerTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var \Foodsharing\Services\SanitizerService
	 */
	private $sanitizer;

	protected function _before()
	{
		$this->sanitizer = $this->tester->get(\Foodsharing\Services\SanitizerService::class);
	}

	protected function _after()
	{
	}

	// tests
	public function testPlainToHtmlEncodesTags()
	{
		$in = 'Hi<there>, you <b>keep this</b>?';
		$out = $this->sanitizer->plainToHtml($in);
		$this->assertEquals(
			'Hi&lt;there&gt;, you &lt;b&gt;keep this&lt;/b&gt;?',
			$out
		);
	}

	public function testPurifyHtmlStripsScriptTag()
	{
		$in = '<p>This should stay</p><script type="text/javascript">alert()</script><p>And this is last</p>';
		$out = $this->sanitizer->purifyHtml($in);
		$this->assertEquals(
			'<p>This should stay</p><p>And this is last</p>',
			$out
		);
	}

	public function testMarkdownToHtmlEncodesTags()
	{
		$in = 'Hi<there>, you <b>keep this</b>?';
		$out = $this->sanitizer->markdownToHtml($in);
		$this->assertEquals(
			'<p>Hi&lt;there&gt;, you &lt;b&gt;keep this&lt;/b&gt;?</p>',
			$out
		);
	}

	public function testMarkdownToHtmlHandlesNewline()
	{
		$in = "Hi\nthere";
		$out = $this->sanitizer->markdownToHtml($in);
		$this->assertContains(
			'Hi<br />',
			$out
		);
		/* We do not want to specify if it keeps newline or not, but we want to have a break in the output. */
		$this->assertContains(
			'there',
			$out
		);
	}

	public function testHtmlToPlainConvertsNewline()
	{
		$in = 'Hi<br />there';
		$out = $this->sanitizer->htmlToPlain($in);
		$this->assertEquals(
			"Hi\nthere",
			$out
		);
	}

	public function testMarkdownRendersSimpleList()
	{
		$in = "* Hi\n* there";
		$out = $this->sanitizer->markdownToHtml($in);
		$this->assertContains(
			'<li>Hi</li>',
			$out
		);
	}
}
