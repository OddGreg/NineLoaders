# Container-Agnostic Configuration Classes

This repository is a collection of classes that manage configurations. Internally, the loader and configuration sets do not use nor require a dependency injector or service locator. A container may be passed into the LoaderSet, which passes it through the ConfigurationSet and on to the individual Configurators. The Configurator then populates the container as required.

The main repository classes are:

1. `LoaderSet` -- A set of Configuration Sets.
1. `ConfigurationSet` -- A set of Configurators.
1. `Configurator` -- A configurator.
1. `ConfigFileReader` and `ConfigFileWriter` -- Configuration File Reading and Writing.

## Quick Overview

The diagram below reveals the class hierarchy of the core classes.  

![Relationship Diagram](http://horsedragon.ca/share/Relationship_Diagram_3.png)

* A `LoaderSet` is a collection of `ConfigurationSet` objects. Each LoaderSet may pass a single container reference to a `ConfigurationSet`. Each loader set manages and initiates loading and applying configuration sets. 

> Subclass the LoaderSet class if you need a specific identifier (ie: `AurynLoaderSet`) or need to further initialize the environment. 

* A `ConfigurationSet` is a collection of `Configurator` objects. Each collection set manages inserting, loading and applying `Configurator` objects. 

> The `ConfigurationSet` class handles the insertion, selection, loading and applying of the `Configurators` it contains.

* A `Configurator` class handles the configuration of a single scope. i.e.: a `TwigConfigurator` may handle the configuration required for __Twig__ while `DatabaseConfigurator` or `MiddlewareConfigurator` and so on would handle their respective configuration scopes.

* `ConfigFileReader` and `ConfigFileWriter` handle the reading and writing of configuration files. Files may be in `.php`, `.yml` or `.json` format. XML is not supported out of the box.

> In some cases you may only require the `ConfigFileReader` for managing access to file-based configuration values.  
 
#### Example Configuration Settings file.
 
```php
return [
    // example use of a subclassed configuration set
    // which handles its own configurators etc. or
    // it may do something entirely different.
    ExampleConfigurationSet::class => [
        'name'        => 'example.config',
        'config_path' => __DIR__ . '/../config/examples/',
        'priority'    => 'high',
    ],
    // example of a labeled ConfigurationSet
    // note that the label isn't significant. The name
    // parameter defines the name given to the set.
    'views'                        => [
        'name'        => 'example.views',
        'config_path' => __DIR__ . '/../config/',
        'priority'    => 'low',
        'config'      => [
            BladeConfigurator::class    => ['name' => 'blade', 'dataset' => 'view.blade',],
            TwigConfigurator::class     => ['name' => 'twig', 'dataset' => 'view.twig'],
            MarkdownConfigurator::class => ['name' => 'markdown', 'dataset' => 'view.markdown'],
        ],
    ],
];
```

## Configuration File Handling

The simplest of the classes deal with reading, accessing and writing directory-located config files.

| Class | Description |
|-------|-------------|
| `ConfigFileReader` | Just-in-time config file reading with compound key (so-called 'dot' notation) access. |
| `ConfigFileWriter` | Using a `ConfigFileReader` as a dependency, this class allows making changes to the configuration and then writing to update directory-located config files. |
 
 

## Configuration and Loader Classes.

The **Nine\Loaders** package provides a method for configuring dependency injectors, service providers, and general classes. Mainly driven by one or more configuration files, the package also provides a configuration builder which can read and generate configuration files.

Because the package doesn't depend on any particular container or framework, it can be used in just about anything.
 
**PHP**, **YAML** and **JSON** are supported. PHP is the default.

## Installation

Please note that this package is intended for **PHP 7**.

1. [Install Composer](https://getcomposer.org/).
2. type: `composer require oddgreg/nine-config --no-dev`

Optionally, if you want to run the tests, remove the `--no-dev` from the above.

## Using ConfigFileReader Without Sets

The ConfigFileReader class can be used without any other of the configuration set classes. In a simple configuration case, there may be no need or desire to handle config files any other way.

Because ConfigFileReader caches configurations from a file the first time it is read, subsequent access is fast. You will only ever load what is needed at the time.

## Instantiating and Using the ConfigFileReader Class

```php

// instantiate the reader
$config = new ConfigFileReader(__DIR__ . '/../config/');

// nothing much else to do. The class will read config files on demand.
// this will read $basePath . 'view.php' then return the 'twig' index array.
$twigConfig = $config['view.twig'];

// -- or go deeper --
$twigEnabled = $config['view.twig.enabled'];

// multiple requests from the same config source (ie: view.php) do not 
// reload the file. The ConfigFileReader caches requests.

```

## Using ConfigFileWriter to Modify and Write Config Files

`ConfigFileReader` cannot modify or write configuration files. Using it insures that nothing will change in the configuration files throughout the life of its use. Sometimes it is useful to modify one or more configuration files during certain stages of application execution. Where this is necessary, the `ConfigFileWriter` is provided.
   
`ConfigFileWriter` has `ConfigFileReader` as a dependency. The following provides a simple example:
   
```php
// create and preload a collection of configurations.
$reader = (new ConfigFileReader(CONFIG))->preloadPath();

// create the writer containing pre-loaded configurations.
$writer = new ConfigFileWriter($reader);

// modify the configuration (both methods are equivalent.)
$writer->view_markdown_defaults_debug = false;
// -- or --
$writer['view.markdown.defaults.debug'] = false;

// extract an outer key array and write it to a new file
$writer->exportPHPFile(
    // the path to where the new file will be written
    CONFIG . '../temp/',
    
    // the outer key to extract and write (ie: ['view'] -> 'CONFIG/view.php')
    // optionally, this can be set to '*' to export the entire cache.
    'view',
    
    // optionally, force the filename to something other than the key
    'production.php'
    
    // note that the key to read the new configuration is 'production', and may be read as follows:
    $reader = (new ConfigFileReader(CONFIG . '../temp/'));
    $productionConfig = $reader->read('production');        
);
```

> Note: The `ConfigFileWriter` class does **not** modify the `ConfigFileReader` dependency cache in any way. 

The result will be a new config file ('production.php') that reflects the new state of the configuration.   
   
Finally, the current `ConfigFileWriter` configuration state may be used to initialize the cache of a new `ConfigFileReader`:
 
```php
$reader = new ConfigFileReader($writer);
```

## Package Loader and Configuration Classes

The following classes are provided by the package:

| Class | Contains | Description |
|-------|----------|-------------|
| LoaderSet | 1 or more ConfigurationSets | Used to define, load and configure a specific configuration context. |
| ConfigurationSet | 1 or more Configurators | Used to define, load and configure specific objects such as a Pimple, Auryn or ContainerInterop container. | 
| Configurator | 1 or more configuration key=>value settings. | Used to define, load and configure a set of configurations values for a given context. |


And the following support classes are included:

| Class | Description |
|-------|-------------|
| LoaderReflector | Used by the LoaderSet to inject dependencies into ConfigurationSets and Configurators. Depends on the SymbolTable. |
| Priority | A simple data object that centralizes Priority definitions and resolutions. |
| SymbolTable |  Used by LoaderReflector, this class is a rudimentary symbol table providing naive type and value checking. A LoaderSet loads the symbol table with required non-class, **PHP 7** typed parameter dependencies for the LoaderSet to create objects in the configuration chain without need for any specific Container. |


## How Loader Classes Work

**Overview**

1. A LoaderSet is a repository of ConfigurationSets.
2. A ConfigurationSet is a repository of Configurators.
3. A Configurator is a repository of Configuration items, and provides a method for loading configuration data, and configuring services.

### Common Loader Methods

|method|description|
|:------|:-----------|
|`configure()`| Call on LoaderSet to trigger configuration of all attached ConfigurationSets, each of which call the configure methods on all attached Configurators. |
|`load([])`|Called by a repository to trigger loading configuration data. Analogous to `register`.|

### Example LoaderSet Configuration File

i.e.: config/loaders/**container.php**

```php

return [
    AurynConfigurationSet::class => [
        // the identifier given to this configuration set.
        'name'        => 'app.di',
        // the path to the folder that contains configuration files
        // for this set.
        'config_path' => CONFIG,
        // the loader priority.
        'priority'    => 'high', # 'high' | 'normal' | 'low' | int
        // the list of Configurators in this set.
        'config'      => [
            // the configurator
            BladeConfigurator::class    => [
                // the identifier for this Configurator
                'name'     => 'blade',
                // the data set loaded by the ConfigFileReader class
                // defaults to '' if not supplied.
                'dataset' => 'view.blade',
                // the set priority. Defaults to 'normal' if not supplied.
                'priority' => 'normal',
                // any settings to add or to override settings from the data set.
                // defaults to [] if not supplied.
                'config'   => ['cargo' => 'shamalam'],
            ],
            TwigConfigurator::class     => ['name' => 'twig', 'dataset' => 'view.twig'],
            MarkdownConfigurator::class => ['name' => 'markdown', 'dataset' => 'view.markdown'],
        ],
    ],
    IlluminateConfigurationSet::class => [
        'name' 			=> 'app.container',
        'config_path' 	=> CONFIG,
        'priority' 		=> 'high',
        'config' 		=> [
            BladeConfigurator::class    => [
                'name'     => 'blade',
                'dataset' => 'view.blade',
            ],
            TwigConfigurator::class     => ['name' => 'twig', 'dataset' => 'view.twig'],
            MarkdownConfigurator::class => ['name' => 'markdown', 'dataset' => 'view.markdown'],
        ],
    ],
];
```

**A descriptive example**:

```php
$config = new ConfigFileReader(CONFIG . 'loaders/');
$reflector = new LoaderReflector;

// load the dependency injector
(new LoaderSet($reflector, 'container', $config->read('container')))
    ->loadAll()->configure;

// load the configuration for an application
(new LoaderSet($reflector, 'application', $config->read('app')))
    ->loadAll()->configure;

**-- or --**

// load the configuration for an api
(new LoaderSet($reflector, 'api', $config->read('api')))
    ->loadAll()->configure();
```

To get access to individual sets and configurators:

`$loader['app.di']` returns an array containing the first set. (`AurynConfigurationSet`)

`$loader['app.di]['set']` returns the instance of `AurynConfigurationSet`.
 
`$loader['app.di]['set']['blade']` returns the instance of the `BladeConfigurator`.

`$loader['app.di]['set']['blade']->getSettings()` returns the loaded settings of `BladeConfigurator`.

`$loader['app.di']['set']->getConfigurators()` returns an array of Configurators containing meta information. Below is a sample of results based on the example above:

```php
   |  blade => array (4)
   |  |  configurator => BladeConfigurator #c6f0
   |  |  |  dataset protected => "view.blade" (10)
   |  |  |  key protected => "blade" (5)
   |  |  |  priority protected => 100
   |  |  |  settings protected => array (2)
   |  |  |  |  enabled => TRUE
   |  |  |  |  defaults => array (2)
   |  |  |  |  |  cache => ".../cache/blade" (42)
   |  |  |  |  |  template_paths => array (7)
   |  |  |  |  |  |  0 => ".../views/" (51)
   |  |  |  |  |  |  1 => ".../views/assets/" (58)
   |  |  |  |  |  |  2 => ".../views/templates/default/" (69)
   |  |  |  |  |  |  3 => ".../views/templates/default/forms/" (75)
   |  |  |  |  |  |  4 => ".../views/templates/default/pages/" (75)
   |  |  |  |  |  |  5 => ".../views/templates/" (61)
   |  |  |  |  |  |  6 => ".../views/debug/" (57)
   |  |  configured => TRUE
   |  |  loaded => TRUE
   |  |  profile => array (3)
   |  |  |  added => 1469154406.5784
   |  |  |  loaded => 1469154423.0254
   |  |  |  configured => 1469154423.0259
   |  twig => array (4)
   |  |  configurator => TwigConfigurator #f348
   |  |  |  dataset protected => "view.twig" (9)
   |  |  |  key protected => "twig" (4)
   |  |  |  priority protected => 100
   |  |  |  settings protected => array (2)
   |  |  |  |  enabled => TRUE
   |  |  |  |  defaults => array (5)
   |  |  |  |  |  type => 4
   |  |  |  |  |  filesystem => array (7)
   |  |  |  |  |  |  0 => ".../views/" (51)
   |  |  |  |  |  |  1 => ".../views/assets/" (58)
   |  |  |  |  |  |  2 => ".../views/templates/default/" (69)
   |  |  |  |  |  |  3 => ".../views/templates/default/forms/" (75)
   |  |  |  |  |  |  4 => ".../views/templates/default/pages/" (75)
   |  |  |  |  |  |  5 => ".../views/templates/" (61)
   |  |  |  |  |  |  6 => ".../views/debug/" (57)
   |  |  |  |  |  options => array (4)
   |  |  |  |  |  |  cache => ".../cache/twig" (41)
   |  |  |  |  |  |  debug => NULL
   |  |  |  |  |  |  auto_reload => NULL
   |  |  |  |  |  |  strict_variables => NULL
   |  |  |  |  |  templates => array ()
   |  |  |  |  |  form => array (1)
   |  |  |  |  |  |  templates => array (1)
   |  |  |  |  |  |  |  0 => "bootstrap_3_horizontal_layout.html.twig" (39)
   |  |  configured => TRUE
   |  |  loaded => TRUE
   |  |  profile => array (3)
   |  |  |  added => 1469154406.5786
   |  |  |  loaded => 1469154423.0255
   |  |  |  configured => 1469154423.0259
   |  markdown => array (4)
   |  |  configurator => MarkdownConfigurator #4e55
   |  |  |  dataset protected => "view.markdown" (13)
   |  |  |  key protected => "markdown" (8)
   |  |  |  priority protected => 100
   |  |  |  settings protected => array (1)
   |  |  |  |  defaults => array (5)
   |  |  |  |  |  class => "MarkdownExtra" (13)
   |  |  |  |  |  template_paths => array (4)
   |  |  |  |  |  |  0 => ".../views/templates" (60)
   |  |  |  |  |  |  1 => ".../views/templates/forms" (66)
   |  |  |  |  |  |  2 => ".../views/templates/default" (68)
   |  |  |  |  |  |  3 => ".../views/templates/default/hello" (74)
   |  |  |  |  |  debug => TRUE
   |  |  |  |  |  html5 => TRUE
   |  |  |  |  |  keepListStartNumber => TRUE
   |  |  configured => TRUE
   |  |  loaded => TRUE
   |  |  profile => array (3)
   |  |  |  added => 1469154406.5787
   |  |  |  loaded => 1469154423.0255
   |  |  |  configured => 1469154423.0259
```

### LoaderSet Configuration Definition

The structure of the LoaderSet configuration file is as follows:

    [ <class name of ConfigurationSet> => [ <name>, <config_path>, <priority>, <config> ]
        where priority is optional.

| Identifier | Type | Description |
|------------|--------|-------------|
| 'name' | string | The identifier given to the ConfigurationSet. |
| 'config_path' | string | The path to the base directory that contains configuration files. Note that each LoaderSet may reference a different base configuration path. |
| 'priority' | 'high','normal','low' or integer | The relative activation priority (load and configure order.) |
| 'config' | array | A formatted array describing one or more Configurators. *See below.* |

### ConfigurationSet Configuration Definition (`<config>`)

| Identifier | Type | Description |
|------------|------|-------------|
| 'name' | string | The identifier given to the individual Configurator. |
| 'dataset' | string | A coded reference to the config file to load - optionally included an index. Note that 'view.twig', for example, references the config file at `<base_path>` `view`.php[`<index>`] where view.php returns an associative array of configuration values and [`<index>`] returns an element of the array. |
| 'priority' | 'high','normal','low' or integer | The relative activation priority (common to all classes.) |
| 'config' | array | An optional set of configuration values (*must be an associative array*) either in addition to any loaded from the `dataset` or that overload identical keys from the `dataset`.







## License: MIT
*Copyright &copy; 2016, Greg Truesdell - Part of the Formula Nine Framework.*
