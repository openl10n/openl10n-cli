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

project:
    slug: android
    locales: [en, fr, es]

files:
    - config/locales/<locale>/<domain>.json
```

Upload translations:

```
openl10n push
```

Download translations:

```
openl10n pull
```

## License

OpenLocalization is released under the MIT License.
See the bundled LICENSE file for details.
