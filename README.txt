=== 42U Jetpack Booster ===
Contributors: 42urick
Tags: jetpack
Requires at least: 3.0
Tested up to: 3.4.2
Stable tag: 1.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The 42U Jetpack Booster adds redirect tags and HTML email templates to Jetpack Contact Forms.

== Description ==

The 42U Jetpack Booster adds redirect tags and HTML email templates to Jetpack Contact Forms.

To add a redirect to your Jetpack Contact Form, just add a text field named "redirect" to your form. It will be hidden on display.

From the HTML view, set the default value to the location where you would like the form to redirect after submission.

[contact-field label="redirect" type="text" default="/thanks/" /]

Note: you must replace the default each time after editing the form with the Jetpack WYSIWYG editor. The value is not saved by Jetpack.
        
== Installation ==

1. Upload the `42u-jetpack-booster` directory (including all files within) to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==
1. The basic HTML email template.

== Frequently Asked Questions ==

= Why does my default value for the redirect disappear? =

The Jetpack WYSIWYG editor does not save default values. You must add the default value each time you edit the form using the Jetpack WYSWYG.

= Why are my form fields out of order? =

Jetpack always moves the first TEXTAREA field, no matter where it is on your form, into the third position in the email and in the Feedback Tab. 
We think this is weird, too, but don't have an easy way to change it.

= Can I just stick with text emails? =

Sure. Disable HTML emails on the Jetpack Booster Options Screen.

= Is it multisite compatible? =

You betcha.

== Changelog ==

= 1.3.1 =
* Moved scripts into the footer. 

= 1.3 =
* Revised the method to add a redirect. This method is backwards compatible with earlier releases. Documentation updates. CSS update. 

= 1.2 =
* Documentation updates. CSS update.

= 1.1 =
* Documentation updates.

= 1.0 =
* Initial release.


== Upgrade Notice ==
* none
