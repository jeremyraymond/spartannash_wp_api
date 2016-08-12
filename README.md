SpartanNash Posts
=================
## Description:
This plugin extends WP API v2 to add a few custom endpoints that return specified posts. It also attaches the meta data
from custom fields in a neat and organized way to the returned object. For example:

    {
    "ID": 141,
    "post_author": "1",
    "post_date": "2016-07-25 13:08:09",
    "post_date_gmt": "2016-07-25 13:08:09",
    "post_content": "<p class=\"p1\">Nulla metus metus, ullamcorper vel, Nam nec ante. Sed lacinia,i...etc</p>",
    "post_title": "Testimonial 3",
    "post_excerpt": "",
    "post_status": "publish",
    "comment_status": "closed",
    "ping_status": "closed",
    "post_password": "",
    "post_name": "testimonial-3",
    "to_ping": "",
    "pinged": "",
    "post_modified": "2016-07-25 13:08:09",
    "post_modified_gmt": "2016-07-25 13:08:09",
    "post_content_filtered": "",
    "post_parent": 0,
    "guid": "http://s18171.p386.sites.pressdns.com/?post_type=testimonials&#038;p=141",
    "menu_order": 0,
    "post_type": "testimonials",
    "post_mime_type": "",
    "comment_count": "0",
    "filter": "raw",
    "post_meta": {
        "edit_last": "1",
        "edit_lock": "1469452005:1",
        "name": "Andy Anderson",
        "title": "Junior Thought Dispenser"
        }
    }

Endpoints/Routes
----------------
### /wp-json/spartannash/v2/posts/
This endpoint mimics the functionality of WP_Query. Query parameters can be added as necessary to
retrieve the desired posts. The query parameters line up almost exactly with the $args in **WP_Query($args)**.
There is the addition of one query parameter of 'key'. This changes the returned array to be an associative array
that uses a field from within the posts as the array key. For example: **/?key=path** will return an array of the
posts that uses the path (everything after the domain) as the key. Recommended keys include 'path', 'ID', and 'guid'
because they're unique and won't result in post results overriding each other in the array.

You can read what is available in $args here: [https://codex.wordpress.org/Class_References/WP_Query#Parameters](https://codex.wordpress.org/Class_References/WP_Query#Parameters)
**Example:** http://example.com/wp-json/spartannash/v2/posts/?post_type=post,page&orderby=title&order=DESC
*Note that if there's multiple post_types in the query, comma separate them out like in the above example.*

### /wp-json/spartannash/v2/posts/path/<$path>
This endpoint uses the path on the end to get the data (plus meta data) for the post that matches. This is useful
because you don't know the ID when a user enters a url, and if you want to dynamically retrieve a page based on the url
that's not possible using vanilla WP API (it can just use the slug, which can be duplicated). If left blank, it will
retrieve the designated homepage. If the page is a child of another page, use the full path of 'parent_page/child_page'.
This will retrieve pages, posts, and all custom posts that have a url.

### /wp-json/spartannash/v2/options/<$options>
This endpoint retrieves the values of wordpress options based on the option name using the get_option($option) method
for every comma separated value. If no options matching the submitted options exist, it returns an error. If some
submitted options exist but some don't, it will return the value for the ones that do and return false for the ones
that don't.
**Example:** http://example.com/wp-json/spartannash/v2/options/header_logo,footer_address,footer_copywrite

### /wp-json/spartannash/v2/menus/
This endpoint retrieves the all of the wordpress menus created as well as all of the menu items assigned to each menu and
returns it as an array. It also adds 'path' as a returned value so you can do relative urls in your menu.

### /wp-json/spartannash/v2/menus/<$menu>
This endpoint retrieves all of the menu items from a single menu based on either the menu id, slug, or name and returns
them as an array. It also adds 'path' as a returned value so you can do relative urls in your menu.



