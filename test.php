<?php


function getFilmTrailers($cat,$page,$itemsPerPage) {
   $offset = ($page - 1) * $itemsPerPage;
   $select = mysql_query("SELECT FLMT_Id,FILM_Id,FLMT_UTube_Path,FLMT_Image,FLMT_Type,FLMT_Title,FLMT_Descr FROM bb_film_trailers ft limit $offset,$itemsPerPage ;");
   $film_trailers = array();
   $gallery = array();
   $film_ids = array();

  while($row = mysql_fetch_object($select))
    {
       $row_data = new stdClass();
       if($row->FLMT_Type == 'TrailersPromos'){
        $field_media_type = 4;
      }
      $row_data->title = $row->FLMT_Title;
      $row_data->field_film = $row->FILM_Id;
      $row_data->field_media_category = "Video";
      $row_data->field_media = "Films";
      $row_data->field_latest = "No";
      $row_data->field_video_youtube_path = $row->FLMT_UTube_Path;
      $row_data->field_media_type  = $field_media_type;
      $row_data->field_media_description = $row->FLMT_Descr;

      $film_ids[$row->FILM_Id] = $row->FILM_Id;
      $rows[] = $row_data;
      // $rows[] = $row_data;
    }
    $film_ids = array_keys($film_ids);
    $url = 'http://192.168.27.100/metromatinee/?q=importfilmid';
    $result = service_call($url,$film_ids);
//print_r($result);
    $new_rows = array();
    foreach ($rows as $key => $value) {
      $value->field_film = $result[$value->field_film];
      $new_rows[] = $value;
    }

      return array("results" => $rows);
}
function getFilmClips($cat,$page,$itemsPerPage) {
  $offset = ($page - 1) * $itemsPerPage;
  $select = mysql_query("SELECT FLMCP_Id,FILM_Id,FLMCP_UTube_Path,FLMCP_Image,FLMCP_Descr,FLMCP_Title,FLMCP_Type FROM bb_film_clips fc limit $offset,$itemsPerPage;");
  $film_clips = array();
  $gallery = array();
  $film_ids = array();

  while($row = mysql_fetch_object($select))
    {
      $row_data = new stdClass();

      if($row->FLMCP_Type == 'Interviews'){
        $field_media_type = 86;
      }
      if($row->FLMCP_Type == 'Songs'){
        $field_media_type = 87;
      }
      if($row->FLMCP_Type == 'Scene'){
        $field_media_type = 5;
      }
      if($row->FLMCP_Type == 'Full Length Movie'){
        $field_media_type = 90;
      }
      if($row->FLMCP_Type == 'TrailersPromos'){
        $field_media_type = 4;
      }
      if($row->FLMCP_Type == 'Short Film'){
        $field_media_type = 91;
      }
      if($row->FLMCP_Type == 'General'){
        $field_media_type = 89;
      }
      if($row->FLMCP_Type == 'News'){
        $field_media_type = 88;
      }

      $row_data->field_film = $row->FILM_Id;;
      $row_data->title = $row->FLMCP_Title;
      $row_data->field_media_category = "Video";
      $row_data->field_media = "Films";
      $row_data->field_latest = "No";
      $row_data->field_video_youtube_path = $row->FLMCP_UTube_Path;
      $row_data->field_media_type  = $field_media_type;
      $row_data->field_media_description = $row->FLMCP_Descr;
      $film_ids[$row->FILM_Id] = $row->FILM_Id;

      $rows[] = $row_data;

    }
    $film_ids = array_keys($film_ids);
    $url = 'http://192.168.27.100/metromatinee/?q=importfilmid';
    $result = service_call($url,$film_ids);
//print_r($result);
    $new_rows = array();
    foreach ($rows as $key => $value) {
      $value->field_film = $result[$value->field_film];
      $new_rows[] = $value;
    }

    return array("results" => $rows);
}

function getNews($page,$itemsPerPage){
  $offset = ($page - 1) * $itemsPerPage;
  $select = mysql_query("SELECT NEWS_Id,NEWS_Title,NEWS_Description,NEWS_Image FROM bb_news_master nm limit $offset,$itemsPerPage;");
  $rows = array();

   while($row = mysql_fetch_object($select))
   {
     $row_data = new stdClass();
     $row_data->news_id = $row->NEWS_Id;
     $row_data->title = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '',$row->NEWS_Title);
     $row_data->field_description = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '',$row->NEWS_Description);
     $img_url = "http://www.metromatinee.com/MetromatineMoviewNews/images/";
     if($row->NEWS_Image == ''){
     $row_data->field_image = '';
     }else{
     $row_data->field_image = $img_url.$row->NEWS_Image;
     }

    $taxonomy_ids = array('1' => 70,
                          '2' => 71,
                          '3' => 79,
                          '4' => 81,
                          '5' => 83,
                          '6' => 82,
                          '7' => 80,
                          '8' => 75,
                          '9' => 76,
                          '10' => 77,
                          '11' => 78,
                          '12' => 134,
                          '13' => 135
      );

    $news_categories = getNewsCategory($row_data->news_id);
    $cat_ids = array();
    foreach($news_categories as $key => $value){
       $cat_ids[] = $taxonomy_ids[$value->NC_Id];
    }
   $row_data->field_news_type = $cat_ids;
  // print_r($row_data);exit;
   $rows[] = $row_data;
   }
  return array("results" => $rows);

}
 ?>
