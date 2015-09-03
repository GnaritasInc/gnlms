# gnlms: A WordPress plugin implementing an LMS

The goal of gnlms is to implement an LMS ("Learning Management System") to allow hosting of SCORM-compliant online training/e-learning courses on WordPress sites. We (Gnaritas Inc.) developed the code originally for a stand-alone user-facing site, and have re-factored it into a WordPress plugin, with the LMS administration functions in the WordPress back end.

There's still work to be done, but we decided to make this plug-in available in its current state to allow others to adapt it for their own purposes.

## Features

- Site administrators can upload course files from the WordPress admin panel.
- Course users can register for and take courses from the front end.
- Email notifications can be supplied for course activities such as course start, completion, and (if the course includes a pass/fail metric) failure.
- Reports of user and course activity data can be viewed within the LMS or exported in CSV format.
- Action and filter hooks allow for customization.

## Installation and set-up

1. Upload and extract the archive to your WordPress plugins directory and activate the plugin on the "Plugins" admin panel.
2. After installation, set up the following options on the "Settings>General" admin panel:

    - **Course Path** : The physical path to the directory where you will upload course files. This directory should be writable.
    - **Course URL** : The request URL pointing to the course path directory above.
    - **Course Image URL** : The request URL for a directory with course images or screenshots for display in course listings.
--  **Course Detail Page** : The URL of a page on your site that will display individual course listings using the [gnlms\_course\_detail] shortcode.

3. Make the classes/log directory under the plugin root writable to allow the plugin to write email notification and SCORM data-saving logs.

## Adding courses

Courses are listed under "LMS Courses" admin menu. Courses can be added manually using the "Add New" link, or by uploading course files in a ZIP archive.

### Adding manually

- Select "Add New" from the "LMS Courses" admin menu.

### Uploading course files

Course files can be uploaded by choosing the "Upload" link under the "LMS Courses" admin menu:

- Select a ZIP archive containing the course files.
- Enter a directory where the ZIP archive will be extracted. This should be a subdirectory of the one you specified for the "Course Path" option. It will be created if it doesn't exist. **Note**: If the directory _does_ exist, the ZIP archive will be extracted there, overwriting any existing files with the same names as those contained in the archive.
- Do one of the following:
    - Enter the course title and launch URL in the text boxes provided 
    
    **OR**

    - Choose "Get title and launch URL from manifest":
        - The manifest should be at the archive root and, per the SCORM specification, be named "imsmanifest.xml".
        - The manifest should specify a single SCO, i.e. it should have a single "organization" element referencing a single "resource" element of type "sco".
        - Only the SCORM 1.2 manifest specification is supported.
- A new course record will be created once the files are successfully uploaded and extracted.

## Displaying course information

Course information can be displayed on site pages using the following shortcodes (among others):

- **[gnlms\_available\_courses]**: Displays a list of courses in the system including each course's title, description and image, with links to the individual course pages.
- **[gnlms\_course\_detail]**: Displays information about an individual course. Authenticated users have an option to register for the course, launch it, if they're already registered, or see their completion date and score, if they've already completed it.
- **[gnlms\_user\_current\_courses]**: Displays information on courses for which the current user is registered with a link to launch the course.
- **[gnlms\_user\_completed\_courses]**: Displays information on courses the current user has completed, including the completion date and score.

See the code in classes/gnlms\_LMS.php and classes/gnlms\_Listwidget.php for more shortcodes.

## Registering users for courses

Authenticated blog users can self-register for courses. Users can also be registered for courses by administrators from the WordPress admin panel.

### Registering from the user context

-  **On a user's profile page** : 
  -- Scroll down below the WordPress profile fields and click "Manage Course Registrations".
-  **From the "Users" admin panel listing** : 
  -- Mouse over the user's record and click "Courses".
- Click "Register this user for new courses" under "Registered Courses"
- Select a course from the dialog and click "OK". (Courses for which the user is already registered or has completed are excluded from the available courses.)

### Registering from the course context

- From the "LMS Courses" admin panel, navigate to the course's edit page
- At the bottom of the page below the course edit form, click "Register new users for this course" under "Registered Users"
- Enter the first few letters of user's last name in the search dialog and click "Search"
- Select one or more matched users (or check "Select All") and click "Register Users".

## Email notifications

WordPress administrators may register for email notifications using the checkboxes under "LMS Alert Preferences" in the "Personal Options" section of their profile page.

**Note** :

- Email notifications aren't sent immediately; they're queued for delivery and sent by an hourly WordPress "cron" job. 
- WordPress' "cron" implementation depends on site activity. If your site isn't very busy, you can set up a "real" server cron to ensure there's at least one WordPress request every hour.

## Future development goals

- Re-implement course data model as a WordPress custom post type.
- Continue to refactor legacy code using standard WordPress API functions as much as possible.
- Implement validation for client-side SCORM adapter.
- Add a templating system for course listing output.