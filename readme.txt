=== WP Petfinder ===
Contributors: esiteq
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=FHF5UF699YTSW&source=url
Tags: petfinder
Requires at least: 5.0
Tested up to: 5.3.2
Requires PHP: 5.6
License: GPLv2 or later
Stable tag: trunk
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WP Petfinder plugin was designed to integrate Petfinder.com database with your Wordpress site via Petfinder API v2. It will be useful for pet shelters and volunteers to help Pets to get adopted.

== Shortcodes ==

[pf_search_form]

Displays Pet search form. Available options:

**location**: default location. For this option, you can use: city, state; latitude,longitude; or postal code. Example: [pf_search_form location="33025"].

**type**: default animal type. Possible values: dog, cat and more (refer to https://www.petfinder.com/developers/v2/docs/#get-animal-types).

**gender**: default gender. Possible values: male, female.

[pf_search_results]

Displays search results. Available options:

**location**: display animals from specific location. For this option, you can use: city, state; latitude,longitude; or postal code. Example: [pf_search_form location="33025"]. This option will override value set in URL variable.

**type**: animal type.  Possible values: dog, cat and more (refer to https://www.petfinder.com/developers/v2/docs/#get-animal-types). This option will override value set in URL variable.

**gender**: display animals of specific gender. Possible values: male, female. This option will override value set in URL variable.

**shelter_id**: display animals from shelter with specific ID. If **shelter_id** is set, any **location** value will be ignored. Example: [pf_search_results shelter_id="NY606"]

**size**: display animals of specific size. Possible values: small, medium, large, xlarge. This option will override value set in URL variable.

**view**: display animals as **grid** or **list**. This option will override value set in URL variable.

**status**: display animals with specific status. Values: adoptable, adopted.

**sort**: sorting order. Values: recent, -recent, distance, -distance, random. Default is recent.

== Widgets ==

= WPPF Animals From Shelter =

Widget that displays animals. Its output is similar to **[pf_search_results]** shortcode (you can also use shortcode in a widget). Available options:

**Shelter ID**: display animals from specific shelter. Optional.

**Number of animals**: display specified number of animals (1 to 10). Default is 1.

**Animal type**: display specified animal type. By default, displays all animal types.

**IDs**: display animals with specified IDs (IDs can be taken from Petfinder.com). Example: 12345, 432546.

**Template**: display widget using selected template.

= WPPF Search Form =

Works similar to [pf_search_form] shortcode (you can use shortcode instead of a widget). Available options:

**Shelter ID**: display animals from specific shelter.

**Animal Name**: search for animal by name.

**Location**: search for animals in specified location (see **location** field from shortcode for options).

**Type**: search for animals of specific type.

**Gender**: search for animals of specific gender.

**Breed**: search for animals of specific breed (list is available when Type is selected).

**Size**: search for animals of specific size.

**Hide fields**: hide specific fields from search form. Values can be any combination of the following (comma separated): name, location, type, breed, size.

== Plugin options ==

Options are available under WP Petfinder menu.

= General Options =

**Petfinder API key**, **Petfinder API secret**: you can find these values under your account at Petfinder.com. Please set these keys first, otherwise plugin won't work.

**WP Petfinder Cache**: will significantly speed up API calls by caching results using local database. If you are using third party caching plugins such as WP Total Cache, Redis Object Catche, etc - you can turn this option Off. Otherwise keep it On.

**Search Results page**: users will be redirected to this page when they click Submit in search form (both shortcode and widget). If page is not set, it will use current page for search results.

**Animal details page**: users will be redirected to this page when they click animal profile in search results.

**Cat adoption page**: redirect to this page if user clicks **Adopt me** button on cat's page. Can be the same as below.

**Dog adoption page**: redirect to this page if user clicks **Adopt me** button on dog's page. Can be the same as above.

**Detail page title**: rewrite page title (for seo). Default: [name] - [gender] [age] [type]

= Look & Feel =

**WP Petfinder Icon**: you can choose between cat and dog icons that will be displayed in Admin menu.

**Custom CSS**: you can put your CSS here to override plugin's styles. Plugin's styles are located in css/wp-petfinder.css.

== Actions ==

wppf_before_search_form, wppf_after_search_form, wppf_before_search_results, wppf_after_search_results, wppf_animal_gallery

== Installation ==

Like any other plugins, just unpack plugin to your Wordpress 'plugins' directory and activate it.

== Frequently Asked Questions ==

= Question =

Answer.

== Screenshots ==

Screenshots will be uploaded soon.

== Changelog ==

= 0.2 =
* Support of different pages for Cat & Dog adoption. You can still use the same page for all animal types.
* Added new Options page: Look & Feel.
* Widgets now supports templates.

= 0.1 =
* Initial release.

== Upgrade Notice ==

No upgrade issues are known.

== Donate ==

My name is Alex and I am a volunteer that helps stray cats. If you want to support plugin's development and help animals, you can donate some amount via PayPal by clicking Donation link.