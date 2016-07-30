<?php namespace Nine\Loaders;

use Nine\Loaders\Exceptions\ConfigurationFileNotFound;
use Nine\Loaders\Exceptions\InvalidConfigurationImportValueException;
use Nine\Loaders\Exceptions\InvalidConfigurationPathException;
use Nine\Loaders\Exceptions\KeyDoesNotExistException;
use Nine\Loaders\Exceptions\UnsupportedUseOfArrayAccessMethod;

/**
 * Test the Collection Class
 *
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class ConfigFileReaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var ConfigFileReader */
    protected $reader;

    public function setUp()
    {
        $this->reader = new ConfigFileReader(CONFIG);
    }

    public function tearDown()
    {
        $this->reader->clearCache();
    }

    public function test_bad_assignment()
    {
        // using the writer to mutate the config should work
        $writer = new ConfigFileWriter($this->reader);
        $writer['special-key'] = ['key' => 'special'];
        static::assertArrayHasKey('special-key', $writer);

        // attempting to write to the reader will fail
        $this->expectException(UnsupportedUseOfArrayAccessMethod::class);
        $this->reader['test'] = 'this will fail.';
    }

    public function test_basePath()
    {
        // bogus path
        $this->reader->setBasePath('bogus/path');

        // with a bad path, readConfig will fail.
        $this->expectException(InvalidConfigurationPathException::class);
        $this->reader->readConfig('auth');
    }

    public function test_existence()
    {
        static::assertArrayHasKey('enabled', $this->reader['view.twig']);
        static::assertArrayNotHasKey('not-a-key', $this->reader['view.twig']);
        static::assertTrue(is_array($this->reader->read('view')));

        $this->expectException(KeyDoesNotExistException::class);
        static::assertFalse($this->reader['nope']);
    }

    public function test_exporting()
    {
        // create and preload a collection of configurations.
        $reader = (new ConfigFileReader(CONFIG))->preloadPath();

        // create the writer containing pre-loaded configurations.
        $writer = new ConfigFileWriter($reader);

        // modify the configuration (both methods are equivalent.)
        ## $writer->view_markdown_defaults_debug = false;
        static::assertTrue($writer['view.markdown.defaults.debug']);
        $writer['view.markdown.defaults.debug'] = false;
        static::assertFalse($writer['view.markdown.defaults.debug']);
        static::assertFalse($writer['view']['markdown']['defaults']['debug']);

        // extract an outer key array and write it to a new file
        $writer->exportPhpFile(
        // the path to where the new file will be written
            CONFIG . '../temp/',
            // the outer key to extract and write (ie: ['view'] -> 'CONFIG/view.php')
            // optionally, this can be set to '*' to export the entire cache.
            'view',
            // optionally, force the filename to something other than the key
            'production.php'
        );

        $tester = new ConfigFileReader(CONFIG . '../temp/');
        $tester->read('production');

        static::assertFalse($tester['production.markdown.defaults.debug']);

        unlink(CONFIG . '../temp/production.php');
    }

    public function test_forget()
    {
        $writer = new ConfigFileWriter($this->reader->preloadPath());

        $writer['unhinged'] = ['unhinged' => true];
        unset($writer['unhinged']);
        static::assertFalse($this->reader->cached('unhinged'));
    }

    public function test_getCache()
    {
        static::assertEmpty($this->reader->getCache());

        // defaults to all .php files in the path folder.
        $this->reader->preloadPath();

        // build a list of the files preloadPath must have accessed.
        $keys = glob($this->reader->getBasePath() . '*.php');

        // the count should be the same
        static::assertCount(count($keys), $this->reader->getCache());
    }

    public function test_imports_part_1()
    {
        $this->reader->readConfig('config.json');
        static::assertCount(4, $this->reader->getCache());

        $this->reader->clearCache();

        $this->reader->readConfig('config.yml');
        static::assertCount(4, $this->reader);

        // reset and set up to read a bad config file.
        $this->reader->clearCache();
        $this->reader->setBasePath(ROOT . 'tests/src/temp/');

        $this->expectException(InvalidConfigurationImportValueException::class);
        $this->reader->readConfig('bad_config.php');
    }

    public function test_imports_part_2()
    {
        // first preload
        $this->reader->preloadPath();
        static::assertCount(9, $this->reader->getCache());

        // any subsequent calls should not attempt to make duplicate keys,
        // so this should succeed without an error.
        $this->reader->preloadPath();
        // and the count should remain the same.
        static::assertCount(9, $this->reader->getCache());
    }

    public function test_preload()
    {
        $this->reader->preloadPath();

        // count the php files in the basePath folder.
        // each filename becomes a key in the cache.
        $keys = count(glob($this->reader->getBasePath() . '*.php'));

        static::assertCount($keys, $this->reader);
        static::assertArrayHasKey('app', $this->reader);
        static::assertArrayHasKey('view', $this->reader);

        static::assertTrue($this->reader->has('view.blade'));

        $this->reader->clearCache();
        $this->expectException(InvalidConfigurationPathException::class);
        $this->reader->preloadPath('bad-path');
    }

    public function test_read()
    {
        // load the auth config file
        $this->reader->read('auth');

        // there should now be only one key in the reader
        static::assertCount(1, $this->reader);

        $this->reader->readMany(['app','view']);
        static::assertCount(3, $this->reader);

        // bad file key
        $this->expectException(ConfigurationFileNotFound::class);
        $this->reader->read('pants');
    }

    public function test_readConfig()
    {
        // verify that the key is not currently cached.
        static::assertFalse($this->reader->cached('auth'));

        // this should succeed
        $this->reader->readConfig('auth');
        // ... and should now be cached.
        static::assertTrue($this->reader->cached('auth'));

        // this will too but should hit the cache for the test
        $this->reader->readConfig('auth');

        $this->expectException(InvalidConfigurationPathException::class);
        $this->reader->readConfig('bad-file');
    }

}
