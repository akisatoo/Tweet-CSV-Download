<?php
require 'TwistOAuth.phar';
require_once 'config.php';

session_start();

//csvダウンロードの処理
if ($_SESSION['tweet_result']) {

  $csvArr = [];
  // foreach でまわす
  foreach ($_SESSION['tweet_result'] as $tweet) {
    array_push($csvArr,array($tweet['date'],$tweet['tweet'],$tweet['url']));
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
