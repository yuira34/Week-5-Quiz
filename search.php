<?php
use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WhitespaceTokenizer;
require_once 'vendor\autoload.php';

echo '<form method="POST" action="">
          Input Keyword <input type="text" name="keyword">
          <input type="submit" name="search" value="Search"><br>
          </form>';

if (isset($_POST["search"])) {
    $sample_data = array();
    $titles = array();
    $connection = mysqli_connect("localhost", "root", "mysql", "news");
    $query = "SELECT title, clean_data FROM contents";
    $result = mysqli_query($connection, $query);

    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            array_push($sample_data, $row['clean_data']);
            array_push($titles, $row['title']);
        }
        array_push($sample_data, $_POST["keyword"]);
    } else echo "Data is still empty. Please crawl first";

    $tf = new TokenCountVectorizer(new WhitespaceTokenizer());
    $tf->fit($sample_data);
    $tf->transform($sample_data);

    $tf_idf = new TfIdfTransformer($sample_data);
    $tf_idf->transform($sample_data);

    $query_index = count($sample_data) - 1;
    while ($index < $query_index) {
        $chebyshev_distances = array();
        foreach ($sample_data[$index] as $key => $value) {
            $distance = abs($sample_data[$query_index][$key] - $value);
            array_push($chebyshev_distances, $distance);
        }
        $max_distance = round(max($chebyshev_distances, 2));
        $title = $titles[$index];
        $update_query = "UPDATE contents SET similarity = '$max_distance' WHERE title = '$title'";
        mysqli_query($connection, $update_query);

        $index++;
    }
    echo "<table border='1'>
            <th align='center'>News Title</th>
            <th align='center'>News Link</th>
            <th align='center'>Similarity Distance (based on Chebyshev)</th>";

    $select_query = "SELECT title, link, similarity FROM contents ORDER BY similarity ASC";
    $select_result = mysqli_query($connection, $select_query);

    if (mysqli_num_rows($select_result) > 0) {
        while ($row = mysqli_fetch_assoc($select_result)) {
            $title = $row['title'];
            $link = $row['link'];
            $similarity = $row['similairty'];
            echo "<tr>
            <td>$title</td>
            <td>$link</td>
            <td>$similairty</td>
            </tr>";
        }
    }
    echo "</table>";
    mysqli_close($connection);
}
?>