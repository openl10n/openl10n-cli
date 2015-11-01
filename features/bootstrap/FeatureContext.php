<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;

class FeatureContext implements Context
{
    /**
     * @var int
     */
    private $commandExitCode;

    /**
     * @var string
     */
    private $commandOutput;

    /**
     * @var \Openl10n\Cli\Application
     */
    private $application;

    /**
     * @var \GuzzleHttp\Handler\MockHandler[]
     */
    private $guzzleMockHandlerList;

    /**
     * @var array
     */
    private $guzzleTransactionHistory = array();

    /**
     * @var string
     */
    private $workingDir;

    /**
     * Cleans test folders in the temporary directory.
     *
     * @BeforeSuite
     * @AfterSuite
     */
    public static function cleanTestFolders()
    {
        if (is_dir($dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'openl10n')) {
            self::clearDirectory($dir);
        }
    }

    /**
     * Prepares test folders in the temporary directory.
     *
     * @BeforeScenario
     */
    public function prepareTestFolders()
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'openl10n' . DIRECTORY_SEPARATOR .
            md5(microtime() * rand(0, 10000));

        mkdir($dir, 0777, true);

        $this->workingDir = $dir;
    }

    /**
     * @BeforeScenario
     */
    public function prepareApplication()
    {
        // Move to temporary working dir
        chdir($this->workingDir);

        $factory = new \Openl10n\Cli\ApplicationFactory();
        $this->application = $factory->createApplication();
        $this->application->setAutoExit(false);

    }

    /**
     * Creates a file with specified name and context in current workdir.
     *
     * @Given /^(?:there is )?a file named "([^"]*)" with:$/
     *
     * @param string       $filename name of the file (relative path)
     * @param PyStringNode $content  PyString string instance
     */
    public function aFileNamedWith($filename, PyStringNode $content)
    {
        $content = strtr((string) $content, array("'''" => '"""'));
        $this->createFile($this->workingDir . '/' . $filename, $content);
    }

    /**
     * Checks whether a file at provided path exists.
     *
     * @Given /^file "([^"]*)" should exist$/
     *
     * @param   string $path
     */
    public function fileShouldExist($path)
    {
        PHPUnit_Framework_Assert::assertFileExists($this->workingDir . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Runs openl10n command with provided parameters
     *
     * @When /^I run "openl10n(?: ((?:\"|[^"])*))?"$/
     *
     * @param string $argumentsString
     */
    public function iRunOpenl10n($argumentsString = '')
    {
        $argumentsString = strtr($argumentsString, array('\'' => '"'));

        $fh = tmpfile();
        $input = new \Symfony\Component\Console\Input\StringInput($argumentsString);
        $output = new \Symfony\Component\Console\Output\StreamOutput($fh);

        $this->commandExitCode = $this->application->run($input, $output);

        fseek($fh, 0);
        $output = '';
        while (!feof($fh)) {
            $output = fread($fh, 4096);
        }
        fclose($fh);
        $this->commandOutput = $output;
    }

    /**
     * Checks whether previously ran command passes|fails with provided output.
     *
     * @Then /^it should (fail|pass) with:$/
     *
     * @param string       $success "fail" or "pass"
     * @param PyStringNode $text    PyString text instance
     */
    public function itShouldPassWith($success, PyStringNode $text)
    {
        $this->itShouldFail($success);
        $this->theOutputShouldContain($text);
    }

    /**
     * Checks whether previously runned command passes|failes with no output.
     *
     * @Then /^it should (fail|pass) with no output$/
     *
     * @param string $success "fail" or "pass"
     */
    public function itShouldPassWithNoOutput($success)
    {
        $this->itShouldFail($success);
        PHPUnit_Framework_Assert::assertEmpty($this->getOutput());
    }

    /**
     * Checks whether specified file exists and contains specified string.
     *
     * @Then /^"([^"]*)" file should contain:$/
     *
     * @param string       $path file path
     * @param PyStringNode $text file content
     */
    public function fileShouldContain($path, PyStringNode $text)
    {
        $path = $this->workingDir . '/' . $path;
        PHPUnit_Framework_Assert::assertFileExists($path);
        $fileContent = trim(file_get_contents($path));
        // Normalize the line endings in the output
        if ("\n" !== PHP_EOL) {
            $fileContent = str_replace(PHP_EOL, "\n", $fileContent);
        }
        PHPUnit_Framework_Assert::assertEquals($this->getExpectedOutput($text), $fileContent);
    }
    /**
     * Checks whether specified content and structure of the xml is correct without worrying about layout.
     *
     * @Then /^"([^"]*)" file xml should be like:$/
     *
     * @param string       $path file path
     * @param PyStringNode $text file content
     */
    public function fileXmlShouldBeLike($path, PyStringNode $text)
    {
        $path = $this->workingDir . '/' . $path;
        PHPUnit_Framework_Assert::assertFileExists($path);
        $fileContent = trim(file_get_contents($path));
        $dom = new DOMDocument();
        $dom->loadXML($text);
        $dom->formatOutput = true;
        PHPUnit_Framework_Assert::assertEquals(trim($dom->saveXML(null, LIBXML_NOEMPTYTAG)), $fileContent);
    }

    /**
     * Checks whether last command output contains provided string.
     *
     * @Then the output should contain:
     *
     * @param PyStringNode $text PyString text instance
     */
    public function theOutputShouldContain(PyStringNode $text)
    {
        PHPUnit_Framework_Assert::assertContains($this->getExpectedOutput($text), $this->getOutput());
    }

    private function getExpectedOutput(PyStringNode $expectedText)
    {
        $text = strtr($expectedText, array('\'\'\'' => '"""', '%%TMP_DIR%%' => sys_get_temp_dir() . DIRECTORY_SEPARATOR));
        // windows path fix
        if ('/' !== DIRECTORY_SEPARATOR) {
            $text = preg_replace_callback(
                '/[ "]features\/[^\n "]+/', function ($matches) {
                return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
            }, $text
            );
            $text = preg_replace_callback(
                '/\<span class\="path"\>features\/[^\<]+/', function ($matches) {
                return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
            }, $text
            );
            $text = preg_replace_callback(
                '/\+[fd] [^ ]+/', function ($matches) {
                return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
            }, $text
            );
        }
        return $text;
    }

    /**
     * Checks whether previously ran command failed|passed.
     *
     * @Then /^it should (fail|pass)$/
     *
     * @param string $success "fail" or "pass"
     */
    public function itShouldFail($success)
    {
        if ('fail' === $success) {
            if (0 === $this->commandExitCode) {
                echo 'Actual output:' . PHP_EOL . PHP_EOL . $this->getOutput();
            }
            PHPUnit_Framework_Assert::assertNotEquals(0, $this->commandExitCode);
        } else {
            if (0 !== $this->commandExitCode) {
                echo 'Actual output:' . PHP_EOL . PHP_EOL . $this->getOutput();
            }
            PHPUnit_Framework_Assert::assertEquals(0, $this->commandExitCode);
        }
    }

    private function getOutput()
    {
        $output = $this->commandOutput;
        // Normalize the line endings in the output
        if ("\n" !== PHP_EOL) {
            $output = str_replace(PHP_EOL, "\n", $output);
        }
        // Replace wrong warning message of HHVM
        $output = str_replace('Notice: Undefined index: ', 'Notice: Undefined offset: ', $output);
        return trim(preg_replace("/ +$/m", '', $output));
    }

    private function createFile($filename, $content)
    {
        $path = dirname($filename);
        $this->createDirectory($path);
        file_put_contents($filename, $content);
    }

    private function createDirectory($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }

    private static function clearDirectory($path)
    {
        $files = scandir($path);
        array_shift($files);
        array_shift($files);
        foreach ($files as $file) {
            $file = $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($file)) {
                self::clearDirectory($file);
            } else {
                unlink($file);
            }
        }
        rmdir($path);
    }

    /**
     * @Given there is a openl10n server serving a :resource resource
     */
    public function thereIsAOpenl10nServerAtUri($resource)
    {
        $this->guzzleMockHandlerList[$resource] = new \GuzzleHttp\Handler\MockHandler();

        $handler = \GuzzleHttp\HandlerStack::create($this->guzzleMockHandlerList[$resource]);

        $this->guzzleTransactionHistory[$resource] = array();
        $handler->push(\GuzzleHttp\Middleware::history($this->guzzleTransactionHistory[$resource]));

        $client = new \GuzzleHttp\Client(['handler' => $handler]);

        $this->application
            ->getContainer()
            ->get('api')
            ->getEntryPoint($resource)
            ->setClient($client);
    }

    /**
     * @Given :method call to :resource on :uri will return :statusCode with:
     */
    public function httpCallToResourceWillReturnWith($method, $resource, $uri, $statusCode, PyStringNode $stringNode)
    {
        $this->guzzleMockHandlerList[$resource]->append(
            function($request, $options) use ($method, $uri, $statusCode, $stringNode) {

                PHPUnit_Framework_Assert::assertEquals($method, $request->getMethod());
                PHPUnit_Framework_Assert::assertEquals($uri, $request->getRequestTarget());

                return new \GuzzleHttp\Psr7\Response($statusCode, [], $stringNode->getRaw());
            }
        );
    }

    /**
     * @Given :method call to :resource on :uri will return :statusCode
     */
    public function httpCallToResourceWillReturn($method, $resource, $uri, $statusCode)
    {
        $this->guzzleMockHandlerList[$resource]->append(
            function($request, $options) use ($method, $uri, $statusCode) {

                PHPUnit_Framework_Assert::assertEquals($method, $request->getMethod());
                PHPUnit_Framework_Assert::assertEquals($uri, $request->getRequestTarget());

                return new \GuzzleHttp\Psr7\Response($statusCode, []);
            }
        );
    }

    /**
     * @Then I should have :method call to :resource on :uri with:
     */
    public function iShouldHaveMethodCallToResourceOnUriWith($method, $resource, $uri, PyStringNode $stringNode)
    {
        foreach ($this->guzzleTransactionHistory[$resource] as $key => $transaction) {

            if ($transaction['request']->getMethod() == $method && $transaction['request']->getRequestTarget() == $uri) {

                $reflectionProperty = new \ReflectionProperty($transaction['request'], 'stream');
                $reflectionProperty->setAccessible(true);
                $stream = $reflectionProperty->getValue($transaction['request']);

                PHPUnit_Framework_Assert::assertSame(
                    json_decode($stringNode->getRaw(), true),
                    json_decode($stream->read($stream->getSize()), true)
                );

                unset($this->guzzleTransactionHistory[$resource][$key]);
            }
        }
    }

    /**
     * @Then I should have :method call to :resource on :uri
     */
    public function iShouldHaveMethodCallToResourceOnUri($method, $resource, $uri)
    {
        $found = false;
        foreach ($this->guzzleTransactionHistory[$resource] as $key => $transaction) {

            if ($transaction['request']->getMethod() == $method && $transaction['request']->getRequestTarget() == $uri) {

                $found = true;

                unset($this->guzzleTransactionHistory[$resource][$key]);
            }
        }

        PHPUnit_Framework_Assert::assertTrue($found);
    }
}
