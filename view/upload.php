<?php

$d = 'aliyun-oss';

use OSS\WP\Config;
use OSS\WP\Upload;

?>

<div class="wrap" style="margin: 10px;">

    <h1><?php echo __('Aliyun OSS Upload', $d) ?></h1>
    <p><?php echo __('Bulk upload existing files to bucket', $d) ?></p>

    <?php

    $basedir = wp_upload_dir()['basedir'];
    echo '<p>'.__('Files under ', $d).'<code>'.$basedir.'</code></p>';

    function listDirContents($dir, $pre = '')
    {
        $results = array();
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = $dir.'/'.$value;
            if (!is_dir($path)) {
                $results[] = str_replace('./', '', $pre.'/'.$value);
            }
            else if ($value != "." && $value != "..") {
                $results = array_merge($results, listDirContents($path, $pre.'/'.$value));
            }
        }
        return $results;
    };

    function shouldUploadFile($file)
    {
        if (preg_match('/-[0-9]+x[0-9]+\..+$/', $file)) {
            if (isset($_POST['skip-thumbnail'])) {
                return false;
            }
        }
        return true;
    }

    if (!empty($_POST)) {
        echo '<pre>';
        foreach (listDirContents($basedir) as $file) {
            if (shouldUploadFile($file)) {
                $type = wp_check_filetype($file);
                $upload = array(
                    'file' => $basedir.$file,
                    'type' => $type['type'],
                );
                $result = Upload::getInstance()->uploadOriginToOss($upload);
                echo $file.' ... '.(isset($result['error']) ? '<span style="color: #f44336">Error</span>' : '<span style="color: #4caf50">Done</span>').'<br>';
            } else {
                echo $file.' ... '.'<span style="color: #ff9800">Skipped</span>'.'<br>';
            }
        }
        echo '</pre>';
    } else {
        echo '<pre>';
        foreach (listDirContents($basedir) as $file) {
            echo $file.'<br>';
        }
        echo '</pre>';

        ?>
        <form name="form1" method="post" action="<?php echo wp_nonce_url(Config::$settingsUrl.'-upload'); ?>">

            <label for="skip-thumbnail">
                <input name="skip-thumbnail" type="checkbox" id="skip-thumbnail">
                <?php echo __('Skip Thumbnails', $d) ?>
            </label>

            <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Upload', $d)?>"></p>
        </form>
        <?php
    }

    ?>
</div>
