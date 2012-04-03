<?php

class Miffie
{

    /**
     * @var bool
     */
    //protected $_clientLoaded = false;
    
    /**
     * @var string
     */
    protected $_mode = 'runCLI';
    
    /**
     * @var array of messages
     */
    protected $_messages = array();
    
    /**
     * @var string
     */
    protected $_homeDirectory = null;
    
    /**
     * @var string
     */
    protected $_storageDirectory = null;
    
    /**
     * @var string
     */
    protected $_configFile = null;
    
    /**
     * main()
     * 
     * @return void
     */
    public static function main()
    {
        $miffie = new self();
        $miffie->bootstrap();
        $miffie->run();
    }
    
    /**
     * bootstrap()
     * 
     * @return MIFFIE
     */
    public function bootstrap()
    {
        // detect settings
        $this->_mode             = $this->_detectMode();
        $this->_homeDirectory    = $this->_detectHomeDirectory();
        //$this->_storageDirectory = $this->_detectStorageDirectory();
        $this->_configFile       = $this->_detectConfigFile();
        
        // setup
        $this->_setupPHPRuntime();
        //$this->_setupToolRuntime();
    }
    
    /**
     * run()
     * 
     * @return Miffie
     */
    public function run()
    {
        switch ($this->_mode) {
            case 'runError':
                $this->_runError();
                $this->_runInfo();
                break;
            case 'runSetup':
                if ($this->_runSetup() === false) {
                    $this->_runInfo();
                }
                break;
            case 'runInfo':
                $this->_runInfo();
                break;
            case 'runCLI':
            default:
                $this->_runCLI();
                break;
        }
        
        return $this;
    }
    
    /**
     * _detectMode()
     * 
     * @return Miffie
     */
    protected function _detectMode()
    {
        $arguments = $_SERVER['argv'];

        $mode = 'runCLI';
        
        if (!isset($arguments[0])) {
            return $mode;
        }
        
        if ($arguments[0] == $_SERVER['PHP_SELF']) {
            $this->_executable = array_shift($arguments);
        }
        
        if (!isset($arguments[0])) {
            return $mode;
        }
        
        if ($arguments[0] == '--setup') {
            $mode = 'runSetup';
        } elseif ($arguments[0] == '--info') {
            $mode = 'runInfo';
        } 
        
        return $mode;
    }
    

    /**
     * _detectHomeDirectory() - detect the home directory in a variety of different places
     * 
     * @param $mustExist Should the returned value already exist in the file system
     * @param $returnMessages Should it log messages for output later
     * @return string
     */
    protected function _detectHomeDirectory($mustExist = true, $returnMessages = true)
    {
        $homeDirectory = null;
        
        $homeDirectory = getenv('MIFFIE_HOME'); // check env var MIFFIE_HOME
        if ($homeDirectory) {
            $this->_logMessage('Home directory found in environment variable MIFFIE_HOME with value ' . $homeDirectory, $returnMessages);
            if (!$mustExist || ($mustExist && file_exists($homeDirectory))) {
                return $homeDirectory;
            } else {
                $this->_logMessage('Home directory does not exist at ' . $homeDirectory, $returnMessages);
            }
        }
        
        $homeDirectory = getenv('HOME'); // HOME environment variable
        
        if ($homeDirectory) {
            $this->_logMessage('Home directory found in environment variable HOME with value ' . $homeDirectory, $returnMessages);
            if (!$mustExist || ($mustExist && file_exists($homeDirectory))) {
                return $homeDirectory;
            } else {
                $this->_logMessage('Home directory does not exist at ' . $homeDirectory, $returnMessages);
            }
            
        }

        $homeDirectory = getenv('HOMEPATH');
            
        if ($homeDirectory) {
            $this->_logMessage('Home directory found in environment variable HOMEPATH with value ' . $homeDirectory, $returnMessages);
            if (!$mustExist || ($mustExist && file_exists($homeDirectory))) {
                return $homeDirectory;
            } else {
                $this->_logMessage('Home directory does not exist at ' . $homeDirectory, $returnMessages);
            }
        }

        $homeDirectory = getenv('USERPROFILE');
        
        if ($homeDirectory) {
            $this->_logMessage('Home directory found in environment variable USERPROFILE with value ' . $homeDirectory, $returnMessages);
            if (!$mustExist || ($mustExist && file_exists($homeDirectory))) {
                return $homeDirectory;
            } else {
                $this->_logMessage('Home directory does not exist at ' . $homeDirectory, $returnMessages);
            }
        }
        
        return false;
    }
    
    /**
     * _detectStorageDirectory() - Detect where the storage directory is from a variaty of possiblities
     * 
     * @param $mustExist Should the returned value already exist in the file system
     * @param $returnMessages Should it log messages for output later
     * @return string
     */
    protected function _detectStorageDirectory($mustExist = true, $returnMessages = true)
    {
        $storageDirectory = false;
        
        $storageDirectory = getenv('MIFFIE_STORAGE_DIR');
        if ($storageDirectory) {
            $this->_logMessage('Storage directory path found in environment variable MIFFIE_STORAGE_DIR with value ' . $storageDirectory, $returnMessages);
            if (!$mustExist || ($mustExist && file_exists($storageDirectory))) {
                return $storageDirectory;
            } else {
                $this->_logMessage('Storage directory does not exist at ' . $storageDirectory, $returnMessages);
            }
        }
        
        $homeDirectory = ($this->_homeDirectory) ? $this->_homeDirectory : $this->_detectHomeDirectory(true, false); 
        
        if ($homeDirectory) {
            $storageDirectory = $homeDirectory . '/.miffie/';
            $this->_logMessage('Storage directory assumed in home directory at location ' . $storageDirectory, $returnMessages);
            if (!$mustExist || ($mustExist && file_exists($storageDirectory))) {
                return $storageDirectory;
            } else {
                $this->_logMessage('Storage directory does not exist at ' . $storageDirectory, $returnMessages);
            }
        }
        
        return false;
    }
    
    /**
     * _detectConfigFile() - Detect config file location from a variety of possibilities
     * 
     * @param $mustExist Should the returned value already exist in the file system
     * @param $returnMessages Should it log messages for output later
     * @return string
     */
    protected function _detectConfigFile($mustExist = true, $returnMessages = true)
    {
        $configFile = null;

        /**
        $currentDirectory = getcwd(); // todo currentDiretory ignore option?
        if ($currentDirectory) {
            $configFile = $currentDirectory . '/miffie.php';
            $this->_logMessage('Config file assumed in current directory at location ' . $configFile, $returnMessages);
            if (!$mustExist || ($mustExist && file_exists($configFile))) {
                return $configFile;
            } else {
                $this->_logMessage('Config file does not exist at ' . $configFile, $returnMessages);
            }
        }*/
        
        $configFile = getenv('MIFFIE_CONFIG_FILE');
        if ($configFile) {
            $this->_logMessage('Config file found environment variable MIFFIE_CONFIG_FILE at ' . $configFile, $returnMessages);
            if (!$mustExist || ($mustExist && file_exists($configFile))) {
                return $configFile;
            } else {
                $this->_logMessage('Config file does not exist at ' . $configFile, $returnMessages);
            }
        }
        
        $homeDirectory = ($this->_homeDirectory) ? $this->_homeDirectory : $this->_detectHomeDirectory(true, false);
        if ($homeDirectory) {
            $configFile = $homeDirectory . '/.miffie.php';
            $this->_logMessage('Config file assumed in home directory at location ' . $configFile, $returnMessages);
            if (!$mustExist || ($mustExist && file_exists($configFile))) {
                return $configFile;
            } else {
                $this->_logMessage('Config file does not exist at ' . $configFile, $returnMessages);
            }
        }
        
        /**
        $storageDirectory = ($this->_storageDirectory) ? $this->_storageDirectory : $this->_detectStorageDirectory(true, false);
        if ($storageDirectory) {
            $configFile = $storageDirectory . '/miffie.php';
            $this->_logMessage('Config file assumed in storage directory at location ' . $configFile, $returnMessages);
            if (!$mustExist || ($mustExist && file_exists($configFile))) {
                return $configFile;
            } else {
                $this->_logMessage('Config file does not exist at ' . $configFile, $returnMessages);
            }
        }*/
        
        return false;
    }
    

    /**
     * _setupPHPRuntime() - parse the config file if it exists for php ini values to set
     * 
     * @return void
     */
    protected function _setupPHPRuntime()
    {
        // set php runtime settings
        ini_set('display_errors', true);

        // support the changing of the current working directory, necessary for some providers
        if (isset($_ENV['MIFFIE_CURRENT_WORKING_DIRECTORY'])) {
            chdir($_ENV['MIFFIE_CURRENT_WORKING_DIRECTORY']);
        }
        
        if (!$this->_configFile) {
            return;
        }
        $miffie_php_settings = include($this->_configFile);
        $phpINISettings = ini_get_all();
        foreach ($miffie_php_settings as $MIFFIEINIKey => $MIFFIEINIValue) {
            if (substr($MIFFIEINIKey, 0, 4) === 'php.') {
                $phpINIKey = substr($MIFFIEINIKey, 4); 
                if (array_key_exists($phpINIKey, $phpINISettings)) {
                    ini_set($phpINIKey, $MIFFIEINIValue);
                }
            }
        }

        return null;
    }

    
    
    /**
     * _runError() - Output the error screen that tells the user that the tool was not setup
     * in a sane way
     * 
     * @return void
     */
    protected function _runError()
    {
        
        echo <<<EOS
    
***************************** MIFFIE ERROR ********************************
In order to run the MIFFIE command, you need to ensure that Zend Framework
is inside your include_path.  There are a variety of ways that you can
ensure that this MIFFIE command line tool knows where the Zend Framework
library is on your system, but not all of them can be described here.

The easiest way to get the MIFFIE command running is to allow is to give it
the include path via an environment variable MIFFIE_INCLUDE_PATH or
MIFFIE_INCLUDE_PATH_PREPEND with the proper include path to use,
then run the command "miffie --setup".  This command is designed to create
a storage location for your user, as well as create the miffie.php file
that the MIFFIE command will consult in order to run properly on your
system.  

Example you would run:

$ MIFFIE_INCLUDE_PATH=/path/to/library miffie --setup

Your are encourged to read more in the link that follows.

EOS;

        return null;
    }

    /**
     * _runInfo() - this command will produce information about the setup of this script and
     * Zend_Tool
     * 
     * @return void
     */
    protected function _runInfo()
    {
        echo 'MIFFIE & CLI Setup Information' . PHP_EOL
           . '(available via the command line "miffie --info")' 
           . PHP_EOL;
        
        echo '   * ' . implode(PHP_EOL . '   * ', $this->_messages) . PHP_EOL;
        
        echo PHP_EOL;
        
        echo 'To change the setup of this tool, run: "miffie --setup"';
           
        echo PHP_EOL;

    }

    
    /**
     * _runSetup() - parse the request to see which setup command to run
     * 
     * @return void
     */
    protected function _runSetup()
    {
        $setupCommand = (isset($_SERVER['argv'][2])) ? $_SERVER['argv'][2] : null;
        
        switch ($setupCommand) {
            case 'storage-directory':
                $this->_runSetupStorageDirectory();
                break;
            case 'config-file':
                $this->_runSetupConfigFile();
                break;
            case 'autopagerize':
                $this->_runSetupAutopagerize();
                break;
            default:
                $this->_runSetupMoreInfo();
                break;
        }
        
        return null;
    }

    /**
     * _runSetupStorageDirectory() - if the storage directory does not exist, create it
     * 
     * @return void
     */
    protected function _runSetupStorageDirectory()
    {
        $storageDirectory = $this->_detectStorageDirectory(false, false);
        
        if (file_exists($storageDirectory)) {
            echo 'Directory already exists at ' . $storageDirectory . PHP_EOL
               . 'Cannot create storage directory.';
            return;
        }
        
        mkdir($storageDirectory);
        
        echo 'Storage directory created at ' . $storageDirectory . PHP_EOL;
    }

    /**
     * _runSetupConfigFile()
     * 
     * @return void
     */
    protected function _runSetupConfigFile()
    {
        $configFile = $this->_detectConfigFile(false, false);
        
        if (file_exists($configFile)) {
            echo 'File already exists at ' . $configFile . PHP_EOL
               . 'Cannot write new config file.';
            return;
        }
        
        $includePath = get_include_path();
        
        $contents = <<<EOF
<?php
\$config = array(
    // 'php' => array( // php.ini setting
    //     'include_path' => 
    // );
    //
    // 'logging' => array(
    // ),
    //
    // 'autopagerize' => array(
    // ),
    //
    //
    // 'di'  => array();
);

return \$config;

EOF;
        
        file_put_contents($configFile, $contents);
        
        $iniValues = ini_get_all();
        if ($iniValues['include_path']['global_value'] != $iniValues['include_path']['local_value']) {
            echo 'NOTE: the php include_path to be used with the tool has been written' . PHP_EOL
               . 'to the config file, using MIFFIE_INCLUDE_PATH (or other include_path setters)' . PHP_EOL
               . 'is no longer necessary.' . PHP_EOL . PHP_EOL;
        }
        
        echo 'Config file written to ' . $configFile . PHP_EOL;
        
        return null;
    }

    /**
     * _runSetupMoreInfo() - return more information about what can be setup, and what is setup
     * 
     * @return void
     */
    protected function _runSetupMoreInfo()
    {
        $homeDirectory    = $this->_detectHomeDirectory(false, false);
        //$storageDirectory = $this->_detectStorageDirectory(false, false);
        $configFile       = $this->_detectConfigFile(false, false);
        
        echo <<<EOS

miffie Command Line Tool - Setup
----------------------------
        
Current Paths (Existing or not):
    Home Directory: {$homeDirectory}
    Config File: {$configFile}

Important Environment Variables:
    MIFFIE_HOME 
        - the directory this tool will look for a home directory
    MIFFIE_CONFIG_FILE 
        - where this tool will look for a configuration file
    MIFFIE_INCLUDE_PATH
        - set the include_path for this tool to use this value
    MIFFIE_INCLUDE_PATH_PREPEND
        - prepend the current php.ini include_path with this value
    
Search Order:
    Home Directory:
        - MIFFIE_HOME, then HOME (*nix), then HOMEPATH (windows)
    Config File:
        - MIFFIE_CONFIG_FILE, then {home}/.miffie.php, then {home}/miffie.php,
          then {storage}/miffie.php

Commands:
    miffie --setup config-file
        - create the config file with some default values
    miffie --setup autopagerize
        - setup autopagerize data from wedata
    miffie --setup directory
        - setup the storage directory, directory will be created

EOS;
    }

    protected function _runSetupAutopagerize()
    {
        if (!isset($this->_configFile) || !$this->_configFile) {
            echo '**setup autopagerize require Config file**', PHP_EOL;
            $this->_runSetupMoreInfo();
            return;
        }

        $runner = new Miffie\Runner;

        $config = Zend\Config\Factory::fromFile($this->_configFile, true);
        $bootstrap = new Miffie\Bootstrap($config);
        $bootstrap->bootstrap($runner);
        $locator = $runner->getLocator();

        $client = $locator->get('client');
        //$client->setUri('http://example.com');

        $wedataApi = $locator->get('wedata-api');
        $wedataStorage = $locator->get('wedata-storage');

        var_dump($wedataStorage);
        //$wedataApi->setHttpClient($locator->get('client'));

        return;

        $items = $wedataApi->getItems('AutoPagerize', null); //get all items
        $wedataStorage->storeItems('AutoPagerize', $items);
    }
    
    /**
     * _runCLI() - This is where the magic happens, dispatch Zend_Tool
     * 
     * @return void
     */
    protected function _runCLI()
    {
        $runner = new Miffie\Runner;
        if (isset($this->_configFile) && $this->_configFile) {
            $config = Zend\Config\Factory::fromFile($this->_configFile, true);
            $bootstrap = new Miffie\Bootstrap($config);
            $bootstrap->bootstrap($runner);
        }
        $runner->run();
    }

    /**
     * @deprecated
     */
    protected function _bootstrapRunner($runner)
    {
        $configOptions = array();
        if (isset($this->_configFile) && $this->_configFile) {
            $configOptions['configOptions']['configFilepath'] = $this->_configFile;
        }
        if (isset($this->_storageDirectory) && $this->_storageDirectory) {
            $configOptions['storageOptions']['directory'] = $this->_storageDirectory;
        }

        if (!$this->_configFile) {
            return;
        }

        $config = Zend\Config\Factory::fromFile($this->_configFile, true);
        $bootstrap = new Miffie\Bootstrap($config);
        $bootstrap->bootstrap($runner);
    }

    /**
     * _logMessage() - Internal method used to log setup and information messages.
     * 
     * @param $message
     * @param $storeMessage
     * @return void
     */
    protected function _logMessage($message, $storeMessage = true)
    {
        if (!$storeMessage) {
            return;
        }
        
        $this->_messages[] = $message;
    }
}

if (!getenv('MIFFIE_NO_MAIN')) {
    Miffie::main();
}

