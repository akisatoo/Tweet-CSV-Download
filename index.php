<?php
require 'TwistOAuth.phar';
require_once 'config.php';

//テーブル表示
if ($_GET['keyword']) {
  $connection = new TwistOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);

  $tweets_params = ['q' => $_GET['keyword'] ,'count' => TWEET_MAX_COUNT];
  $tweets = $connection->get('search/tweets', $tweets_params)->statuses;

  $result = [];

  // foreach でまわす
  foreach ($tweets as $tweet) {
    $datetime = date('Y/m/d', strtotime($tweet->created_at));
    $url = sprintf('https://twitter.com/%s/status/%s/', $tweet->user->screen_name, $tweet->id_str);

    $tweet_data = [];
    $tweet_data['date'] = $datetime;
    $tweet_data['tweet'] = $tweet->text;
    $tweet_data['url'] = $url;

    array_push($result, $tweet_data);
  }
}

?>

<html>
  <head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css" type="text/css">
  </head>
  <body>
    <h1>Tweet CSV Download</h1>
    <form action="index.php" method="get">
      <input type="text" name="keyword">
      <input type="submit" value="検索">
    </form>

    <?php
    if(!$result){
      echo '<p>該当ツイートなし</p>';
    } else {
      echo '<p>検索キーワード：',$_GET['keyword'],'</p>';
      echo '<form action="download.php" method="post">';
      echo '<input type="hidden" name="download" value="',$_GET['keyword'],'">';
      echo '<input type="submit" value="Download">';
      echo '</form>';
      echo '<table>';
      echo '<tr>';
      echo '<th>date</th>';
      echo '<th>tweet</th>';
      echo '<th>url</th>';
      echo '</tr>';

      foreach($result as $r){
        echo '<tr>';
        echo '<td>',$r['date'],'</td>';
        echo '<td>',$r['tweet'],'</td>';
        echo '<td><a target="_blank" href="',$r['url'],'">',$r['url'],'</a></td>';
        echo '</tr>';
      }
      echo '</table>';
    }

    ?>
  </body>
</html>
