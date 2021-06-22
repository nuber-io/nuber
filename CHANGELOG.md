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

## [0.4.0] - TBC

### Added

- Virtual machine management
- Added warning sign to debug log when request is success but LXD returns an error
- Added error check during installation of nuber, to make sure updates can be downloaded if not bail
- Added warning on ports page when assigned IP address is different to that was set

### Changed

- Setup host no longer sets up private IPv6 networking

## [0.3.0] - 2021-06-16

### Added

- Added MAC address to networking
- Added openSUSE 15.3 image to menu

### Fixed

- Fixed openSUSE 15.1 error, removed as this image is no longer available
- Fixed calculating used disk space on Alpine containers
- Fixed host validation to wrap IPv6 in square brackets

## [0.2.0] - 2021-05-28

### Added

- Added Network module
- Added default host setting in hosts
- Added displaying of IPv6 addresses if they are available

### Changed

- Installation now creates nuber-bridged for the bridged network
- Installation now created vnet0 as the default network for Nuber (previously nuberbr0)
- Installation no longer creates profiles
- Installation gives the option to select port for web interface

### Fixed

- Fixed activity timeout (JS) which was timing out after 4 minutes, this has been adjusted to 10 minutes.

## [0.1.0] - 2021-05-20

- Initial release
