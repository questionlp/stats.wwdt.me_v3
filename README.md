# Wait Wait Don't Tell Me! Stats and Details Site, Version 3

## Status

This project contains legacy code and is no longer maintained. It has been
published for historical reference use and to show the progression of the
Stats Page over the years.

## Overview

PHP-based web application that serves up statistics and details for the NPR
weekly quiz show [Wait Wait... Don't Tell Me!](http://waitwait.npr.org)

## Requirements

- PHP 7.0 (Other versions of PHP are untested)
- MySQL or MariaDB database containing data from the Wait Wait... Don't Tell Me! Stats Page database

## Installation

Refer to [INSTALLING.md](INSTALLING.md) for information on how to set up an
instance of this web application that can be served through Apache 2.x.

## Known Issues

The following are known issues that either impact the functionality with the
application or extraneous `NOTICE` or `WARNING` messages generated due to
portions of the code or dependant packages haven't been fully ported to
PHP 7.x.

- Special characters, including any accented characters, aren't rendered
  as part of converting string data from the database and running them
  through `htmlentities()`
- Uses deprecated or unmaintained packages, including `MDB2` and `silex`

## Contributing

This project contains legacy code and new contributions generally will
not be accepted at this time. If there is interest in modernizing this codebase
to resolve breaking issues; then, contributions to this project and its
contributors must follow the Contributor Covenent Code of Conduct, version 2.0,
detailed at:
https://www.contributor-covenant.org/version/2/0/code_of_conduct.html.

## License

This library is licensed under the terms of the
[Apache License 2.0](http://www.apache.org/licenses/LICENSE-2.0).
