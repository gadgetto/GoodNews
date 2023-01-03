# GoodNews

![Minimum MODX version](https://img.shields.io/badge/MODX_min-3.x-green)
![Minimum PHP version](https://img.shields.io/badge/PHP-7.2.5-green)

GoodNews is a powerful integrated group and newsletter mailing system for the content management framework MODX Revolution. With GoodNews you can easily create mailings or newsletters and have them sent to your subscribers or internal MODX user groups automatically right from your MODX back-end. GoodNews is built with a lot of passion for details, a polished interface and easy operation. The sending process of GoodNews is lightning fast due to its underlying multithreaded sending system.

GoodNews has built in bounce handling for scanning bounced email messages and to automatically perform configurable actions on subscribers based on bounce counters.

## Reqirements

- MODX 3.0.0 or later
- PHP 7.2.5 or later
- Cron (or a web-based cron-job provider)
- PHP Imap Extension (for automatic bounce handling)
- PHP Exec enabled (if not available, mailings can only be sent in single processes)
- optional pThumb add-on (for automatic image resizing)

## Latest Changes

For complete list of changes read the [changelog](./CHANGELOG.md "CHANGELOG")

## Contribution

Your contribution and pull requests are welcome!

## Copyright

GoodNews is copyright 2014-2022 by bitego (@gadgetto).
All rights reserved.

## License

GoodNews is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

[GNU General Public License v2](./LICENSE.md "GNU General Public License v2")