<?php
  function SPRINGAPIWP_set_data($data, $fileName) {
    $SEP = '$SPRING$';

    $contents = join($SEP, $data);

    $file = fopen(plugin_dir_path( __FILE__ ) . $fileName, 'w');
    fwrite($file, $contents);
    fclose($file);
  }

  function SPRINGAPIWP_get_data($fileName) {
    $SEP = '$SPRING$';
    $contents = file_get_contents(plugin_dir_path( __FILE__ ) . $fileName);

    return explode($SEP, $contents);
  }

?>