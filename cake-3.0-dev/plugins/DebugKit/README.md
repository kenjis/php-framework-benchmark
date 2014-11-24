# CakePHP DebugKit [![Build Status](https://secure.travis-ci.org/cakephp/debug_kit.png?branch=3.0)](http://travis-ci.org/cakephp/debug_kit)

DebugKit provides a debugging toolbar and enhanced debugging tools for CakePHP applications.

## Requirements

The 3.0 branch has the following requirements:

* CakePHP 3.0.0 or greater.
* PHP 5.4.16 or greater.
* SQLite or another database driver that CakePHP can talk to. By default DebugKit will use SQLite, if you
  need to use a different database see the Database Configuration section below.

## Installation

* Install the plugin with composer from your CakePHP Project's ROOT directory (where composer.json file is located)
```sh
php composer.phar require cakephp/debug_kit "3.0.*-dev"
```

* Load the plugin
```php
Plugin::load('DebugKit', ['bootstrap' => true]);
```
* Set `'debug' => true,` in `config/app.php`.

## Reporting Issues

If you have a problem with DebugKit please open an issue on [GitHub](https://github.com/cakephp/debug_kit/issues).

## Contributing

If you'd like to contribute to DebugKit, check out the
[roadmap](https://github.com/cakephp/debug_kit/wiki/roadmap) for any
planned features. You can [fork](https://help.github.com/articles/fork-a-repo)
the project, add features, and send [pull
requests](https://help.github.com/articles/using-pull-requests) or open
[issues](https://github.com/cakephp/debug_kit/issues).

## Versions

DebugKit has several releases, each compatible with different releases of
CakePHP. Use the appropriate version by downloading a tag, or checking out the
correct branch.

* `1.0, 1.1, 1.2` are compatible with CakePHP 1.2.x. These releases of DebugKit
  will not work with CakePHP 1.3. You can also use the `1.2-branch` for the mos
  recent updates and bugfixes.
* `1.3.0` is compatible with CakePHP 1.3.x only. It will not work with CakePHP
  1.2. You can also use the `1.3` branch to get the most recent updates and
  bugfixes.
* `2.0.0` is compatible with CakePHP 2.0.x only. It will not work with previous
  CakePHP versions.
* `2.2.0` is compatible with CakePHP 2.2.0 and greater. It will not work with
  older versions of CakePHP as this release uses new API's available in 2.2.
  You can also use the `master` branch to get the most recent updates.
* `2.2.x` are compatible with CakePHP 2.2.0 and greater. It is a necessary
  upgrade for people using CakePHP 2.4 as the naming conventions around loggers
  changed in that release.
* `3.0.x` is compatible with CakePHP 3.0.x and is still under active development.

# Documentation

## Database Configuration

By default DebugKit will store panel data into a SQLite database in your application's `tmp`
directory. If you cannot install pdo_sqlite, you can configure DebugKit to use a different
database by defining a `debug_kit` connection in your `config/app.php` file.

## Toolbar Panels

The DebugKit Toolbar is comprised of several panels, which are shown by clicking the
CakePHP icon in the upper right-hand corner of your browser after DebugKit has been
installed and loaded. Each panel is comprised of a panel class and view element.
Typically, a panel handles the collection and display of a single type of information
such as Logs or Request information. You can choose to panels from the toolbar or add
your own custom panels.

### Built-in Panels

There are several built-in panels, they are:

 * **Request** Displays information about the current request, GET, POST, Cake
   Parameters, Current Route information and Cookies if the `CookieComponent`
   is in you controller's components.
 * **Session** Display the information currently in the Session.
 * **Timer** Display any timers that were set during the request see
   `DebugKitDebugger` for more information. Also displays
   memory use at component callbacks as well as peak memory used.
 * **Sql Logs** Displays sql logs for each database connection.
 * **Log** Display any entries made to the log files this request.
 * **Variables** Display View variables set in controller.
 * **Environment** Display environment variables related to PHP + CakePHP.

## Configuration

There is no configuration at this time. Configuration options will be coming soon.

## Developing Your Own Panels

You can create your own custom panels for DebugKit to help in debugging your applications.

### Panel Classes

Panel Classes simply need to be placed in the `src/Panel` directory. The
filename should match the classname, so the class `MyCustomPanel` would be
expected to have a filename of `src/Panel/MyCustomPanel.php`.

```php
namespace App\Panel;

use DebugKit\DebugPanel;

/**
 * My Custom Panel
 */
class MyCustomPanel extends DebugPanel {
        ...
}
```

Notice that custom panels are required to subclass the `DebugPanel` class.

### Callbacks

By default Panel objects have 2 callbacks, allowing them to hook into the
current request. Panels subscribe to the `Controller.initialize` and
`Controller.shutdown` events. If your panel needs to subscribe to additional
events, you can use the `implementedEvents` method to define all of the events
your panel is interested in.

You should refer to the built-in panels for some examples on how you can build panels.


### Panel Elements

Each Panel is expected to have a view element that renders the content from the
panel. The element name must be the underscored inflection of the class name.
For example `SessionPanel` has an element named `session_panel.ctp`, and
SqllogPanel has an element named `sqllog_panel.ctp`. These elements should be
located in the root of your `View/Elements` directory.

#### Custom Titles and Elements

Panels should pick up their title and element name by convention. However, if you need to choose a custom element name or title, you can define methods to customize your panel's behavior:

- `title()` - Configure the title that is displayed in the toolbar.
- `elementName()` Configure which element should be used for a given panel.

### Panels in Other Plugins

Panels provided by [Plugins](http://book.cakephp.org/3.0/en/plugins.html)
work almost entirely the same as other plugins, with one minor difference:  You
must set `public $plugin` to be the name of the plugin directory, so that the
panel's Elements can be located at render time.

```php
namespace MyPlugin\Panel;

use DebugKit\DebugPanel;

class MyCustomPanel extends DebugPanel {
    public $plugin = 'MyPlugin';
        ...
}
```

To use a plugin panel, update your application's DebugKit configuration to include
the panel.

```php
Configure::write(
	'DebugKit.panels',
	array_merge(Configure::read('DebugKit.panels'), ['MyCustomPanel'])
);
```

The above would load all the default panels as well as the custom panel from `MyPlugin`.

## DebugKit Storage

By default, DebugKit uses a small SQLite database in you application's `/tmp` directory to store
the panel data. If you'd like DebugKit to store its data elsewhere, you should define a `debug_kit`
connection.
