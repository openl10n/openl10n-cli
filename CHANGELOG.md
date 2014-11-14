# Change Log

All notable changes to this project will be documented in this file.

## Unreleased

- Allow to overwrite the configuration filepath via the `--config-file` option

## 0.1.1 - 2014-11-14

Bug fixes.

- Fix Symfony guess patterns
- Fix file regex build when translation files are at the root of the project (see [#28](https://github.com/openl10n/openl10n-cli/issues/28))

## 0.1.0 - 2014-09-12

Initial version.

- **Commands**: init, push, pull
- Allow to change the defaut working directory via the `--working-dir` option
- Use of *openl10n/sdk:0.1.0* (with Guzzle 4)
