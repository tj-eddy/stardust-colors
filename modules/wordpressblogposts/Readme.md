Wordpress Blog Posts
====================
for Prestashop by Musaffar Patel

Introduction
------------

Wordpress Blog Posts - a module which allows you to display your latest posts from your wordpress blog directly within your Prestashop store. The module can be configured to be displayed in one the common template hooks; or can be displayed anywhere within your store using the custom hook provided by the module.
The module also provides some additional features for further customising the blog posts displayed on your site.

Installation
------------
The installation procedure is very much similar to most other Prestashop modules. No core changes required therefore installation is straight forward, be sure to follow the instructions below to ensure a successful installation.

1. Upload the wordpressblogposts module folder to your store {root}/modules folder
2. Once uploaded, login to your Prestashop Back Office and head over to the “Modules” section
3. Search for the module and click Install
4. After a successful installation, the module configuration screen is presented to you. More details on this in the next section.

After installation has completed successfully it is necessary to edit your wordpress sites functions.php in your blogs theme folder. Adding the code below will ensure the post featured images are added to the blog rss feed.

**Add the following code your wordpress themes functions.php file:**

    function add_my_rss_node() {
    global $post;
    if ( has_post_thumbnail( $post->ID ) ){
    $image_url = wp_get_attachment_image_src( get_post_thumbnail_id($post-
    >ID), 'large');
    echo("<image>{$image_url[0]}</image>");
    }
    }
    add_action('rss2_item', 'add_my_rss_node');


Configuration
-------------

The module configuration screen is displayed immediately after a successful installation or can be accessed later using the configure link in the modules list.

**Blog URL**
Enter the url of your blog feed into this field. You can obtain the url by visiting your blog in a web browser and viewing the source while looking for the url to your rss feed, This is usually in the following format:
http://www.yourblogurl.com/feed/

**Displayable Hooks**
This is a list of hooks you can use to display your latest blog posts; each hook can be customised individually. The main hooks available are:

*displayHome*
Displays the latest blog post(s) on your homepage

*displayLeftColumn*
Displays the latest blog post(s) in the left hand side column of your Prestashop theme

*displayRightColumn*
The same as above but within your right hand side column

*displayWBPPostsCustom*
This is a custom hook which you can place anywhere in your theme’s tpl file if you wish to display the latest blog posts in a location other than the display hooks above. See below for more details on how to use this hook.

Display Hook Settings
---------------------

You can configure each hook in the list of hooks displayed in the configuration screen by clicking the edit option next to the hook. Below are the options available:

*Show Blog posts for this hook*
Use this to enable or disable the displaying of blog posts in the location of this hook.

*How many Blog Posts to display?*
Choose the number of blog posts which should be displayed here

*Category Filter*
You can filter the posts displayed by their corresponding wordpress categories. Enter a comma separated list of your wordpress post category names here and the module will only posts belonging to those categories.

Using the custom hook
---------------------
As mentioned earlier, the module provides a custom hook you can use in the form of a shortcode which can be placed in any of your themes template files. The feature is useful if you wish to display blog posts in a location other than the homepage or left or right columns, you may even use a custom hook to display your latest blog posts in another modules view file.

Below is an example of a shortcode, copy and paste the code below to any of template files:

    {hook h="displayWBPPostsCustom" limit=1 category_filter='' template='hook_home.tpl'}

The shortcode includes several options which you can edit:

*Limit*
The number of posts which should be displayed

*Category Filter*
A comma separated list of wordpress post categories to filter the displayed posts by.

*Template*
The default value is hook_home.tpl. However if you wish to use a custom template; you can do so by performing the following actions:

Copy the file {root}/modules/wordpressblogposts/views/templates/front/hook_home.tpl to {root}/themes/{your_theme}/modules/wordpressblogposts/
views/templates/front/hook_custom.tpl. You can name the file whatever you wish; in this example I have used “hook_custom.tpl”. Now edit the short code with the new file name: for example template=’hook_custom.tpl’
Once setup, your posts should be displayed in whichever location you placed the short code in.

Caching
-------
Connecting to your RSS feed each time the blog posts widget is displayed on your site would result in slower page load times. To circumvent this the module fetches and caches posts each time you save the configuration of a hook and consequently improving performance. To clear the cache simply save the configuration of the desired hook and this will update your blog posts cache.

Support & Feedback
------------------
Should you run into any problems regarding the installation or usage of the module please feel free to get in touch with me through the Prestashop addons store.

I would also welcome any feedback on the module, your feedback will help improve the module in the future.

