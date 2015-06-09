<?php
/**
 * Behat integration test bootstrapping. Largely a copy of Magento 2's bootstrap.php
 * for integration tests. Script initialize a Magento 2 test framework
 * application and perform some additional bootstrapping of the test framework -
 * setting up an object manager, configuration, logging, etc.
 */
use EbayEnterprise\TestFramework\Bootstrap\BehatDocBlock;
use Magento\Framework\App\Utility\Files as AppFilesUtility;
use Magento\Framework\Autoload\AutoloaderRegistry;
use Magento\Framework\Profiler\Driver\Standard as StandardProfilerDriver;
use Magento\Framework\Shell;
use Magento\Framework\Shell\CommandRenderer;
use Magento\TestFramework\Application;
use Magento\TestFramework\Bootstrap\Settings;
use Magento\TestFramework\Bootstrap;
use Magento\TestFramework\Bootstrap\Environment;
use Magento\TestFramework\Bootstrap\MemoryFactory;
use Magento\TestFramework\Bootstrap\Profiler;
use Magento\TestFramework\Helper\Bootstrap as BootstrapHelper;

echo 'Bootstrapping Magento 2 integration test framework' . PHP_EOL;

call_user_func(function () {
    // MAGENTO_ROOT_DIR should be absolute path to where the full M2 integration
    // environment has been installed.
    $magentoBaseDir = $_ENV['MAGENTO_ROOT_DIR'];
    // Path to M2 integration tests.
    $testsBaseDir = $magentoBaseDir . DIRECTORY_SEPARATOR . 'dev' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'integration';
    // Path to M2 integration test framework.
    $testFrameworkDir = $testsBaseDir . DIRECTORY_SEPARATOR . 'framework';
    // Temp directory for test related files - config, generated, cache, and
    // similar files.
    // @TODO Is this the best place for these files to go? Maybe a real tmp directory.
    $testsTmpDir = "{$testsBaseDir}/tmp";

    // Include the Magento 2 bootstrap - includes the M2 autoloader, translation
    // function, profiler and timezone setting.
    require_once $magentoBaseDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';
    // Include the M2 integration test framework's autoload file which adds autoload
    // paths for the test framework.
    require_once $testFrameworkDir . '/autoload.php';

    try {
        // Create a settings object that will be used to bootstrap the application.
        // The given directory will be the "root" directory when resolving paths
        // to configuration files. Second argument are key value pairs of configuration
        // values that can be looked up.
        //
        // Additional configuration options available for the Magento Test Framework.
        // Some or all of these additional configurations may or may not be useful
        // for Behat integration tests.
        //
        // TESTS_PROFILER_FILE
        //   => CSV file to write profiler information to
        // TESTS_MEM_USAGE_LIMIT
        //   => memory usage limit
        // TEST_MEM_LEAK_LIMIT
        //   => check for memory leaks
        // TESTS_ERROR_LOG_LISTENER_LEVEL
        //   => Minimum error log level to listen for. Possible values: -1 ignore all errors, and level constants form http://tools.ietf.org/html/rfc5424 standard
        // TESTS_EXTRA_VERBOSE_LOG
        //   => More verbose output
        $settings = new Settings(
            __DIR__,
            [
                // Local configuration for the DB to be used for running tests. Relative to
                // current directory.
                'TESTS_INSTALL_CONFIG_FILE' => 'config' . DIRECTORY_SEPARATOR . 'install-config-mysql.php',
                // Local XML configuration file ('.dist' extension will be added, if the specified file doesn't exist)
                // Relative to the current directory.
                'TESTS_GLOBAL_CONFIG_FILE' => 'config' . DIRECTORY_SEPARATOR . 'config-global.php',
                // Semicolon-separated 'glob' patterns, that match global XML configuration files.
                // Absolute path to config directory.
                'TESTS_GLOBAL_CONFIG_DIR' => $magentoBaseDir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'etc',
                // Whether to cleanup the application before running tests or not. 'enabled' will
                // perform an uninstall before running tests. Any other value will will skip the uninstall.
                'TESTS_CLEANUP' => 'disabled',
                // Magento mode for tests execution. Possible values are 'default', 'developer' and 'production'.
                'TESTS_MAGENTO_MODE' => 'developer',
            ]
        );

        // I think this is responsible for rendering test output. Not sure if it
        // is truly necessary with Behat tests or if this is really only needed for
        // Magento's integration tests.
        $shell = new Shell(new CommandRenderer());

        // Configuration files specifically for the testing context. The
        // installConfigFile contains configuration for connecting to the test
        // database and accessing the admin.
        $installConfigFile = $settings->getAsConfigFile('TESTS_INSTALL_CONFIG_FILE');
        if (!file_exists($installConfigFile)) {
            $installConfigFile .= '.dist';
        }
        // Additional Magento configuration for the testing context.
        // @TODO What configuration can be set this way?
        $globalConfigFile = $settings->getAsConfigFile('TESTS_GLOBAL_CONFIG_FILE');
        if (!file_exists($globalConfigFile)) {
            $globalConfigFile .= '.dist';
        }
        // Create a sandbox directory for configuration, generated, cache, session
        // and related files created for the test context.
        $sandboxUniqueId = md5(sha1_file($installConfigFile));
        $installDir = "{$testsTmpDir}/sandbox-{$settings->get('TESTS_PARALLEL_THREAD', 0)}-{$sandboxUniqueId}";
        // Create the application - M2 application context for the test run.
        $application = new Application(
            $shell,
            $installDir,
            $installConfigFile,
            $globalConfigFile,
            $settings->get('TESTS_GLOBAL_CONFIG_DIR'),
            $settings->get('TESTS_MAGENTO_MODE'),
            AutoloaderRegistry::getAutoloader()
        );

        // Create a bootstrapper which will do most of the work of setting up the
        // M2 environment for the application to run.
        $bootstrap = new Bootstrap(
            $settings,
            new Environment(),
            new BehatDocBlock("{$testsBaseDir}/testsuite"),
            new Profiler(new StandardProfilerDriver()),
            $shell,
            $application,
            new MemoryFactory($shell)
        );
        // Setup for environment HTTP and session emulation, profiling, memory
        // limits, and event registration (through a somewhat dubious side-effect
        // of DocBlock annotation registration).
        $bootstrap->runBootstrap();

        // cleanup and install ensure the M2 environment is clean before running
        // tests - uninstalls M2 and then reinstalls it using the test configuration.
        // @TODO This may need to be extended to also include any initial data needed
        // for the tests.
        if ($settings->getAsBoolean('TESTS_CLEANUP')) {
            $application->cleanup();
        }
        if (!$application->isInstalled()) {
            $application->install();
        }
        // Initialize the application. Largely responsible for setting up the
        // object manager instance - including updating relevant configuration for
        // the object manager to use test configuration - and replacing some
        // dependencies from the core Magento framework with alternatives from the
        // test framework.
        $application->initialize();

        BootstrapHelper::setInstance(new BootstrapHelper($bootstrap));

        AppFilesUtility::setInstance(new AppFilesUtility($magentoBaseDir));

    } catch (\Exception $e) {
        echo $e . PHP_EOL;
        exit(1);
    }
});
