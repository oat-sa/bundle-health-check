CHANGELOG
=========

Unreleased
----------

* Breaking: require Symfony 6 or 7 (`^6.0 || ^7.0`)
* Breaking: require PHP >= 8.2
* Use colinodell/psr-testlogger in functional tests (psr/log 3 compatibility)

2.1.0
-----

* Added automatic logging for HealthChecker

2.0.0
-----

* Added PHP support from version 7.2 to 8.0
* Added travis builds with coveralls

1.1.0
-----

* Added health check command

1.0.0
-----

* Added symfony down to 4.4.x retro compatibility
* Updated licence to LGPL

0.1.0
-----

* Added ping and health check endpoints
* Added retrieval of CheckerInterface implementations for the HealthChecker service construction
