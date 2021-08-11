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

## [0.4.2] - 2021-08-11

### Security

- Added securing the host and changed instructions on adding host, basically adding a firewall and reverting 0.4.1.

## [0.4.1] - 2021-08-07

### Security

- Changed setup instructions to advise to setup LXD to listen on local IP to prevent LXD API available remotely by default

### Removed

- Removed ZFS setup from install script
- Removed bridge setup from install script

### Added

- Added disabling of virtual machine radio button when KVM not available (requires LXD 4.0.7+ from stable)

## [0.4.0] - 2021-07-16

### Added

- Virtual machine management
- Added warning sign to debug log when request is success but LXD returns an error
- Added error check during installation of nuber, to make sure updates can be downloaded if not bail
- Added warning on ports page when assigned IP address is different to that was set
- Added failed login attempt logging
- Added protocol option when adding port forwarding

### Changed

- Setup host no longer sets up private IPv6 networking
- Changed self signed cert generation to 10 years
- Changed password validation rules

## Security

- Added user authentication failure logging
- Added Content-Security-Policy to login page and moved inline styles and scripts to seperate files
- Added validation checks prior to login
- Added message to install script for user to add IP tables rule for web interface

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
