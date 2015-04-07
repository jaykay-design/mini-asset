<?php
namespace MiniAsset\Test\Cli;

use MiniAsset\AssetConfig;
use MiniAsset\Cli\BuildTask;

class BuildTaskTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $cli = $this->getMock('League\CLImate\CLImate', ['usage', 'out', 'err']);
        $cli->expects($this->any())
            ->method('out')
            ->will($this->returnSelf());

        $config = AssetConfig::buildFromIniFile(
            APP . 'config/integration.ini',
            ['WEBROOT' => TMP]
        );
        $this->task = new BuildTask($cli, $config);
        $this->cli = $cli;

        mkdir(TMP . 'cache_js');
        mkdir(TMP . 'cache_css');
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->rmdir(TMP . 'cache_js');
        $this->rmdir(TMP . 'cache_css');
    }

    /**
     * Helper to clean up directories.
     *
     * @param string $path The path to remove files from.
     * @return void
     */
    protected function rmdir($path)
    {
        $d = dir($path);
        while (($entry = $d->read()) !== false) {
            if (is_file($path . '/' . $entry)) {
                unlink($path . '/' . $entry);
            }
        }
        rmdir($path);
    }

    /**
     * Ensure that help shows.
     */
    public function testMainShowsHelp()
    {
        $this->cli->expects($this->once())
            ->method('usage');
        $result = $this->task->main(['build', '--help']);
        $this->assertSame(0, $result);
    }

    /**
     * Ensure that files are built.
     */
    public function testMainBuildsFiles()
    {
        $this->cli->expects($this->never())
            ->method('usage');

        $result = $this->task->main(['build', '--config', APP . 'config/integration.ini']);
        $this->assertSame(0, $result, 'Exit is bad.');
        $this->assertTrue(file_exists(TMP . 'cache_css' . DS . 'all.css'), 'Css build missing');
        $this->assertTrue(file_exists(TMP . 'cache_js' . DS . 'libs.js'), 'Js build missing');
        $this->assertTrue(file_exists(TMP . 'cache_js' . DS . 'foo.bar.js'), 'Js build missing');
    }
}
