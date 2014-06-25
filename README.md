# OpenLocalization CLI tool

## Install

Build the PHAR file using [Box Project](http://box-project.org/).

```
composer install
box build
mv openl10n.phar /usr/local/bin/openl10n
```

## Usage

Write a `openl10n.yml` file on the root of your project:

```
server:
    hostname: openl10n.dev
    username: user
    password: user

project: foobar

files:
    - pattern: config/translations/<locale>.json
    - pattern: app/Resources/<locale>.yml
```

Upload translations:

```
openl10n push --locale=all
```

Download translations:

```
openl10n pull --locale=all
```

## License

OpenLocalization is released under the MIT License.
See the bundled LICENSE file for details.
