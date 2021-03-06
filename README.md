# [**Tracy**](https://github.com/nette/tracy) Log Checker
[![Downloads](https://img.shields.io/packagist/dt/radek-bruha/tracy-log-checker.svg?style=flat-square)](https://packagist.org/packages/radek-bruha/tracy-log-checker)
[![Build Status](https://img.shields.io/travis/radek-bruha/tracy-log-checker.svg?style=flat-square)](https://travis-ci.org/radek-bruha/tracy-log-checker)
[![Latest Stable Version](https://img.shields.io/github/release/radek-bruha/tracy-log-checker.svg?style=flat-square)](https://github.com/radek-bruha/tracy-log-checker/releases)

**Usage**
```
composer require radek-bruha/tracy-log-checker
```

**Tracy Usage**
```php
Tracy\Debugger::$logSeverity = E_ALL;
Tracy\Debugger::getBar()->addPanel(new Bruha\Tracy\LogCheckerPanel());
```

**Nette Framework Usage**
```yml
tracy:
    logSeverity: E_ALL
    bar: [Bruha\Tracy\LogCheckerPanel()]
```

**Example Website Usage**

![Tracy Log Checker](https://i.imgur.com/jFDduH4.png)
