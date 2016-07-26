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
This endpoint with no query parameters returns all posts on the site. Query parameters can be added as necessary to
retrieve the desired posts. The query parameters line up exactly with the $args in **WP_Query($args)**. You can read
what is available in $args here: [https://codex.wordpress.org/Class_References/WP_Query#Parameters](https://codex.wordpress.org/Class_References/WP_Query#Parameters)
**Example:** http://example.com/wp-json/spartannash/v2/posts/?post_type=post,page&orderby=title&order=DESC
*Note that if there's multiple post_types in the query, comma separate them out like in the above example.*

### /wp-json/spartannash/v2/posts/path/<$path>
This endpoint uses the path on the end to get the data (plus meta data) for the post that matches. This is useful
because you don't know the ID when a user enters a url, and if you want to dynamically retrieve a page based on the url
that's not possible using vanilla WP API. If left blank, it will retrieve the designated homepage. If the page is a
child of another page, use the full path of 'parent_page/child_page'. This will retrieve pages, posts, and all custom
posts that have a url.