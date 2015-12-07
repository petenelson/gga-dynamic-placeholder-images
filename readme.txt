=== Dynamic Placeholder Images ===
Contributors: gungeekatx
Tags: images, media
Donate link: http://baconmockup.com
Requires at least: 4.0
Tested up to: 4.4
Stable tag: 2.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Serve dynamic placeholder images (like baconmockup or placekitten)

== Description ==

If you've ever wanted to make your own dynamic placeholder images web site (like [baconmockup](http://baconmockup.com/) or [placekitten](https://placekitten.com/)), this plugin is for you.  Once it's installed and you've added some images, you can access them at a URL with the desired height and width.  Example: http://baconmockup.com/400/300/


Includes:

* [dynamic-images-attribution] shortcode to display a complete list of images that can be used
* JSON API

Be sure to check Settings/Dynamic Placeholder images to fully customize your settings.

View a demo page with my goofy dog: https://petenelson.com/dynamic-clyde-images/

Thanks to [Kenzie Moss](https://twitter.com/kenziemoss) for the plugin banner and icon.

== Installation ==

1. Upload the gga-dynamic-placeholder-images to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place the `[dynamic-images-attribution]` shortcode in a page or post
4. Check Settings/Dynamic Placeholder images to fully customize your settings.


== Changelog ==

= v2.0.2 March 10, 2015 =
* Fixed bugs in activation hook
* Added deactivation hook to delete image cache
* Updated uninstall hook to remove plugin options

= v2.0.1 March 8, 2015 =
* Minor updates to the image attribution CSS

= v2.0.0 March 7, 2015 =
* Initial release to the WordPress repository


== Upgrade Notice ==

= v2.0.0 March 7, 2015 =
* Initial release to the WordPress repository


== Frequently Asked Questions ==

= Do you have any questions? =
We can answer them here!


== Screenshots ==

1. Configure your dynamic placeholder image generator
2. Flag an image to be used as a placeholder image
3. Sample image
4. Sample image attributuon grid
