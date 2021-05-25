# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

**Types Of Changes:**

- Added for new features.
- Changed for changes in existing functionality.
- Deprecated for soon-to-be removed features.
- Removed for now removed features.
- Fixed for any bug fixes.
- Security in case of vulnerabilities.

## [0.2.0] - tbc

### Added

- Added Network module
- Added default host setting in hosts

### Changed

- Installation now creates nuber-bridged for the bridged network
- Installation now created vnet0 as the default network for Nuber (previously nuberbr0)
- Installation no longer creates profiles
- Installation gives the option to select port for web interface

### Fixed

- Fixed activity timeout (JS) which was timing out after 4 minutes, this has been adjusted to 10 minutes.

## [0.1.0] - 2021-05-20

- Initial release
