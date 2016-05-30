<?php
function getArtistClips($id,$page,$itemsPerPage) {
    $offset = ($page - 1) * $itemsPerPage;
    $select = mysql_query("SELECT ASTM_Id,ATCLIP_UTube_Path,ATCLIP_Image,ATCLIP_Type,ATCLIP_Descr,ATCLIP_Title from  bb_artist_clips c where c.ASTM_Id = $id limit $offset,$itemsPerPage ;");
    $rows = array();

    $response = file_get_contents('http://192.168.27.100/metromatinee/?q=api/authd/views/artist_imported_id');
    $response = json_decode($response);

    foreach($response as $key => $value ){

    $data[$value->nid] = $value->artist_id;

    }

    while($row = mysql_fetch_object($select))
      {
        //$feild_videos[] = $row->ATCLIP_UTube_Path;
        //print_r($feild_videos);
        $row_data = new stdClass();
        $nid[] = $id;

        $result =array_intersect($data,$nid);
        $result = array_keys($result);
        //$row_data->id = $row->ASTM_Id;
        $row_data->field_artist = $result[0];
        $row_data->title = $row->ATCLIP_Title;
        $row_data->field_media_category = "Video";
        $row_data->field_media = "Artist";
        $row_data->field_latest = "No";

        $row_data->field_video_youtube_path = $row->ATCLIP_UTube_Path;
        //$img_url = "http://www.metromatinee.com/gallery/a$id/large/";
        //$field_media_image = $img_url.$row->ATIM_Image;
        //$row_data->field_media_image = $img_url.$row->ATCLIP_Image;
        if($row->ATCLIP_Type == 'Interviews'){
          $field_media_type = 86;
        }
        if($row->ATCLIP_Type == 'Songs'){
          $field_media_type = 87;
        }
        if($row->ATCLIP_Type == 'Scene'){
          $field_media_type = 5;
        }
        if($row->ATCLIP_Type == 'Full Length Movie'){
          $field_media_type = 90;
        }
        if($row->ATCLIP_Type == 'TrailersPromos'){
          $field_media_type = 4;
        }
        if($row->ATCLIP_Type == 'Short Film'){
          $field_media_type = 91;
        }
        if($row->ATCLIP_Type == 'General'){
          $field_media_type = 89;
        }
        if($row->ATCLIP_Type == 'News'){
          $field_media_type = 88;
        }
        $row_data->field_media_type  = $field_media_type;
        $row_data->field_media_description = $row->ATCLIP_Descr;
        $rows[] = $row_data;
      }
//print_r($rows);
    return array("results" => $rows);
    //return $rows;
}
$array_new = [];
foreach($array_two as $key)
{
   if(array_key_exists($key, $array_one))
   {
       $array_new[$key] = $array_one[$key];
   }
}
//Stripping from $array_one:

foreach($array_one as $key => $val)
{
   if(array_search($key, $array_two) === false)
   {
       unset($array_one[$key]);
   }
}
