<?php
$base_dir = dirname(__FILE__);
$config = include $base_dir . '/config.php';
$black_list = $config['black_list'];
$project_dir = $config['project'];
define('PROJECTDIR', $project_dir);
define('FIRSTUPLOAD', $config['first_upload']);
define('TO', $config['to']);
define('UPLOAD_URL_API', $config['url']);
ini_set('date.timezone', 'Asia/Shanghai');
echo UPLOAD_URL_API;
function curl_post($path)
{
    if (class_exists('\CURLFile')) {
        //   大于PHP5.6
        $file = new \CURLFile($path, "application/octet-stream");
    } else {
        //   小于PHP5.6
        $file = "@" . realpath($path);
    }
    $to_file = str_replace(PROJECTDIR, TO, $path);
    $time = time();
    $source = 'ovs';
    $post_fields = array(
        'file' => $file,
        'to' => $to_file,
        'source' => $source,
        'time' => $time,
        'sign' => md5(md5($time) . $source)
    );

    $ch = curl_init();
    $params[CURLOPT_URL] = UPLOAD_URL_API;
    $params[CURLOPT_HEADER] = false;
    $params[CURLOPT_RETURNTRANSFER] = true;
    $params[CURLOPT_FOLLOWLOCATION] = true;
    $params[CURLOPT_POST] = true;
    $params[CURLOPT_POSTFIELDS] = $post_fields;
    curl_setopt_array($ch, $params);
    $content = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);
    if ($curl_errno > 0) {
        echo json_encode([$curl_errno, $curl_error, UPLOAD_URL_API, $path]);
        exit;
    }
    echo json_encode(['remote_path' => $to_file, 'response' => $content]);
    return true;
}

function list_file($project_dir, $try_time, $black_list)
{
    //1、首先先读取文件夹
    $temp = scandir($project_dir);
    //遍历文件夹
    foreach ($temp as $v) {
        foreach ($black_list['dir'] as $b_v) {
            //在黑名单中
            if (strpos($v, $b_v) !== false) {
                continue 2;
            }
        }

        $dir_or_file = $project_dir . '/' . $v;
        if (is_dir($dir_or_file)) {
            if ($v == '.' || $v == '..') {
                continue;
            }
            list_file($dir_or_file, $try_time, $black_list);
        } else {
            $file_time = date("Y-m-d H:i:s", filemtime($dir_or_file));
            //1、把文件的修改时间放入env中
            if ($try_time == 1 && getenv("$dir_or_file") == false) {
                putenv("$dir_or_file=$file_time");
            }
            //2、判断当前文件的修改时间和文件中的修改时间是否一致，不一致更新env，上传文件到服务器
            //不在黑名单中
            if (in_array($dir_or_file, $black_list['file']) === false && getenv("$dir_or_file") != $file_time && $try_time > 1) {
                curl_post($dir_or_file);
                putenv("$dir_or_file=$file_time");
                echo $dir_or_file . '------' . $file_time . PHP_EOL;

            }
            if (in_array($dir_or_file, $black_list['file']) === false && $try_time == 1 && FIRSTUPLOAD) {
                curl_post($dir_or_file);
                putenv("$dir_or_file=$file_time");
                echo $dir_or_file . '------' . $file_time . PHP_EOL;
            }

        }
    }
}


for ($try_time = 1; true; $try_time++) {
    list_file($project_dir, $try_time, $black_list);
    sleep(1);
    //echo '第'.$try_time.'次遍历'."\r\n";
}
