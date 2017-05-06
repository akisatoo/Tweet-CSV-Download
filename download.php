<?php
require 'TwistOAuth.phar';
require_once 'config.php';

//csvダウンロードの処理
if ($_POST['download']) {
  $connection = new TwistOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);

  $csvArr = [];
  $tweets_params = ['q' => $_POST['download'] ,'count' => TWEET_MAX_COUNT];

  for ($i = 0; $i < REQUEST_COUNT; $i++) {
    $tweets_obj = $connection->get('search/tweets', $tweets_params);
    $tweets = $tweets_obj->statuses;

    // foreach でまわす
    foreach ($tweets as $tweet) {
      $datetime = date('Y/m/d', strtotime($tweet->created_at));
      $url = sprintf('https://twitter.com/%s/status/%s/', $tweet->user->screen_name, $tweet->id_str);

      $tweet_data = [];
      $tweet_data['date'] = $datetime;
      $tweet_data['tweet'] = $tweet->text;
      $tweet_data['url'] = $url;

      array_push($csvArr,array($tweet_data['date'],$tweet_data['tweet'],$tweet_data['url']));
    }

    // 先頭の「?」を除去
    $next_results = preg_replace('/^\?/', '', $tweets_obj->search_metadata->next_results);

    // next_results が無ければ処理を終了
    if (!$next_results) {
        break;
    }

    // パラメータに変換
    parse_str($next_results, $tweets_params);
  }

  try {

    //CSV形式で情報をファイルに出力のための準備
    $csvFileName = '/tmp/' . time() . rand() . '.csv';
    $res = fopen($csvFileName, 'w');
    if ($res === FALSE) {
      throw new Exception('ファイルの書き込みに失敗しました。');
    }

    // データ一覧。この部分を引数とか動的に渡すようにしましょう
    $dataList = $csvArr;

    // ループしながら出力
    foreach($dataList as $dataInfo) {

      // 文字コード変換。エクセルで開けるようにする
      mb_convert_variables('SJIS', 'UTF-8', $dataInfo);

      // ファイルに書き出しをする
      fputcsv($res, $dataInfo);
    }

    // ハンドル閉じる
    fclose($res);

    // ダウンロード開始
    header('Content-Type: application/octet-stream');

    // ここで渡されるファイルがダウンロード時のファイル名になる
    header('Content-Disposition: attachment; filename=tweet_dl_'.date('Ymd').'.csv');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($csvFileName));
    readfile($csvFileName);

  } catch(Exception $e) {

    // 例外処理をここに書きます
    echo $e->getMessage();

  }
}

?>
