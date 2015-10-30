# OpenLocalization CLI tool

[![Build status...](https://img.shields.io/travis/openl10n/openl10n-cli.svg)](http://travis-ci.org/openl10n/openl10n-cli)

## Install

Download the last PHAR file from the [releases panel](https://github.com/openl10n/openl10n-cli/releases)
and add it to your `$PATH`:

```shell
VERSION=vX.X.X
curl -LO "https://github.com/openl10n/openl10n-cli/releases/download/${VERSION}/openl10n.phar"
chmod +x openl10n.phar
mv openl10n.phar /usr/local/bin/openl10n
```

Or clone the source and build it manually using [Composer](https://getcomposer.org/)
and [Box Project](http://box-project.org/).

```shell
git clone https://github.com/openl10n/openl10n-cli.git; cd openl10n-cli
composer install
box build
mv openl10n.phar /usr/local/bin/openl10n
```

You can also include it directly in a PHP project by adding it in your Composer
dependencies:

```shell
composer require openl10n/cli
```

## Usage

Write a `.openl10n.yml` file on the root of your project:

```yaml
# Server configuration
server:
    hostname: openl10n.dev # Location of your openl10n instance
    port: 80               # Specify port if needed (optional)
    use_ssl: true          # If openl10n is protected by ssl (optional)
    username: user         # User credentials (login)
    password: userpass     # User credentials (password)

# Project identifier
project: foobar

# Path to the translation files
files:
    # Example of patterns for a standard Symfony application
    - pattern: app/Resources/translations/*.<locale>.*
    - pattern: src/*Bundle/Resources/translations/*.<locale>.*
```

If you use versionning with this configuration file then it's better to specify
server credentials outside of the project.

You can use an alias in the `.openl10n.yml` file:

```yaml
# Server configuration
server: foobar
```

and reference the credentials for this alias into the
`$HOME/.openl10n/server.conf` file:

```yaml
[foobar]
hostname: openl10n.dev
port: 80
use_ssl: true
username: user
password: userpass
```

Upload translations:

```shell
openl10n push --locale=all
```

Download translations:

```shell
openl10n pull --locale=all
```

### Select files to upload

You can select the files you want to push to the server by adding a file name list at the end of the command:

```shell
openl10n push --locale=all app/Resources/fr.yml config/translations/de.yml
```

### Select files to download

Same thing here:

```shell
openl10n pull --locale=all app/Resources/fr.yml config/translations/de.yml
```

## License

OpenLocalization is released under the MIT License.
See the [bundled LICENSE file](LICENSE) for details.
