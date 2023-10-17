<?php
require_once 'vendor\autoload.php';
include_once('simple_html_dom.php');

echo '<form method="POST" action="">
          Input Keyword <input type="text" name="keyword">
          <input type="submit" name="crawls" value="Crawls"><br>
          </form>';

if (isset($_POST["crawls"])) {
  $key = str_replace(" ", "+", $_POST["keyword"]);
  $proxy = 'proxy3.ubaya.ac.id:8080';
  $result = extract_html("https://www.antaranews.com/search?q=" . $key, $proxy);

  $stemmerFactory = new \Sastrawi\Stemmer\StemmerFactory();
  $stemmer = $stemmerFactory->createStemmer();

  $stopwordFactory = new \Sastrawi\StopWordRemover\StopWordRemoverFactory();
  $stemmer = $stemmerFactory->createStemmer();

  echo "<b>CRAWL RESULT</b>
        <table border='1'>
        <tr>
        <th align='center'>Judul</th>
        <th align='center'>Link Berita</th>
        <th align='center'>Data Bersih</th>
        </tr>";

  $connection = mysqli_connect("localhost", "root", "mysql", "news");
  if ($result['code'] == '200') {
    $html = new simple_html_dom();
    $html->load($result['message']);
    foreach ($html->find('article[class="simple-post simple-big clearfix"]') as $news) {
      $title = $news->find('a', 0)->title;
      $link = $news->find('a', 0)->href;
      $output_stem = $stemmer->stem($title);
      $output_stop = $stopword->remove($title);

      $table_row = "<tr>
              <td>$title</td>
              <td>$link</td>
              <td>$output_stop</td>
              </tr>";

      echo $table_row;

      $sql = "INSERT INTO contents VALUES ('$title','$link','$output_stop', 0)";
      mysqli_query($connection, $sql);
    }
    echo "</table>";
    mysqli_close($connection);
  }
}
?>