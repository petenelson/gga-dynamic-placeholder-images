# Dynamic Placeholder Images
[WordPress plugin](https://wordpress.org/plugins/any-ipsum/) to manage and serve up dynamic placeholder images used by the [baconmockup](http://baconmockup.com/) web site

[![Code Climate](https://codeclimate.com/github/petenelson/gga-dynamic-placeholder-images/badges/gpa.svg)](https://codeclimate.com/github/petenelson/gga-dynamic-placeholder-images)

## What's in here?

- **gga-dynamic-placeholder-images.php** - WordPress plugin wrapper (loades classes, registers core hooks, etc)
- **readme.txt** - Info for the WordPress repo
- **uninstall.php** - WordPress uninstall code
- **admin/partials** - Headers for the plugin settings
- **assets/** - Assets for the WordPress plugin repository
- **lang/** - For future translastions
- **includes/**
  - **class-gga-dynamic-placeholder-images-core.php** - Exposes filters used by other classes
  - **class-gga-dynamic-placeholder-images-api.php** - API handler
  - **class-gga-dynamic-placeholder-images-attachment-meta.php** - Adds meta fields to Media library
  - **class-gga-dynamic-placeholder-images-attribution.php** - Handles the [dynamic-images-attribution] shortcode
  - **class-gga-dynamic-placeholder-images-cache.php** - Handles caching functions
  - **class-gga-dynamic-placeholder-images-dashboard-widgets.php** - Dashboard widget
  - **class-gga-dynamic-placeholder-images-settings.php** - Manages plugin settings
  - **class-gga-dynamic-placeholder-images-stats.php** - Manages stats logging
- **public/** - CSS and images, and template for the attribution shortcode HTML


## Revision History

### v2.0.0 March 7, 2015
- Major rewrite, first release to the WordPress repository

### v1.2 March 6, 2013
- Changed most options to autoload=no
- Added textdomain support
- Added admin option to clear associations created between dimensions and images
- Added hook for delete_attachment to clear options assocation with a deleted image

### v1.1 March 6, 2013
- Initial Release
