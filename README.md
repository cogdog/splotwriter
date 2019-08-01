# SPLOT Writer Plugin for WordPress
built with duct tape by [cog.dog](https://cog.dog)

This plugin provides all the functionality of the [TRU Writer SPLOT theme](http://github.com/cogdog/truwriter) but now can be used in any WordPress theme.

-----
*If this kind of stuff has any value to you, please consider supporting me so I can do more!*

[![Support me on Patreon](http://cogdog.github.io/images/badge-patreon.png)](https://patreon.com/cogdog) [![Support me on via PayPal](http://cogdog.github.io/images/badge-paypal.png)](https://paypal.me/cogdog)

----- 

At this point it's so beta there's no documentation here - the options and customizer docs for [TRU Writer  theme](http://github.com/cogdog/truwriter) apply here.

On activation, the plugin *should* create two Pages in your site; one acts as the gatekeeper if you require an access code to write. The other Page hosts the form to compose new content. Any page can serve as the writing page as long as it contains a `[writerform]` shortcode to initiate the form. The SPLOT Writer option let's you select which page serves as the writing form.


## See it in Action

* https://splots.coventry.domains/pluggedin/
* https://lab.cogdogblog.com/splotwriter/


## Installation

1. Upload `splotwriter` folder to the `/wp-content/plugins/` directory of your site (or upload via Plugins the zip file of this repository
2. Activate the plugin through the 'Plugins' menu in WordPress

## Possible Issues

* If links to your writer page or to the random item (`/random`) on your site fail, try going to Settings/ permalinks and just save to force a rewrite rule change.


## Changes

* v1.1.0 Just got it working! It's beta, baby
