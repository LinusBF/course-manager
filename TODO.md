* Course description in admin panel?
* Course Shop image in admin panel?
* **Create and use a cm_course_meta table for handling shop related settings**
* Course pages have own sidebar field. Maybe through template?
    * Course pages has custom page type cm_course_page - Tie template to page type
* Course shop, how? Use plugin and make Courses a "product"? Code own "shop page" that comes with the plugin
    * Shop page generated, user adds it in to a menu. Page uses a custom "Shop" template.
* ~~User entitlements and user registration. Existing plugin?~~
    * Implement user token system instead - Ground work done
* Implement tag system fully
* Implement settings system fully. Functionality and choices on settings page?
* ~~UAM maybe not needed?~~ - Not needed
* Ask Tina about cm_user_answers structure
* Look into changing database table calls from CmCourse* classes
* ~~Swift theme not removing course pages from menu~~
    * No longer a problem with custom post types
* Update DB calls from SQL to WPDB calls