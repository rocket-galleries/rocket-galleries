=== Rocket Galleries  ===
Homepage: http://rocketgalleries.com
Contributors: MatthewRuddy
Tags: gallery, galleries, image, images, media, rocket, rocket gallery, rocket galleries, photo, album, photo albums, photos, picture, pictures, thumbnails
Requires at least: 3.8
Tested up to: 4.0
Stable tag: 0.2.0.1

Rocket Galleries is the gallery manager WordPress never had. Easily create and manage galleries from one intuitive panel.

== Description ==

Rocket Galleries is the gallery manager WordPress never had. Easily create and manage galleries from one intuitive panel within WordPress. Simple, easy to use, and lightweight. Some of the features include:

* Lightweight, with no Javascript needed and minimal CSS at less than 0.3kb!
* Bulk image uploading, integrated with new WordPress Media Library
* A built in template loader, allowing you to create your own gallery templates for your theme
* Developer friendly, with huge scope for customization using built-in actions & filters

<strong>Follow & contribute to this plugin on <a href="https://github.com/rocket-galleries/rocket-galleries">Github</a>.</strong>

The modern, straight forward interface is built to fit right into the WordPress admin area. It's built to feel like native WordPress functionality, not a plugin, which is great for impressing clients and instantly feeling familiar with the plugin's admin area. Simple, just the way we like it.

Rocket Galleries is also developer friendly. We've made it easy to integrate the plugin with your theme by including a template loader. This allows you to create your own gallery templates and use your own HTML, CSS and Javascript easily. We've also included dozens of filters and actions within the plugin's code, allowing you to hook into the core and create your own Rocket Galleries extensions. The possibilities are limitless!

Last but not least, Rocket Galleries is a very new plugin, so we look forward to your feedback. Over the next couple of months we're hoping to rapidly develop it's functionality, so feel free to use the <a href="http://wordpress.org/support/rocketgalleries/">Support Forums</a> to make feature suggestions and feedback.

== Installation ==

= Display a gallery =
To display a gallery, you can use any of the following methods. In each example, replace the "1" with the ID of the gallery you wish to display.

**In a post/page:**
Simply insert the shortcode below into the post/page.

`[rocketgalleries id="1"]`

**In your theme:**
To insert a gallery in your theme, add the following code to the appropriate theme file.

`<?php if ( function_exists( "rocketgalleries" ) ) { rocketgalleries( 1 ); } ?>`

== Frequently Asked Questions ==

Nothing here yet, but we're constantly working on improving. If you've a question to ask, feel free to ask it on the <a href="http://wordpress.org/support/rocketgalleries/">Support Forums</a>. You may just get featured here ;)

== Screenshots ==

1. "All Galleries" panel. Manage your galleries from here.
2. "Edit Gallery" panel. A beautifully simple interface for managing a gallery.
3. Use the WordPress Media Library to add images to your galleries.
4. "Edit Settings" panel. Various plugin settings can be managed from here.
5. We've integrated a modal window for adding galleries to your posts/pages easily.
6. Preview of default gallery styling with the Twenty Fourteen theme.

== Changelog ==

= 0.2.0.1 =
* Testing new deployment strategy.

= 0.2 =
* Gallery class has now been made re-usable. Easily use it to display your own gallery programatically.
* Added “Link To” option for linking gallery images to Attachment Page, full size image, or other custom options.
* Many more under the hood improvements to prepare the plugin to future plans!

= 0.1.5 =
* Fixed bug that prevented creation of galleries when using PHP v5.2.*.
* Added some missing localisation. 

= 0.1.4 =
* Fixed fatal error on activation when using PHP v5.2.*.

= 0.1.3 =
* Hoping to have fixed some fatal errors on activation.

= 0.1.2 =
* Fixed warning message when resetting the plugin.
* Fixed gallery item link issue.
* Added some missing images.

= 0.1.1 =
* Fixed “Welcome” panel links.
* Some general code improvements.

= 0.1 =
* Initial release.