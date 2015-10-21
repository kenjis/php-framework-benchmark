# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.0.0rc2 - 2015-10-20

Second release candidate.

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Updated expressive to RC2.
- Updated subcomponent versions in installer to `^0.2`

## 1.0.0rc1 - 2015-10-19

First release candidate.

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.5.3 - 2015-10-16

### Added

- [#8](https://github.com/zendframework/zend-expressive-skeleton/pull/8) adds a
  routine to the installer that recursively removes the `src/Composer/`
  directory of the skeleton, ensuring you have a clean start when creating a
  project.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.5.2 - 2015-10-13

### Added

- [#7](https://github.com/zendframework/zend-expressive-skeleton/pull/7) adds a
  dependency on zend-stdlib for the purposes of globbing and merging
  configuration.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.5.1 - 2015-10-11

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#6](https://github.com/zendframework/zend-expressive-skeleton/pull/6) updates
  the zendframework/zend-view package configuration to remove the dependency on
  zendframework/zend-i18n, as it is now handled in the standalone
  zend-expressive-zendviewrenderer package.

## 0.5.0 - 2015-10-10

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#3](https://github.com/zendframework/zend-expressive-skeleton/pull/3) updates
  the skeleton to use zendframework/zend-expressive 0.4.0.

## 0.4.0 - 2015-10-09

First release as zend-expressive-skeleton.

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.3.0 - 2015-09-12

### Added

- Use zend-expressive template factories.
- Use the zend view url helper in the layout template.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.2.0 - 2015-09-11

### Added

- [#bbb2e60](https://github.com/xtreamwayz/expressive-composer-installer/commit/bbb2e607af23e3ae23f6a9c71eb97c3c651c0ca1) adds PHPUnit tests.
- [#791c1c6](https://github.com/xtreamwayz/expressive-composer-installer/commit/791c1c63f324ca08d08e26375f3a356102bf2ad9) adds Whoops error handler.
- [e1d8d7bf](https://github.com/xtreamwayz/expressive-composer-installer/commit/e1d8d7bf5d5e2f51863fa59a37d1963405743201) adds config caching in production mode.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.1 - 2015-09-08

### Added

- [#b4a0923](https://github.com/xtreamwayz/expressive-composer-installer/commit/b4a092386993227f8057d7ad4e0d9762659eefb0) adds support for Pimple 3.0.x. Still needs testing!

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#11](https://github.com/xtreamwayz/expressive-composer-installer/issues/11) fixes an issues where non stable packages are not being installed correctly.

## 0.1.0 - 2015-09-07

Initial tagged release.

### Added

- Everything.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
