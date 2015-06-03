<?php

function SPRINGAPIWP_spring_endpoint($endpoint, $sandbox = true, $site = 'baarmls') {
  $url = "http://api.solidearth.com";
  $url .= $sandbox ? "/sandbox" : "";
  $url .= "/v1";
  $url .= "/" . $endpoint;
  $url .= "/" . $site;

  return $url;
}

function SPRINGAPIWP_get_json($key, $url, $params = '') {
  $url .= "?expand=true&format=JSON";
  $url .= "&api_key=" . $key;
  $url .= $params;

  $url = str_replace(' ', '+', $url);
  $url = str_replace('&amp;', '&', $url);

  $data = file_get_contents(trim($url));
  return json_decode($data, true);
}

function SPRINGAPIWP_spring_search($key, $sandbox = true, $site = 'baarmls') {
  $url = SPRINGAPIWP_spring_endpoint('search', $sandbox, $site);
  $sort = "&sortOption=listPriceDesc";
  return SPRINGAPIWP_get_json($key, $url, $sort)["listing"];
}

function SPRINGAPIWP_spring_listing($key, $id, $sandbox = true, $site = 'baarmls') {
  $url = SPRINGAPIWP_spring_endpoint('search', $sandbox, $site);
  $qs = "&sortOption=listPriceDesc";
  $qs .= "&_keywordsAll=mls_" . $id;

  $results = SPRINGAPIWP_get_json($key, $url, $qs);
  return $results["listing"];
}

function SPRINGAPIWP_agent_listing($key, $name, $sandbox = true, $site = 'baarmls') {
  $url = SPRINGAPIWP_spring_endpoint('search', $sandbox, $site);
  $qs = "&sortOption=listPriceDesc";
  $qs .= "&_keywordsAll=" . $name;

  $results = SPRINGAPIWP_get_json($key, $url, $qs);
  return $results["listing"];
}

function SPRINGAPIWP_quick_search($key, $formValues, $sandbox = true, $site = 'baarmls') {
  $url = SPRINGAPIWP_spring_endpoint('search', $sandbox, $site);

  $processedKeywords = trim($formValues['quick_terms'] . ' ' . $formValues['keyword'] . ' ' . $formValues['school']);
  $processedKeywords = preg_replace('/( )+/', ' ', $processedKeywords);

  $allTerms = str_replace(' ', ',' , $processedKeywords);

  $qs = "&ListPriceMin=" . (empty($formValues['min_list_price']) ? "" : $formValues['min_list_price']);
  $qs .= "&ListPriceMax=" . (empty($formValues['max_list_price']) ? "" : $formValues['max_list_price']);
  $qs .= "&BathsTotalMind=" . (empty($formValues['min_bathrooms']) ? "" : $formValues['min_bathrooms']);
  $qs .= "&BedsTotalMin=" . (empty($formValues['min_bedrooms']) ? "": $formValues['min_bedrooms']);
  $qs .= "&PropertyTypeIn=" . (empty($formValues['property_type']) ? "" : $formValues['property_type']);
  $qs .= "&_keywordsAll=" . (empty($allTerms) ? "" : $allTerms);
  $qs .= "&sortOption=" . (empty($formValues['sorting']) ? "" : $formValues['sorting']);
  $qs .= "&page=" . $formValues['pagination'];

  $results = SPRINGAPIWP_get_json($key, $url, $qs);

  return $results;
}

?>