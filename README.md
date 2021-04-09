# Drupal Lightning
![Lightning logo of a bolt of lightning](https://raw.githubusercontent.com/acquia/lightning/8.x-3.x/lightning-logo.png)

[![Build Status](https://travis-ci.org/acquia/lightning.svg?branch=8.x-3.x)](https://travis-ci.org/acquia/lightning)

## November 2021: So long and thanks for all the fish!
Acquia is **ending support for the Lightning distribution in November 2021**, simultaneously with Drupal 8. At that time, Lightning 3, 4, and 5 will cease receiving any security updates or bug fixes. It is possible to safely uninstall Lightning from your site; please see [the official announcement](https://www.acquia.com/blog/acquia-lightning-eol-2021-acquia-cms-future), [FAQ for site owners](https://support.acquia.com/hc/en-us/articles/1500006393601-Frequently-Asked-Questions-FAQ-regarding-End-of-Support-for-Acquia-Lightning), and [developer instructions](https://github.com/acquia/lightning/wiki/Uninstalling-Lightning) for more information.

---

Lightning's mission is to enable developers to create great authoring
experiences and empower editorial teams.

You'll notice that Lightning appears very sparse out of the box. This is by
design. We want to empower editorial teams and enable developers to jump-start
their site builds. That means that a developer should never have to undo
something that Lightning has done. So we started with a blank slate and
justified each addition from there.

## Installing Lightning
The preferred way to install Lightning is using our
[Composer-based project template][template]. It's easy!

```
$ composer self-update
$ composer create-project acquia/lightning-project MY_PROJECT
```

If you don't want to use Composer, you can install Lightning the traditional way
by downloading a tarball from Lightning's [GitHub releases page](https://github.com/acquia/lightning/releases).
(Please note that the tarball generated by Drupal.Org's packager does not
include required Composer dependencies and should not be used without following
the special instructions found there.)

You can customize your installation by creating a [sub-profile which uses
Lightning as its base profile][sub-profile documentation]. Lightning includes a
Drupal Console command (`lightning:subprofile`) which will generate a
sub-profile for you.

#### Installing from exported config
If you have a config export of a site built with Lighting, you can install it
using the [Config Installer](https://drupal.org/project/config_installer)
profile. This profile is fully supported by Lightning (we even run our tests
against it). You can find more information about installing Lightning from
exported config [here](https://lightning.acquia.com/blog/using-configuration-installer-lightning).

## What Lightning Does
Through custom, contrib, and core modules plus configuration, Lightning aims to
target four functional areas:

### Media
The current version of media includes the following functionality:

* A preconfigured Text Format (Rich Text) with CKEditor WYSIWYG.
* A media button (indicated by a star -- for now) within the WYSIWYG that
  launches a custom media widget.
* The ability to place media into the text area and have it fully embedded as it
  will appear in the live entity. The following media types are currently
  supported:
  * Audio files
  * Tweets
  * Instagram posts
  * Videos (YouTube, Vimeo, and files are supported out of the box)
  * Images
* Drag-and-drop bulk image uploads.
* Image cropping.
* Support for creating slideshows and carousels of media assets.
* Ability to create new media through the media library (/media/add)
* Ability to embed tweets, Instagrams, and YouTube/Vimeo videos directly into
  CKEditor by pasting the video URL

#### Extending Lightning Media (Contributed Modules)
Drupal community members have contributed several modules which integrate
Lightning Media with additional third-party media services. These modules are
not packaged with Lightning or maintained by Acquia, but they are stable and you
can use them in your Lightning site:

  * [Facebook](https://www.drupal.org/project/lightning_media_facebook)
  * [Imgur](https://www.drupal.org/project/lightning_media_imgur)
  * [Flickr](https://www.drupal.org/project/lightning_media_flickr)
  * [500px](https://www.drupal.org/project/lightning_media_d500px)
  * [SoundCloud](https://www.drupal.org/project/lightning_media_soundcloud)
  * [Tumblr](https://www.drupal.org/project/lightning_media_tumblr)
  * [Spotify](https://www.drupal.org/project/lightning_media_spotify)
  * [Pinterest](https://www.drupal.org/project/lightning_media_pinterest)  

### Layout
Lightning includes the Panelizer module, which allows you to configure the
layout of any content type using a drag-and-drop interface (Panels IPE).
Lightning also includes a Landing Page content type for you to create
landing pages with their own one-off layouts and content.

Any content type that uses Panelizer will allow you to set up default layouts
for each view mode of that content type, which you can choose from (or override
on a one-off basis) for individual pieces of content.

Eight layouts are provided out of the box by Panels. You can create your own
layouts (see the Layout Discovery module bundled with Core) or install a
contributed library of layouts like [Radix Layouts](https://www.drupal.org/project/radix_layouts).

### Workflow
Lightning includes tools for building organization-specific content workflows.
Out of the box, Lightning gives you the ability to manage content in one of four
workflow states (draft, needs review, published, and archived). You can create
as many additional states as you like and define transitions between them. It's
also possible to schedule content to be transitioned between states at a
specific future date and time.

### API-First
Lightning ships with several modules which, together, quickly set up Drupal to
deliver data to decoupled applications via a standardized API. By default,
Lightning installs the OpenAPI and JSON API modules, plus the Simple OAuth
module, as a toolkit for authentication, authorization, and delivery of data
to API consumers. Currently, Lightning includes no default configuration for
any of these modules, because it does not make any assumptions about how the
API data will be consumed, but we might add support for standard use cases as
they present themselves.

If you have PHP's OpenSSL extension enabled, Lightning can automatically create
an asymmetric key pair for use with OAuth.

## Project Roadmap
We publish sprint plans for each patch release. You can find a link to the
current one in [this meta-issue][meta_releases] on Drupal.org.

## Resources
Demonstration videos for each of our user stories can be found [here][demo_videos].

Please use the [Drupal.org issue queue][issue_queue] for latest information and
to request features or bug fixes.

## Known Issues

### Media
* If you upload an image into an image field using the new image browser, you
  can set the image's alt text at upload time, but that text will not be
  replicated to the image field. This is due to a limitation of Entity Browser's
  API.
* Some of the Lightning contributed media module listed above might not yet be
  compatible with the core Media module.
* Using the bulk upload feature in environments with a load balancer might
  result in some images not being saved.

### Inherited profiles
Drush is not aware of the concept of inherited profiles and as a result, you
will be unable to uninstall dependencies of any parent profile using Drush. You
can still uninstall these dependencies via the UI at "/admin/modules/uninstall".
We have provided patches [here](https://www.drupal.org/node/2902643)
for Drush which allow you to uninstall dependencies of parent profiles.

* [Drush 9 inherited profile dependencies patch](https://www.drupal.org/files/issues/2902643-2--drush-master.patch).

## Contributing
Issues are tracked on [drupal.org][issue_queue]. Contributions can be provided
either as traditional patches or as pull requests on our [GitHub clone][github].

Each Lightning component also has a drupal.org issue queue:

* [API](https://www.drupal.org/project/issues/lightning_api)
* [Core](https://www.drupal.org/project/issues/lightning_core)
* [Layout](https://www.drupal.org/project/issues/lightning_layout)
* [Media](https://www.drupal.org/project/issues/lightning_media)
* [Workflow](https://www.drupal.org/project/issues/lightning_workflow)

For more information on local development, see CONTRIBUTING.md.

### How to uninstall Lightning
Lightning is an installation profile, so there's no "officially" sanctioned way
to remove it. The procedure outlined here is one that you do **at your own
risk**, and in the worst case scenario **it has the potential to break your
site**, so proceed with caution! It _highly_ recommended to first attempt this
in a development environment before doing it in production.

That said:
1. Change the current installation profile from Lightning to Standard, or
   another installation profile of your choice: `drush config:set core.extension profile standard`.
2. Uninstall any Lightning modules you are not actively using.
3. If needed, export your configuration to account for changes made by outgoing
   modules.
4. You may be using modules that ship with Lightning -- ensure that all of them
   are explicitly listed as requirements in your project's composer.json file,
   in at least the same version you were already using. For example, if you are
   using Panelizer 4.2 or later, you should run `composer require --no-update drupal/panelizer:^4.2`.
   This is **very important**, because your site is likely to break if you
   remove Lightning before ensuring that all the modules you need are being
   required by Composer. If you need to continue using a particular Lightning
   module, you can require it just as you would any other Drupal module; for
   example, `composer require --no-update drupal/lightning_api:^4.1`. (Note
   that this "à la carte" style only works with Lightning 3 or later.)
5. Remove `acquia/lightning` from your composer.json file: `composer remove --no-update acquia/lightning`.
6. Explicitly require Drupal core in your composer.json file: `composer require --no-update drupal/core:~8.7.0`.
   You should specify the same minor version of core that you were using when
   Lightning was installed.
7. Run `composer update`.
8. You should now be all set. If you ever reinstall the site, you will need to
   specify the installation profile (probably Standard), either in the web
   installer, `drush site:install PROFILENAME`, or as a configuration parameter
   for BLT.

[issue_queue]: https://www.drupal.org/project/issues/lightning "Lightning Issue Queue"
[meta_release]: https://www.drupal.org/node/2670686 "Lightning Meta Releases Issue"
[template]: https://github.com/acquia/lightning-project "Composer-based project template"
[d.o_semver]: https://www.drupal.org/node/1612910
[lightning_composer_project]: https://github.com/acquia/lightning-project
[demo_videos]: http://lightning.acquia.com/blog/lightning-user-stories-demonstrations "Lightning user story demonstration videos"
[sub-profile documentation]: https://github.com/acquia/lightning/wiki/Lightning-as-a-Base-Profile "Lightning sub-profile documentation"
[github]: https://github.com/acquia/lightning "GitHub clone"
[lightning_dev]: https://github.com/acquia/lightning-dev "Lightning Dev repository"
