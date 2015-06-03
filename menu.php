<?php
include plugin_dir_path( __FILE__ ) . 'store.php';

add_action('admin_menu', 'add_menu');

function add_menu() {
  add_menu_page( "Spring Slider", "Spring Slider", "publish_posts", "spring-api/main", "SPRINGAPIWP_menu", '', 6);
  add_submenu_page( "spring-api/main", "Search", "Search", "publish_posts", "spring-api/quick", "SPRINGAPIWP_quick_menu");
  add_submenu_page( "spring-api/main", "Listing Details", "Listing Details", "publish_posts", "spring-api/listing", "SPRINGAPIWP_listing_menu");
  add_submenu_page( "spring-api/main", "Agent Listings", "Agent Listings", "publish_posts", "spring-api/agent", "SPRINGAPIWP_agent_menu");
}

function SPRINGAPIWP_menu() {
  if(isset($_POST["main"])) {
    $apikey = $_POST["apikey"];
    $sitename = $_POST["siteselect"];
    $template = stripslashes($_POST["template"]);
    $ids = $_POST["ids"];

    $data = array($apikey, $sitename, $template, $ids);

    SPRINGAPIWP_set_data($data, 'spring.txt');
  }

  $data = SPRINGAPIWP_get_data('spring.txt');

  $default_template = '
    <div class="spring-slider">
      <ul>
        {{#results}}
          <li>
            <img src="{{img}}" />
            {{#location}}{{#address}}
              <p>{{StreetNumber}} {{StreetName}}</p>
              <p>{{City}}, {{StateOrProvince}}</p>
            {{/address}}{{/location}}

            {{#listingPrice}}
              <p>${{listPrice}}</p>
            {{/listingPrice}}
          </li>
        {{/results}}
      </ul>
    </div>

    <script>
      SpringPlugin
      .jQuery(".spring-slider")
      .unslider({dots: true});
    </script>
  ';

  $siteValue = $data[1];
  $template = $data[2];

  if ($template == '') {
    $template = $default_template;
  }

  $html = '
    <h1>SolidEarth SPRING Slider</h1>
    <form action="" method="POST">

      <br><br>'. SPRINGAPIWP_siteSelect($siteValue) . '

      <h2>API Key:</h2>
      <input type="text" name="apikey" value="' . $data[0] . '">
      <br>

      <h2>Template:</h2>
      <textarea name="template" cols="50" rows="5">' . $template . '</textarea>

      <h2>Listing Keys:</h2>
      <textarea name="ids" cols="50" rows="5">' . $data[3] . '</textarea>

      <br>
      <input type="hidden" name="main" value="main" />
      <input type="submit">
    </form>
  ';

  echo $html;
}

function SPRINGAPIWP_listing_menu() {
  if(isset($_POST["listing"])) {
    $apikey = $_POST["apikey"];
    $sitename = $_POST["siteselect"];
    $template = stripslashes($_POST["template"]);
    $ids = "";

    $data = array($apikey, $sitename, $template, $ids);

    SPRINGAPIWP_set_data($data, 'listingRender.txt');
  }

  $data = SPRINGAPIWP_get_data('listingRender.txt');

  $default_template = '
  <div class="listing-overarch">
      {{#results}}
        {{#.}}
          <div class="listing-photo-gallery">
            <div id="listing-current-photo">
            </div>
            <div class="listing-detail-photos">
              <ul>
              {{#Media}}
                <li><img class="listing-direct-img" src="{{file}}" /></li>
              {{/Media}}
              </ul>
            </div>
          </div>
          <div class="clearfix"></div>

          <div class="listing-address-information">
            <p>{{location.address.StreetNumber}} {{location.address.StreetName}} | {{location.address.City}}, {{location.address.StateOrProvince}} {{location.address.PostalCode}}</p>
            <p>${{listingPricing.listPrice}}</p>
          </div>

          <div class="listing-property-description">
            <h1>Property Description</h1>
            <p>{{{remarks.publicRemarks}}}</p>
          </div>

          <div class="listing-full-block">
            <div class="listing-property-full-address">
              <h1>Property Details for {{location.address.StreetNumber}} {{location.address.StreetName}}, {{location.address.City}}, {{location.address.StateOrProvince}} {{location.address.PostalCode}}</h1>
              <ul>
                <li>Property type: {{property.Type}}, {{property.SubType}}</li>
                <li>Bedrooms total: {{structure.BedroomsTotal}}</li>
                <li>Bathrooms total: {{structure.BathroomsTotal}}</li>
                <li>MLS&reg; #: {{ListingId}}</li>
              </ul>
            </div>

            <div class="listing-interior-information">
              <h1>Interior Information</h1>
              <ul>
                <li>Bedrooms total: {{structure.BedroomsTotal}}</li>
                <li>Bathrooms total: {{structure.BathroomsTotal}}</li>
                <li>Bathrooms (full): {{structure.BathroomsFull}}</li>
                <li>Bathrooms (half): {{structure.BathroomsHalf}}</li>
                <li>Bathrooms (three-quarter): {{structure.BathroomsThreeQuarter}}</li>
                <li>Living Area: {{structure.livingArea}}</li>
                <li>Cars: {{structure.carsTotal}}</li>
              </ul>
            </div>

            <div class="listing-features">
              <h1>Features</h1>
              <ul>
              {{#each Features}}
                <li>{{@key}}: {{this}}</li>
              {{/each}}
              </ul>
            </div>

            <div class="listing-school-information">
              <h1>School Information</h1>
              <ul>
                <li>Elementary: {{location.school.elementarySchool}}</li>
                <li>Middle: {{location.school.middleOrJuniorSchool}}</li>
                <li>High: {{location.school.highSchool}}</li>
              </ul>
            </div>

            <div class="listing-agent">
              <h1>Listing Agent</h1>
              <ul>
                <li>Agent Name: {{agentOffice.ListAgent.FullName}}</li>
                <li>Agent Phone: {{agentOffice.ListAgent.OfficePhone}}</li>
                <li>Listing Office: {{agentOffice.ListOffice.Name}}</li>
                <li>Listing Office Phone: {{agentOffice.ListOffice.Phone}}</li>
                <li>Listing Office Email: <a href="mailto:{{agentOffice.ListOffice.Email}}?Subject=MLS%20#%20{{ListingId}}" target="_top">{{agentOffice.ListOffice.Email}}</a></li>
              </ul>
            </div>
          </div>
        {{/.}}
      {{/results}}
  </div>
  ';

  $siteValue = $data[1];
  $template = $data[2];

  if ($template == '') {
    $template = $default_template;
  }

  $html = '
    <h1>SolidEarth Listing Details</h1>
    <form action="" method="POST">

      <br><br>' . SPRINGAPIWP_siteSelect($siteValue) . '

      <h2>API Key:</h2>
      <input type="text" name="apikey" value="' . $data[0] . '">
      <br>

      <h2>Template:</h2>
      <textarea name="template" cols="50" rows="5">' . $template . '</textarea>

      <br>
      <input type="hidden" name="listing" value="listing" />
      <input type="submit">
    </form>
  ';

  echo $html;
}

function SPRINGAPIWP_quick_menu() {
  if(isset($_POST["quick"])) {
    $apikey = $_POST["apikey"];
    $sitename = $_POST["siteselect"];
    $template = stripslashes($_POST["template"]);
    $ids = "";

    $data = array($apikey, $sitename, $template, $ids);

    SPRINGAPIWP_set_data($data, 'quickSearch.txt');
  }

  $data = SPRINGAPIWP_get_data('quickSearch.txt');

  $default_template = '
    <div class="quick-search">
      <div class="search-options" style="float:right;">
        <select form="advanced-search-form" name="sorting" id="sorting-select" onchange="this.form.submit()">
          <option value="ListPrice">Price Low to High</option>
          <option value="ListPrice desc">Price High to Low</option>
          <option value="created desc" selected="selected">Newest</option>
          <option value="created">Oldest</option>
        </select>
      </div>
      <div class="clear-line" />
      <div class="quick-search-background">
        <ul class="quick-search-pages">
          <li class="quick-left-float">Page {{pageInfo.currentPage}}, results {{pageInfo.range}} of {{pageInfo.count}}</li>
          {{#pages}}
            {{#previous}}
              <li><a href="{{{.}}}">Previous<a/></li>
            {{/previous}}
              {{#selected}}
                <li><a style="text-decoration:underline !important;" href="{{{url}}}">{{num}}</a></li>
              {{/selected}}
              {{^selected}}
                <li><a href="{{{url}}}">{{num}}</a></li>
              {{/selected}}
            {{#next}}
              <li><a href="{{{.}}}">Next</a></li>
            {{/next}}
          {{/pages}}
        </ul>
      </div>
      <div class="clear-line" />
      <ul class="quick-search-listings">
        {{#results}}
          <li class="quick-search-listing">
            <img class="quick-search-photo-wrapper" src="{{img}}" />
            {{#listingPricing}}
              <p class="quick-search-price">${{listPrice}}</p>
            {{/listingPricing}}

            {{#location}}{{#address}}
              <p class="quick-search-address">{{StreetNumber}} {{StreetName}}</p>
              <p class="quick-search-address">{{City}}, {{StateOrProvince}}</p>
            {{/address}}{{/location}}

            {{#structure}}
              <p class="quick-search-rooms">{{BedroomsTotal}} Bed, {{BathroomsTotal}} Bath</p>
            {{/structure}}

            {{#property}}
              <p>Property Type: {{Type}}, {{SubType}}</p>
            {{/property}}

            {{#agentOffice}}
              {{#ListAgent}}
                <p>Courtesy of {{FullName}},
              {{/ListAgent}}
              {{#ListOffice}}
                {{Name}}</p>
              {{/ListOffice}}
            {{/agentOffice}}
          </li>
        {{/results}}
      </ul>
      <div class="clear-line" />
      <div class="quick-search-background">
        <ul class="quick-search-pages">
          <li class="quick-left-float">Page {{pageInfo.currentPage}}, results {{pageInfo.range}} of {{pageInfo.count}}</li>
          {{#pages}}
            {{#previous}}
              <li><a href="{{{.}}}">Previous<a/></li>
            {{/previous}}
              {{#selected}}
                <li><a style="text-decoration:underline !important;" href="{{{url}}}">{{num}}</a></li>
              {{/selected}}
              {{^selected}}
                <li><a href="{{{url}}}">{{num}}</a></li>
              {{/selected}}
            {{#next}}
              <li><a href="{{{.}}}">Next</a></li>
            {{/next}}
          {{/pages}}
        </ul>
      </div>
    </div>
  ';

  $siteValue = $data[1];
  $template = $data[2];

  if ($template == '') {
    $template = $default_template;
  }

  $html = '
    <h1>SolidEarth Search</h1>
    <form action="" method="POST">

      <br><br>' . SPRINGAPIWP_siteSelect($siteValue) . '

      <h2>API Key:</h2>
      <input type="text" name="apikey" value="' . $data[0] . '">
      <br>

      <h2>Template:</h2>
      <textarea name="template" cols="50" rows="5">' . $template . '</textarea>

      <br>
      <input type="hidden" name="quick" value="quick" />
      <input type="submit">
    </form>
  ';

  echo $html;
}

function SPRINGAPIWP_siteSelect ( $curVal ) {
  $siteTypes = ['gbrar', 'gcar', 'mlsbox', 'tuscar', 'mibor', 'baarmls', 'sandicor', 'rafgc'];
  $siteSelect = '<h2>Site Select:</h2>
  <select name="siteselect">';

  foreach($siteTypes as $selectVal) {
    if ($curVal == $selectVal) {
      $siteSelect .= '<option value="' . $selectVal . '" selected="selected">' . $selectVal . '</option>';
    }
    else {
      $siteSelect .= '<option value="' . $selectVal . '">' . $selectVal . '</option>';
    }
  }

  $siteSelect .= '</select><br />';

  return $siteSelect;
}

function SPRINGAPIWP_agent_menu() {
  if(isset($_POST["agent"])) {
    $apikey = $_POST["apikey"];
    $sitename = $_POST["siteselect"];
    $template = stripslashes($_POST["template"]);
    $ids = "";

    $data = array($apikey, $sitename, $template, $ids);

    SPRINGAPIWP_set_data($data, 'agentPage.txt');
  }
  $data = SPRINGAPIWP_get_data('agentPage.txt');

  $default_template = '
    <div class="quick-search">
      <div class="clear-line" />
      <ul class="quick-search-listings">
        {{#results}}
          {{#.}}
            <a href="/property/{{ListingId}}/{{location.address.StreetNumber}}-{{location.address.StreetName}}-{{location.address.City}}-{{location.address.StateOrProvince}}"><li class="quick-search-listing">
              <img class="quick-search-photo-wrapper" src="{{Media.1.file}}" />
              {{#listingPricing}}
                <p class="quick-search-price">${{listPrice}}</p>
              {{/listingPricing}}

              {{#location}}{{#address}}
                <p class="quick-search-address">{{StreetNumber}} {{StreetName}}</p>
                <p class="quick-search-address">{{City}}, {{StateOrProvince}}</p>
              {{/address}}{{/location}}

              {{#structure}}
                <p class="quick-search-rooms">{{BedroomsTotal}} Bed, {{BathroomsTotal}} Bath</p>
              {{/structure}}`

              {{#property}}
                <p>Property Type: {{Type}}, {{SubType}}</p>
              {{/property}}

              {{#agentOffice}}
                {{#ListAgent}}
                  <p>Courtesy of {{{FullName}}},
                {{/ListAgent}}
                {{#ListOffice}}
                  {{{Name}}}</p>
                {{/ListOffice}}
              {{/agentOffice}}
            </li></a>
          {{/.}}
        {{/results}}
        {{^results}}
          <p>No listings available at this time.</p>
        {{/results}}
      </ul>
      <div class="clear-line" />
    </div>
  ';

  $siteValue = $data[1];
  $template = $data[2];

  if ($template == '') {
    $template = $default_template;
  }

  $html = '
    <h1>SolidEarth Search</h1>
    <form action="" method="POST">

      <br><br>' . SPRINGAPIWP_siteSelect($siteValue) . '

      <h2>API Key:</h2>
      <input type="text" name="apikey" value="' . $data[0] . '">
      <br>

      <h2>Template:</h2>
      <textarea name="template" cols="50" rows="5">' . $template . '</textarea>

      <br>
      <input type="hidden" name="agent" value="agent" />
      <input type="submit">
    </form>
  ';

  echo $html;
}

?>