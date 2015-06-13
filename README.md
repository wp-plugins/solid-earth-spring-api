# Solid Earth SPRING API Plugin

This repository is the source for the plugin.

I recommend using Vagrantpress to create an environment in which to
work on it.

# Requirements
  - Node
  - NPM
  - Gulp
  - Bower
  - CoffeeScript

# Setup instructions

```bash
npm install
bower install
gulp
```

Zip up the top level folder as 'spring_api.zip', then upload the zip to via Plugins > Add New > Upload Plugin.

# You'll also need:
  - A valid API key from SolidEarth (https://developer.solidearth.com)
  - The docs (https://developer.solidearth.com/docs)

Plug the API key into the Wordpress backend (there should be a tab in the Admin Panel called "Spring Slider").  They'll probably want that changed to Solid Earth Slider or something but not sure atm.

Data is persisted to a text file.

API docs are kinda old, we're currently using an undocumented API parameter for Listing Key lookups.

# Included Shortcodes

[spring-slider] : Gallery shortcode
[quick-search] : Searching function
[full-result] : Renders the full details of a particular listing
[agent-listing name=""] : Displays all the name of an agent with a particular name within the defined site region

# Deployment

The file is enormous when deployed with everything in the plugin directory, so in the directory above, run the following command, substituting if you have named the project something other than spring_api to remove the node modules from the final deploy.

zip -r spring_api.zip spring_api/* -x /spring_api/node_modules/*

# Hints

The initial developer of the plugin was obsessessed with Coffeescript, so if you need to edit the js files, hit the coffee folder instead then edit the Coffeescript with Gulp running in the background to automatically build the Coffeescript into Javascript.

The plugin currently assumes hardcode pages named 'property' and 'search'.

In the admin section it says "Listing Keys" because that's what the users are expecting them to be called (referring to the MLS ID, which is like a 6-digit number) - however, in the API docs, "Listing Key" refers to the internal UUIDs for listings.

Make sure you have an API key configured.

Make sure you have write permissions in whatever directory the plugin is running in.

Make sure the template isn't messed up.