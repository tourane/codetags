# tourane/codetags

## Installation

Install the latest version with:

```bash
$ composer require tourane/codetags
```

## Basic usage

### Requirements

* PHP 5.3.2 is required but using the latest version of PHP is highly recommended.

### Default instance

```php
use Tourane\Codetags;

$codetags = Codetags::instance();

// ...

if ($codetags->isActive('new-version')) {
  // do somethings
}

if ($codetags->isActive('mongodb', 'couchdb')) {
  // at least one of 'mongodb' and 'couchdb' is available
}

if ($codetags->isActive(['foo', 'bar'])) {
  // both 'foo' and 'bar' are available
}
```

### Setting environment variables

#### Setting environment variables in php-fpm

Find your `php-fpm` pool config file (usually `/etc/php/7.2/fpm/pool.d/www.conf`, but could be in other place or have a different name - `/etc/php/7.2/fpm/php-fpm.conf` for example).

Find this line and uncomment it (remove the ‘;’):

```ini
;clear_env = no
```

Add environment variables declaration like this:

```ini
env[CODETAGS_INCLUDED_TAGS] = 'mongodb,foo,bar'
env[CODETAGS_EXCLUDED_TAGS] = 'couchdb'
```

Restart the `php-fpm` process with:

```bash
sudo service php7.2-fpm restart
```

## License

MIT

See [LICENSE](LICENSE) to see the full text.
