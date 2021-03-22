# Health Check Bundle

[![Latest Version](https://img.shields.io/github/tag/oat-sa/bundle-health-check.svg?style=flat&label=release)](https://github.com/oat-sa/bundle-health-check/tags)
[![License GPL2](http://img.shields.io/badge/licence-LGPL%202.1-blue.svg)](http://www.gnu.org/licenses/lgpl-2.1.html)
[![Build Status](https://travis-ci.org/oat-sa/bundle-health-check.svg?branch=master)](https://travis-ci.org/oat-sa/bundle-health-check)
[![Coverage Status](https://coveralls.io/repos/github/oat-sa/bundle-health-check/badge.svg?branch=master)](https://coveralls.io/github/oat-sa/bbundle-health-check?branch=master)
[![Packagist Downloads](http://img.shields.io/packagist/dt/oat-sa/bundle-health-check.svg)](https://packagist.org/packages/oat-sa/bundle-health-check)


> [Symfony](https://symfony.com/) bundle for health checks automation, based on [health check library](https://github.com/oat-sa/lib-health-check)

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Tests](#tests)

## Installation

```console
$ composer require oat-sa/bundle-health-check
```

**Note**: related [flex recipe](https://github.com/symfony/recipes-contrib/tree/master/oat-sa/bundle-health-check/) will enable and auto configure the bundle in your application.

## Usage

### Available endpoints

This bundle provides by default the following endpoints:

|  Method  | Route           | Description                                           |
|----------|-----------------|-------------------------------------------------------|
| `GET`    | `/ping`         | ensures your application is up and running (no logic) |
| `GET`    | `/health-check` | runs registered checkers (custom logic)               |

**Notes**:
- you can check the related [openapi documentation](openapi/health-check.yaml) for more details
- you can update / disable those routes in the `config/routes/health_check.yaml` file of your application (created by [flex recipe](https://github.com/symfony/recipes-contrib/tree/master/oat-sa/bundle-health-check/))

#### Ping

The ping endpoint just returns a `200` response with the string `pong` as body.

It is just here to ensure your application is correctly installed, up and running.

#### Health Checker

This bundle will automatically add the tag `health_check.checker` to your application services if they implement the [CheckerInterface](https://github.com/oat-sa/lib-health-check/blob/master/src/Checker/CheckerInterface.php)
(they will be auto registered onto the [HealthChecker](https://github.com/oat-sa/lib-health-check/blob/master/src/HealthChecker.php) service).

If you want to register a [CheckerInterface](https://github.com/oat-sa/lib-health-check/blob/master/src/Checker/CheckerInterface.php) implementation from 3rd party libraries, you can configure them as following:

```yaml
# config/services.yaml

services:
    My\Bundle\Checker\SomeChecker:
        tags:
            - { name: 'health_check.checker', priority: 2 }

    My\Bundle\Checker\OtherChecker:
        tags:
            - { name: 'health_check.checker', priority: 1 }
```

**Note**: you can use the `priority` property of the `health_check.checker` tag to order them.

### Available command

If you prefer to run your checks in CLI mode, this bundle provides by default the following command:

```console
$ bin/console health:check
```

**Notes**:
- it runs registered checkers as explained in section above
- it returns `0` in case of overall success, or `1` if one (or more than one) checker failed
- it displays a summary of all executed checkers and their result

## Tests

To run provided tests:

```console
$ vendor/bin/phpunit
```

**Note**: see [phpunit file](phpunit.xml.dist) for available suites.
