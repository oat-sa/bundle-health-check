# Health Check Bundle

> [Symfony](https://symfony.com/) bundle for health checks automation, based on [health check library](https://github.com/oat-sa/lib-health-check)

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Tests](#tests)

## Installation

```console
$ composer require oat-sa/bundle-health-check
```

**Note**: related [flex recipe](https://github.com/oat-sa/flex-recipes/tree/master/oat-sa/bundle-health-check/) will enable and auto configure the bundle in your application.

## Usage

### Endpoints

This bundle provides by default the following endpoints:

| Endpoint              | Description                                           |
|-----------------------|-------------------------------------------------------|
| `[GET] /ping`         | ensures your application is up and running (no logic) |
| `[GET] /health-check` | runs configured checkers                              |

**Notes**:
- you can check the [openapi documentation](openapi/health-check.yaml) for more details
- you can change / disable the route in the `config/routes/health_check.yaml` file of your application (created by [flex recipe]((https://github.com/oat-sa/flex-recipes/blob/master/oat-sa/bundle-health-check/0.1/config/routes/health_check.yaml))

### Health Checkers

This bundle will automatically add the tag `health_check.checker` to your application services if they implement the [CheckerInterface](https://github.com/oat-sa/lib-health-check/blob/master/src/Checker/CheckerInterface.php)
They then will be auto registered onto the [HealthChecker](https://github.com/oat-sa/lib-health-check/blob/master/src/HealthChecker.php) service.

If you want to register [CheckerInterface](https://github.com/oat-sa/lib-health-check/blob/master/src/Checker/CheckerInterface.php) implementation from other libraries, you can configure them as follow:

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

**Notes**: you can use the `priority` property of the `health_check.checker` tag to order them.


## Tests

To run provided tests:

```console
$ vendor/bin/phpunit
```

**Note**: see [phpunit file](phpunit.xml.dist) for available suites.
