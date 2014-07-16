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

```yaml
server:
    hostname: openl10n.dev
    # port: 80 # Specify port if needed
    # use_ssl: true # If openl10n is protected by ssl
    username: user
    password: user

project: foobar

files:
    - pattern: config/translations/<locale>.json
    - pattern: app/Resources/<locale>.yml
    # For symfony if you want to import all your translation files
    # - pattern: app/Resources/translations/*.<locale>.*
    # - pattern: src/*Bundle/Resources/translations/*.<locale>.*
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

## License

OpenLocalization is released under the MIT License.
See the bundled LICENSE file for details.
