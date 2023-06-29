# somateiohfaistos.gr
Custom code for somateiohfaistos.gr CRM

## Login related functions

- **v_getUrl()** - get the current URL of a page
- **v_forcelogin()** - checks if a user is logged in and redirects them to the login page if they are not
- **redirect_admin()** - redirects the user to the homepage after login, specifically for administrators
  
## Functions

**extend_admin_search()** - extend the admin search to include address, city, and state

**teo_filter_title()** - modifies the title of posts with the '**clients**' post type by appending additional client information retrieved from custom fields.

Using the following functions, we remove comment support from our wordpress.

- **teo_remove_admin_menus()**
- **teo_remove_comment_support()**
- **teo_remove_comments_admin_bar()**

**teo_remove_divi_project_post_type()** - remove 'project' CPT installed with theme Divi 

## Shortcodes

**[show-tameio]** - Show all receipts from the selected dates (default last seven days) and display them as a list, with basic information (name, date, amount, invoice number, client). Also calculate a total amount from the displayed receipts.

**[show-apodeikseis]** -  Show all receipts, with pagination, 30 per page. Each receipt is display as a card (name, address, date, amount). Each card receipt is clickable, which redirects to a printable form of the receipt.

**[apodeikseis-info]** - Show all receipts returned from a search, in a list style.

**[allfiles]** - show files attached to a client

**[maintenancehistory]** - show client's maintenance history.

**[image-gallery]** - show client's image gallery

**[phone_home]** - display client's home phone

**[phone_work]** - display client's work phone

**[phone_other]** - display client's other phone

**[mobile_personal]** - display client's personal mobile

**[mobile_work]** - display client's work mobile

**[mobile_other]** - display client's other mobile

**[client-info]** - show a list of all clients, with pagination, ready to print.

**[apd-fullname]** - show client's fullname

**[apd-fulladdress]** - show client's full address

**[EditApodeiksi]** - link to edit a receipt in backend

**[OlesApodeikseis]** - link to show all receipts in backend

## ACF Related functions
- acf_load_member_info() - load member info based on id  
- apd_check_name() - check field name before saving
- apd_check_lastname() - check field lastName before saving
- apd_check_address() - check field address before saving
