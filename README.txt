## CF Get to Shortcode

The CF Get to Shortcode plugin adds the ability to add a shortcode with specific content depending on the GET variables added to the URL.  The plugin adds a Settings page to enter new shortcode keys and content for each URL GET variable desired.  The plugin also provides default options for when not GET variables are set.

### Usage

The CF Get to Shortcode plugin uses the WordPress shortcode functionality to output content set on the CF Get to Shortcode options page.  The shortcode can be used anywhere shortcodes are accepted.  

The following is an example of a CF Get to Shortcode shortcode to be added:
	
	[cfgts name="shortcode_name"]
	
A help button has also been provided on each of the shortcode items to demonstrate exactly what the shortcode should look like when entering it into content.

### Entering a new GET section

To enter a new GET section, on the settings page 

1.  Click the "Add New Get Option" button at the bottom of the page.  This will create a new section directly above the Default section.
2.  Next enter the values for the GET variables.  The only required field is the Key.  Both value and description are optional
3.  Click the "Save" button next to the Value input field
4.  Click on the "Add Shortcode" button to add a new shortcode
5.  Add a shortcode name into the "Shortcode Name" field
6.  Add a value into the "Shortcode Value" field
7.  Once all changes are complete, click the "Save Settings" button to save all changes

### Adding a global shortcode

The "Add Global Shortcode" button will add a shortcode to each of the GET key areas, and also to the default area.

To enter a global shortcode:

1.  Click the "Add Global Shortcode" button
2.  A new field will appear to enter the shortcode name
3.  Enter a shortcode name in the field
4.  Click the "GO" button (A new shortcode entry field will be added to each section)
5.  Enter the desired shortcode value into each section
