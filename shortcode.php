<?php
require_once 'api.php';
require_once 'store.php';

add_shortcode( 'spring-slider', 'SPRINGAPIWP_render_slider' );

function SPRINGAPIWP_render_slider( $atts ){
  $data = SPRINGAPIWP_get_data('spring.txt');
  $key = $data[0];
  $siteValue = $data[1];
  $template = $data[2];
  $ids = explode("\n", $data[3]);

  $results = array();

  for ($i=0; $i < sizeof($ids); $i++) {
    array_push($results, SPRINGAPIWP_spring_listing($key, $ids[$i], false, $siteValue));
  }

  $remaining = 5 - sizeof($results);

  if ($remaining > 0) {
    $search_results = SPRINGAPIWP_spring_search($key, false, $siteValue);
    $search_results = array_slice($search_results, 0, $remaining);
    $results = array_merge($results, $search_results);
  }

  $html = "
    <pre class='spring-data-hidden' style='display: none !important;'>
      " . json_encode(array('template' => base64_encode($template), 'results' => $results)) . "
    </pre>
  ";

  return $html;
}

add_shortcode('quick-search', 'SPRINGAPIWP_render_quick_search');

function SPRINGAPIWP_render_quick_search ( $atts ) {
  if(isset($_GET['property_type']) || isset($atts['name'])) {
    //TO DO: pin in the advanced search bar
    $data = SPRINGAPIWP_get_data('quickSearch.txt');
    $key = $data[0];
    $siteValue = $data[1];
    $template = $data[2];

    $results = SPRINGAPIWP_quick_search($key, $_GET, isset($atts["name"]) ? $atts["name"] : "" ,false, $siteValue);

    //currently the Solid Earth API returns 20 by default
    $solidEarthPageLength = 20;

    $pageCount = floor($results["Count"] / $solidEarthPageLength);

    if(($results["Count"] % $solidEarthPageLength) !== 0 )
      $pageCount++;

    $serverURLArray = explode("?", "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", 2) ;
    $server_host = $serverURLArray[0];
    $qs = "?";
    $qs .= "quick_terms=" . (empty($_GET['quick_terms']) ? "" : $_GET['quick_terms']);
    $qs .= "&property_type=" . (empty($_GET['property_type']) ? "" : $_GET['property_type']);
    $qs .= "&keyword=" . (empty($_GET['keyword']) ? "" : $_GET['keyword']);
    $qs .= "&school=" . (empty($_GET['school']) ? "" : $_GET['school']);
    $qs .= "&min_bedrooms=" . (empty($_GET['min_bedrooms']) ? "" : $_GET['min_bedrooms']);
    $qs .= "&min_bathrooms=" . (empty($_GET['min_bathrooms']) ? "" : $_GET['min_bathrooms']);
    $qs .= "&min_list_price=" . (empty($_GET['min_list_price']) ? "" : $_GET['min_list_price']);
    $qs .= "&max_list_price=" . (empty($_GET['max_list_price']) ? "" : $_GET['max_list_price']);
    $qs .= "&sorting=" . (empty($_GET['sorting']) ? "" : $_GET['sorting']);
    $qs .= "&pagination=";

    for($i=0; $i < $pageCount; $i++) {
      $pageArray[$i]['url'] = str_replace(' ', '+', $server_host . $qs . $i);
      $pageArray[$i]['num'] = $i + 1;
    }

    $pageCurrentlyOn = empty($_GET['pagination']) ? 0 : $_GET['pagination'];

    $pageOffset = 3;
    $pageLimiter = 10;

    if($pageCount > $pageLimiter) {
      $pageArray[$pageOffset]['postfix'] = true;
      $pageArray[($pageCount - $pageOffset)]['prefix'] = true;

      for($i=$pageOffset; $i < ($pageCount - $pageOffset); $i++) {
        $pageArray[$i]['hidden'] = true;

        if($i == ($pageCurrentlyOn -1) || $i == ($pageCurrentlyOn +1)) {
          $pageArray[$i]['hidden'] = false;
        }
      }
    }

    if(($pageCurrentlyOn -1) < 0) {
      $pageArray[0]['previous'] = NULL;
    }
    else {
      $pageArray[0]['previous'] = $pageArray[$pageCurrentlyOn-1]['url'];
    }

    if(($pageCurrentlyOn+1) > ($pageCount-1)) {
      $pageArray[$pageCount-1]['next'] = NULL;
    }
    else {
      $pageArray[$pageCount-1]['next'] = $pageArray[$pageCurrentlyOn+1]['url'];
    }

    foreach ($results["listing"] as &$res) {
      foreach ($res["listingPricing"] as &$lprice)
      {
        $lprice = number_format($lprice);
      }
      foreach ($res["location"] as &$location)
      {
        foreach ($location as &$l) {
          if(strlen($l) !== 2) {
            $l = ucwords(strtolower($l));
          }
        }
      }
    }

    $pageArray[$pageCurrentlyOn]['selected'] = $pageCurrentlyOn;

    $rangeMax = ($pageCurrentlyOn+1)*$solidEarthPageLength;
    $rangeMin = ($rangeMax - $solidEarthPageLength) + 1;

    if($rangeMax > $results["Count"]) {
      $rangeMax = $results["Count"];
    }

    $pageGeneral['named'] = empty( $atts['name']);
    $pageGeneral['range'] = $rangeMax == 0 ? 0 : $rangeMin . '-' . $rangeMax;
    $pageGeneral['count'] = number_format($results["Count"]);
    $pageGeneral['currentPage'] = $pageCurrentlyOn + 1;

    $html = "";

    if(isset($_GET['property_type'])){
      $html .= SPRINGAPIWP_search_form('advanced');
    }

    $html .= "
      <pre class='spring-data-hidden' style='display: none !important;'>" .
        json_encode(array('pages' => $pageArray, 'pageInfo' => $pageGeneral, 'template' => base64_encode($template), 'results' => $results["listing"])) .
        "</pre>";

    return $html;
  }
  else {
    $html = SPRINGAPIWP_search_form('quick');

    return $html;
  }
}

function SPRINGAPIWP_search_form($searchType) {
  $serverURLArray = explode("?", "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", 2) ;
  $server_host = $serverURLArray[0];

  if($searchType === 'advanced') {
    $propertyTypes = array('Single Family Residence', 'Manufactured Home', 'Condominium', 'Townhouse');
    $bedroomTypes = array(1, 2, 3, 4, 5, 6);
    $bathroomTypes = array(1, 2, 3, 4, 5, 6);

    $html = '
        <form id="spring-advanced-search-form" class="spring-advanced-search-form spring-clearfix" action="/search" method="GET">
          <div class="spring-advanced-top-portion" id="spring-advanced-top-portion">
            <div class="spring-advanced-search-text-entries">
              <select id="spring-advanced-property-select" name="property_type" data-placeholder="Property Type">
                <option value="" disabled="">Property Type</option>';

                foreach($propertyTypes as $value) {
                  if($_GET['property_type'] == $value) {
                    $html .= '<option value="' . $value . '" selected="selected">' . $value . '</option>';
                  }
                  else {
                    $html .= '<option value="' . $value . '">' . $value . '</option>';
                  }
                }
              $html .= '</select>
              <br />

              <input type="text" id="spring-advanced-quick-terms" name="quick_terms" value="' . $_GET['quick_terms'] . '" placeholder="City, Zip, Neighborhood">
              <br />

              <input type="text" id="spring-advanced-keyword" name="keyword" value="' . $_GET['keyword'] . '" placeholder="Address, MLS#, Keywords">
              <br />
              <input type="text" id="spring-advanced-school" style="display:none" name="school" value="' . $_GET['school'] . '" placeholder="School">
            </div>

            <div class="spring-advanced-search-min-max">
              <select id="spring-advanced-min-bedrooms" name="min_bedrooms">
                <option value="0" selected="selected">Min Beds</option><option value="0">none</option>';

                foreach ($bedroomTypes as $value) {
                  if($_GET['min_bedrooms'] == $value) {
                    $html .= '<option value="' . $value . '" selected="selected">' . $value . '</option>';
                  }
                  else {
                    $html .= '<option value="' . $value . '">' . $value . '</option>';
                  }
                }

              $html .= '</select>

              <select id="spring-advanced-search-min-bathrooms" name="min_bathrooms">
                <option value="0" selected="selected">Min Baths</option><option value="0">none</option>';

                foreach ($bathroomTypes as $value) {
                  if($_GET['min_bathrooms'] == $value) {
                    $html .= '<option value="' . $value . '" selected="selected">' . $value . '</option>';
                  }
                  else {
                    $html .= '<option value="' . $value . '">' . $value . '</option>';
                  }
                }

              $html .= '</select>

              <br />

              <select id="spring-advanced-min-list-price" name="min_list_price">
                <option value="0" selected="selected">Min Price</option>
                <option value="0">none</option>';

                for($i = 100000; $i <= 20000000; $i += 50000) {
                  if($_GET['min_list_price'] == $i) {
                    $html .= '<option selected="selected" value=' . $i . '>$' . number_format($i) . '+</option>';
                  }
                  else {
                    $html .= '<option value=' . $i . '>$' . number_format($i) . '+</option>';
                  }
                }

              $html .= '</select>

              <select id="spring-advanced-max-list-price" name="max_list_price">
                <option value="" selected="selected">Max Price</option>
                <option value="">Max Price</option>';

                for($i = 100000; $i <= 20000000; $i += 50000) {
                  if($_GET['max_list_price'] == $i) {
                    $html .= '<option selected="selected" value=' . $i . '>$' . number_format($i) . '+</option>';
                  }
                  else {
                    $html .= '<option value=' . $i . '>$' . number_format($i) . '+</option>';
                  }
                }

              $html .= '</select>
            </div>
            <br />
          </div>

          <input type="hidden" name="pagination" value="0">
          <span class="spring-advanced-search-submit" style="float: right;" id="spring-search-field-toggle" onclick="hiddenCheck()" />More Search Fields</span>
          <input class="spring-advanced-search-submit" style="float: right;" type="submit" value="Submit Search">
        </form>
        <script type="text/javascript">
          function hiddenCheck() {
            var schoolDisplay = document.getElementById("spring-advanced-school");
            document.getElementById("spring-search-field-toggle").innerHTML = schoolDisplay.style.display === "none" ? "Less Search Fields" : "More Search Fields";
            schoolDisplay.style.display = schoolDisplay.style.display === "none" ? "" : "none";
            document.getElementById("spring-advanced-top-portion").style.height = schoolDisplay.style.display === "none" ? "110px" : "135px";
          }
        </script>
    ';
  }
  else {
    $onPage = strstr($server_host, '/search') ? '?quick_terms=&property_type=Single+Family+Residence&keyword=&min_bedrooms=0&min_bathrooms=0&min_list_price=0&max_list_price=&school=&sorting=created+desc&pagination=0' : 'search?quick_terms=&property_type=Single+Family+Residence&keyword=&min_bedrooms=0&min_bathrooms=0&min_list_price=0&max_list_price=&school=&sorting=created+desc&pagination=0';

    $html = '
      <form class="spring-quick-search-form" action="/search" method="GET">';
        $html .= '<input type="text" id="spring-quick_terms" name="quick_terms" class="spring-search-field spring-full" value="" placeholder="City, Zip, Neighborhood">
        <br />

        <select id="spring-quick-property-select" name="property_type" class="spring-search-field" data-placeholder="Property Type">
          <option value="" disabled="">Property Type</option>
          <option value="Single Family Residence">Single Family Residence</option>
          <option value="Manufactured Home">Manufactured Home</option>
          <option value="Condominium">Condominium</option>
          <option value="Townhouse">Townhouse</option>
        </select>
        <br />

        <input type="text" id="spring-keyword" name="keyword" class="spring-search-field spring-full" value="" placeholder="Address, MLS#, Keywords">
        <br />

        <div class="spring-min-max">
          <select id="spring-min-bedrooms" name="min_bedrooms" class="spring-quick-left-float">
            <option value="0" selected="selected">Min Beds</option><option value="0">none</option>
            <option value="1">1+ beds</option><option value="2">2+ beds</option>
            <option value="3">3+ beds</option><option value="4">4+ beds</option>
            <option value="5">5+ beds</option><option value="6">6+ beds</option>
          </select>

          <select id="spring-min-bathrooms" name="min_bathrooms" class="spring-quick-right-float">
            <option value="0" selected="selected">Min Baths</option><option value="0">none</option>
            <option value="1">1+ baths</option><option value="2">2+ baths</option>
            <option value="3">3+ baths</option><option value="4">4+ baths</option>
            <option value="5">5+ baths</option><option value="6">6+ baths</option>
          </select>
        </div>

        <div class="spring-min-max">
          <select id="spring-min-list-price" name="min_list_price" class="spring-quick-left-float">
            <option value="0" selected="selected">Min Price</option>
            <option value="0">none</option>';

            for($i = 100000; $i <= 20000000; $i += 50000) {
              $html .= '<option value=' . $i . '>$' . number_format($i) . '+</option>';
            }

          $html .= '</select>

          <select id="spring-max-list-price" name="max_list_price" class="spring-quick-right-float">
            <option value="" selected="selected">Max Price</option>
            <option value="">Max Price</option>';

            for($i = 100000; $i <= 20000000; $i += 50000) {
              $html .= '<option value=' . $i . '>$' . number_format($i) . '+</option>';
            }

          $html .= '</select>
        </div>';

        //only insert if advanced
        $html .= '<input type="hidden" name="school" value="">';

        $html .= '<br />

        <input type="hidden" name="pagination" value="0">
        <input type="hidden" name="sorting" value="created desc">
        <input id="spring-find-home-button" type="submit" value="Find Home">
        <p class="spring-advanced-search-link"><a href="' . $server_host . $onPage . '">Advanced Search</a></p>
      </form>
    ';
  }

  return $html;
}

add_shortcode( 'full-result', 'SPRINGAPIWP_render_full' );

function SPRINGAPIWP_render_full ( $atts ){
  $html = "";

  if(isset($_POST['spring-friend-post'])) {
    $to = $_POST['spring-post-friend-name'] . " <" . $_POST['spring-friend-email'] . ">";
    $fullAddress = $_POST['spring-post-fullAddress'];
    $message = $_POST['spring-post-message'];

    $link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $subject = "A Friend Thinks You'll Like " . $fullAddress;

    $content = "<p>$link</p>";
    $content .= "<p>Property: $fullAddress </p>";
    $content .= "<p>Message From A Friend: $message</p>";

    $headers[] = "Content-type: text/html" ;

    $status = wp_mail($to, $subject, $content, $headers);

    if($status) {
      $html .= "<p style='color: green;'>Email to friend was sent successfully.</p> <br />";
    }
    else {
      $html .= "<p style='color: red;'>Email to friend was not sent successfully. Please try again. </p> <br />";
    }
  }

  if(isset($_POST['spring-request-post'])) {
    $to = $_POST['spring-post-agent-email'];
    $fullAddress = $_POST['spring-post-fullAddress'];
    $mlsNumber = $_POST['spring-post-mls'];
    $personName = $_POST['spring-post-self-name'];
    $replyTo = $_POST['spring-post-self-email'];
    $appointment1 = $_POST['spring-post-self-appt1'];
    $appointment2 = $_POST['spring-post-self-appt2'];
    $comments = $_POST['spring-self-post-comments'];

    $subject = "Request for Information for " . $fullAddress;

    $content = "<p>Request for information for $fullAddress ,#$mlsNumber</p>";
    $content .= "<p>Sender Information</p>";
    $content .= "<p>Name: $personName</p>";
    $content .= "<p>Email Address: <a href='mailto:$replyTo'>$replyTo</a></p>";
    $content .= "<p>Preferred Appointments: $appointment1, $appointment2</p>";
    $content .= "<p>Comments: $comments</p>";

    $headers[] = "Content-type: text/html" ;

    $status = wp_mail($to, $subject, $content, $headers);

    if($status) {
      $html .= "<p style='color: green;'>Request for information sent successfully.</p> <br />";
    }
    else {
      $html .= "<p style='color: red;'>Request for information was not sent successfully. Please try again. </p> <br />";
    }
  }

  $data = SPRINGAPIWP_get_data('listingRender.txt');
  $key = $data[0];
  $siteValue = $data[1];
  $template = $data[2];
  $telephone = $data[3];
  $googleMapsKey = $data[4];

  $results = array();

  $listingID = get_query_var('listingID');

  array_push($results, SPRINGAPIWP_spring_listing($key, $listingID, false, $siteValue));

  if(isset($results[0][0]["listingPricing"]["listPrice"])) {
    $results[0][0]["listingPricing"]["listPrice"] = number_format($results[0][0]["listingPricing"]["listPrice"]);
  }

  $results[0][0]["phone"] = $telephone;
  $results[0][0]["googleMapsKey"] = $googleMapsKey;

  $html .= "
    <pre class='spring-data-hidden' style='display: none !important;'>
      " . json_encode(array('template' => base64_encode($template), 'results' => $results)) . "
    </pre>
  ";

  return $html;
}

add_shortcode('agent-listing', 'SPRINGAPIWP_agent_render');

function SPRINGAPIWP_agent_render ( $atts, $content, $sc ) {
  $data = SPRINGAPIWP_get_data('agentPage.txt');

  $key = $data[0];
  $siteValue = $data[1];
  $template = $data[2];

  $name = str_replace(" ", ",", $atts["name"]) ;

  $results = array();

  array_push($results, SPRINGAPIWP_agent_listing($key, $name, false));

  $html = "
    <pre class='spring-data-hidden' style='display: none !important;'>
      " . json_encode(array('template' => base64_encode($template), 'results' => $results)) . "
    </pre>
  ";

  return $html;
}

?>