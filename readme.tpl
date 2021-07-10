=== Heckler ===
Contributors: mastarija
Tags: shortcode, hook, code, block
Tested up to: 5.7
Requires at least: 5.0
Requires PHP: 5.6
Stable tag: {{TAG}}
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Create custom text and code snippets, and attach them to hooks or use them as short codes, along with programmable display rules.

== Description ==

Do you often need to create small pieces of content that should be present on e.g. every product page, but don't justify writing a full on plugin?

Do you mostly work with designers or users that sometimes need to update some of that content, but they don't know their way through Git, FTP or code in general?

If you've answered yes to any of those two questions then this plugin is for you!

Heckler allows you to create reusable pieces of code or text and execute / display them on any wordpress hook (after `init`) or use them as a simple shortcode within your content.

If you have some coding skills, you can programm a rule which determines if the text should be shown, or the code should be hooked or executed.

As an added bonus, there's even a VIM mode in the code editor to make your life as a developer in this world of makeshift plugins just a little more bearable.

If you wish to contribute to this wonderfully horrible plugin you can do so on its [GitHub repository](https://github.com/mastarija/heckler).

== Frequently Asked Questions ==

= Elementor? =

Yes, this plugin supports the [Elementor](https://wordpress.org/plugins/elementor/) as this is what my team works with the most these days. Other builders could be added at a later date.

= How does it work? =

This plugin stores code snippets in the plugin subfolder `usr` and text snippets in the WordPress database as a Heckler post.

During the WordPress `init` phase, the plugin checks for all defined snippets that have defined hooks, and if the `rule` returns true, it hooks the code (loaded from the `usr` folder) or the text to the defined hooks.

In case a snippet is used as a shortcode, before the shortcode is executed the `rule` is checked, and if it passes, only then is the text rendered or the code executed.

= How is this not a security hazard? =

Good question. This plugin uses [nonces](https://codex.wordpress.org/WordPress_Nonces) to protect the edit form, and in order to access the edit form you need to have highest possible privilegies. To make sure that a user has modified a snippet from within the Hecker UI the `save_post_heckler` hook is used. Also, all the stored code snippets are prefixed with `<?php if ( !defined( 'ABSPATH' ) ) return;` to ensure a snippet can't be accessed directly without loading the WordPress first.

In other words, it's as much of a hazard as the plugin editor, or the plugin installer that come by default with the WordPress installation.

If this is still too much of a risk for you, then this plugin might not be for you.

== Screenshots ==

1. Simple text editor content, with Elementor support.
2. Hook list used to define hooks on which to display your content, priority, number of arguments (if you are using Code) and if this hook is active or inactive.
3. Content displayed on the `wp_footer` hook.
4. A Rule editor with VIM support.
5. A Code editor with VIM support.
6. Heckler shortcode embedded into the content.
7. Output of a Heckler shortcode content generated through a Code script.