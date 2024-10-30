=== Comment Form Inline Errors ===
Contributors: latorante
Donate link: http://donate.latorante.name/
Tags: comments, errors, comment
Requires at least: 3.0
Tested up to: 3.7.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Stable tag:  1.0.2

Display comment form errors nicely!

== Description ==

    This plugin takes care of WordPress inner comments error handling, and shows comment errors above the form, instead of using the weird grey page with single line error description. All this **out-of-the-box**.

It also **remembers** what you've typed in. So for example, if you fill in the comment form, and send it and forget required field, it will show the error above the form, and prefills the form with values you've submitted so you don't have to retype them again (which is annoying).

By default, the plugin prints the error in this markup:

`<div id="formError" class="formError" style="color:red;">
	<p>--error-here--</p>
</div><div class="clear clearfix"></div>`

So it should be quite easy for you to style it in your css file with `".formError"` or `"#formError"`, you know the drill :). The default red colour is there just in case you won't be able to style and since it's error, red seems appropriate.

**By the way,** if you've customised your comment form with new fields, and you're using the `preprocess_comment` hook, with the correct WordPress way of handeling errors, which is using `wp_die` on error encounter, this plugin will play nicely with that, and display your error messsage above the form as well.

== Installation ==

1. Go to your admin area and select Plugins -> Add new from the menu.
2. Search for "Comment Form Inline Errors".
3. Click install.
4. Click activate.

== Frequently Asked Questions ==

= It doesn't do anything, what should I do? =

If that is the case, deactivate the plugin, delete it, and move on :).

= I have a costum post type with comments support, will this work with it? =

**Yes,** it will.

= I have my own custom comment form fields, will it remember the values of those after error as well? =

Do you know what? It actually might, if they are hooked in `comment_form_default_fields`, but it only checks for inputs[text] and textareas tho :).

== Screenshots ==

1. This shows form error before plugin activation, and after.
2. With plugin activated, before submitting our comment.
3. With plugin activated, after comment submission.

== Changelog ==
= 1.0.2 =
* Tested on new WP version.
= 1.0.1.1 =
* Fixed spelling.
= 1.0.1 =
* Fixed backslash issue.
= 1.0 =
* Plugin released.