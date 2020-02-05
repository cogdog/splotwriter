# SPLOT Writer Plugin for WordPress
built with duct tape by [cog.dog](https://cog.dog)

This plugin provides all the functionality of the [TRU Writer SPLOT theme](http://github.com/cogdog/truwriter) but now can be used in any WordPress theme.

-----
*If this kind of stuff has any value to you, please consider supporting me so I can do more!*

[![Support me on Patreon](http://cogdog.github.io/images/badge-patreon.png)](https://patreon.com/cogdog) [![Support me on via PayPal](http://cogdog.github.io/images/badge-paypal.png)](https://paypal.me/cogdog)

----- 

## What is This?

At this point this plugin is still being tested, so there's no documentation here -  but all of the the options and customizer docs for [TRU Writer  theme](http://github.com/cogdog/truwriter) apply here.

On activation, the plugin *should* create two Pages in your site; one acts as the gatekeeper if you require an access code to write. The other Page hosts the form to compose new content. Any page can serve as the writing page as long as it contains a `[writerform]` shortcode to initiate the form. The SPLOT Writer option let's you select which page serves as the writing form.

## With Thanks

SPLOTs have no venture capital backers, no IPOs, no real funding at all. But they have been helped along by a few groups worth recognizing with an icon and a link.

The original TRU Writer was developed under a [Thompson Rivers University Open Learning Fellowship](http://cogdog.trubox.ca/). The idea for and development of this plugin version was supported by Coventry University's [Disruptive Media Learning Lab](https://dmll.org.uk/) plus ongoing support by [Patreon patrons](https://patreon.com/cogdog).

[![Thompson Rivers University](https://cogdog.github.io/images/tru.jpg)](https://tru.ca)  [![Disruptive Media Learning Lab](https://cogdog.github.io/images/dmll.jpg)](https://dmll.org.uk/)   [![Supporters on Patreon](https://cogdog.github.io/images/patreon.jpg)](https://patreon.com/cogdog) 


## See it in Action

* Conestoga College Teaching and Learning ([Coldbox Theme](https://wordpress.org/themes/coldbox/)) - see the [Teaching Stories](https://tlconestoga.ca/category/stories/) submitted via the [Share a Story page](https://tlconestoga.ca/write/)
* Coventry Demo ([Hitchcock theme](https://wordpress.org/themes/hitchcock/)) https://splots.coventry.domains/pluggedin/
* SPLOPT Demo ([Miyazaki theme](https://wordpress.org/themes/miyazaki/)) https://lab.cogdogblog.com/splotwriter/


## Installation

1. Upload `splotwriter` folder to the `/wp-content/plugins/` directory of your site (or upload via Plugins the zip file of this repository
2. Activate the plugin through the 'Plugins' menu in WordPress

## Possible Issues

* If links to your writer page or to the random item (`/random`) on your site fail, try going to Settings/ permalinks and just save to force a rewrite rule change.


## Changes

* v1.1.0 Just got it working! It's beta, baby
* v1.2.0 Minor fixes, new options to enable/hide email entry form plus allow limiting use of emails by domain
