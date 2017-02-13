<?php
require('config.php');



/*
 * artist details
 */
 //curl_setopt($ch, CURLOPT_TIMEOUT, 400);
 //$GLOBALS['url'] = 'http://192.168.27.100/metromatinee/';

  function artistsdetails($page,$itemsPerPage){
    $offset = ($page - 1) * $itemsPerPage;
    $select = mysql_query("SELECT ASTM_Id,ASTM_Name,ASTM_Gender,ASTM_Biography,ASTM_Address,ASTM_Workingfield,ASTM_Otherworks,ASTM_DebutFilm,ASTM_Date_Of_Birth,ASTM_Date_Of_Death,ASTM_Description FROM bb_artist_master limit $offset,$itemsPerPage;");
    $rows = array();

    //$rows_data = array();

      while($row = mysql_fetch_object($select))
      {

        // $row_data['id'] = $row->ASTM_Id;
        // $row_data['title'] = $row->ASTM_Name;
        // $row_data['field_gender'] = $row->ASTM_Gender;
        // $row_data['field_biography'] = $row->ASTM_Biography;
        // $row_data['field_address'] = $row->ASTM_Address;
        // $row_data['field_artist_type'] = $row->ASTM_Workingfield;
        // $row_data['field_otherworks'] = $row->ASTM_Otherworks;
        // $row_data['field_debut_film'] = $row->ASTM_DebutFilm;
        // $row_data['field_date_of_birth'] = $row->ASTM_Date_Of_Birth;
        // $row_data['field_date_of_death'] = $row->ASTM_Date_Of_Death;
        // $row_data['field_description'] = $row->ASTM_Description;
        // $row_data['field_profile_image'] = $row->Profile_Images;
     $row_data = new stdClass();
     $row_data->id = $row->ASTM_Id;
     $row_data->title = $row->ASTM_Name;
     //$row_data->field_gender = $row->ASTM_Gender;
     if($row->ASTM_Gender == 'M'){
       $row_data->field_gender = "Male";
     }
     elseif($row->ASTM_Gender == 'F'){
       $row_data->field_gender = "Female";
     }
     elseif($row->ASTM_Gender == ''){
       $row_data->field_gender = "";
     }
     else{
       $row_data->field_gender = "Company";
     }
     $row_data->field_biography = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '',$row->ASTM_Biography);
     $row_data->field_address = $row->ASTM_Address;
     $row_data->field_artist_type = explode(",",$row->ASTM_Workingfield);
     $row_data->field_otherworks = $row->ASTM_Otherworks;
     $row_data->field_debut_film = $row->ASTM_DebutFilm;
     if($row->ASTM_Date_Of_Birth == "0000-00-00"){
       $row_data->field_date_of_birth = '';
     }else{
       $row_data->field_date_of_birth = $row->ASTM_Date_Of_Birth;
     }
     if($row->ASTM_Date_Of_Death == "0000-00-00"){
       $row_data->field_date_of_death = '';
     }else{
       $row_data->field_date_of_death = $row->ASTM_Date_Of_Death;
     }
     $row_data->field_description = $row->ASTM_Description;
     $img_url = "http://old.metromatinee.com/gallery/a$row_data->id/thumb/";
     $profile_images = getArtistProfileImage($row_data->id);
     if($profile_images[0]->PAM_Images == ''){
       $row_data->field_profile_image = '';
     }else{
       $row_data->field_profile_image  = $img_url.$profile_images[0]->PAM_Images;
     }

     $rows[] = $row_data;
      }
      return array("results" => $rows);
  }

  //artist profile image
  function getArtistProfileImage($id) {
     $select = mysql_query("SELECT PAM_Images FROM bb_artist_Profile_image p where p.ASTM_Id = $id ORDER BY PAM_Id DESC
                LIMIT 1;");
     $rows = array();
     while($row = mysql_fetch_object($select))
  	   {
       $rows[] = $row;
  	   }
     return $rows;
  }

  //artist gallery images
  function getArtistImage($page,$itemsPerPage) {
      $offset = ($page - 1) * $itemsPerPage;
      $select = mysql_query("SELECT ASTM_Id,ATIM_Image,ATIM_Add_Date FROM bb_artist_image i limit $offset,$itemsPerPage ;");
      $rows = array();
      $gallery = array();
      $artist_ids = array();

      while($row = mysql_fetch_object($select))
    	  {
        $img_url = "http://old.metromatinee.com/gallery/a$row->ASTM_Id/large/";
          if((@fopen($img_url.$row->ATIM_Image,"r")==true)){
            $gallery[$row->ASTM_Id][] = array('url' => $img_url.$row->ATIM_Image,'date' => strtotime($row->ATIM_Add_Date));            
            $artist_ids[$row->ASTM_Id] = $row->ASTM_Id;
          }
    	  }
        $artist_ids = array_keys($artist_ids);
        $url = 'http://www.metromatinee.com/?q=importnid';
        $result = service_call($url,$artist_ids);
//print_r($result);
       foreach ($gallery as $key => $value) {               
         $row_data = new stdClass();
         $row_data->field_artist = $result[$key];
         $row_data->title = "Photo Gallery $page";
         $row_data->field_media_category = "Image";
         $row_data->field_media = "Artist";
         $row_data->field_latest = "No";
         $row_data->field_media_image = $value[0]['url'];        
         $row_data->created = $value[0]['date'];
         $rows[] = $row_data;
       }

    return array("results" => $rows);
  }

  //artist video clips by film id
  function getArtistClipsByFilmID($page,$itemsPerPage) {
      $offset = ($page - 1) * $itemsPerPage;
      $select = mysql_query("SELECT FILM_Id,ATCLIP_UTube_Path,ATCLIP_Image,ATCLIP_Type,ATCLIP_Descr,ATCLIP_Title from  bb_artist_clips c where FILM_Id > 0 order by FILM_Id limit $offset,$itemsPerPage ;");
      $rows = array();
      $gallery = array();
      $film_ids = array();

      while($row = mysql_fetch_object($select))
        {
          $row_data = new stdClass();

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

          $row_data->title = $row->ATCLIP_Title;
          $row_data->field_film = $row->FILM_Id;
          $row_data->field_media_category = "Video";
          $row_data->field_media = "Artist";
          $row_data->field_latest = "No";
          $row_data->field_media_type  = $field_media_type;
          $row_data->field_media_description = $row->ATCLIP_Descr;
          $row_data->field_video_youtube_path = get_youtube_url($row->ATCLIP_UTube_Path);
          //$row_data->field_video_youtube_path = $row->ATCLIP_UTube_Path;

          $film_ids[$row->FILM_Id] = $row->FILM_Id;
          $rows[] = $row_data;
        }

        $film_ids = array_keys($film_ids);
        $url = 'http://www.metromatinee.com/?q=importfilmid';
        $result = service_call($url,$film_ids);
        //print_r($result);

        $new_rows = array();
        foreach ($rows as $key => $value) {
          $value->field_film = $result[$value->field_film];
          $new_rows[] = $value;
        }

      return array("results" => $new_rows);
    }
  
//artist film clips by artist id
function getArtistClipsByArtistID($page,$itemsPerPage) {
    $offset = ($page - 1) * $itemsPerPage;
    $select = mysql_query("SELECT ASTM_Id,ATCLIP_UTube_Path,ATCLIP_Image,ATCLIP_Type,ATCLIP_Descr,ATCLIP_Title from  bb_artist_clips c where FILM_Id = 0 order by FILM_Id limit $offset,$itemsPerPage ;");
    $rows = array();
    $gallery = array();
    $artist_ids = array();

    while($row = mysql_fetch_object($select))
      {
        $row_data = new stdClass();

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

        $row_data->title = $row->ATCLIP_Title;
        $row_data->field_artist = $row->ASTM_Id;
        $row_data->field_media_category = "Video";
        $row_data->field_media = "Artist";
        $row_data->field_latest = "No";
        $row_data->field_media_type  = $field_media_type;
        $row_data->field_media_description = $row->ATCLIP_Descr;
        $row_data->field_video_youtube_path = get_youtube_url($row->ATCLIP_UTube_Path);
        //$row_data->field_video_youtube_path = $row->ATCLIP_UTube_Path;

        $artist_ids[$row->ASTM_Id] = $row->ASTM_Id;
        $rows[] = $row_data;
      }

      $artist_ids = array_keys($artist_ids);
      $url = 'http://www.metromatinee.com/?q=importnid';
      $result = service_call($url,$artist_ids);

      $new_rows = array();
      foreach ($rows as $key => $value) {
        $value->field_artist = $result[$value->field_artist];
        $new_rows[] = $value;
      }

    return array("results" => $new_rows);
  }

  //film main details
  function filmdetails($page,$itemsPerPage){

    $offset = ($page - 1) * $itemsPerPage;
    $select = mysql_query("SELECT FILM_Id,FILM_Name,FILM_Language,FILM_ReleaseDtae,FILM_Duration,FILM_Description,FILM_TimeToVisit,FILM_Hit,FILM_Year,FILM_Released FROM bb_film_definition_master limit $offset,$itemsPerPage;");
    $rows = array();
    $artist_ids = array();

    while($row = mysql_fetch_object($select))
      {

       $row_data = new stdClass();

       $row_data->field_film_id_ = $row->FILM_Id;
       $row_data->title = $row->FILM_Name;
       $row_data->field_language = $row->FILM_Language;
       if($row->FILM_ReleaseDtae == "NULL"){
        $row_data->field_release_date = '';
        }else{
        $row_data->field_release_date = $row->FILM_ReleaseDtae;
        }
        $row_data->field_duration = $row->FILM_Duration;
        if($row->FILM_Description == '0'){
          $row_data->field_film_description = '';
        }else{
        $row_data->field_film_description = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '',$row->FILM_Description);
        }
        $row_data->field_time_to_visit = $row->FILM_TimeToVisit;
        $row_data->field_hit = $row->FILM_Hit;
        $row_data->field_year = $row->FILM_Year;
        if($row->FILM_Released == "0000-00-00"){
          $row_data->field_released = '';
        }
        else{
          $row_data->field_released = $row->FILM_Released;
        }
        $img_url = "http://old.metromatinee.com/movies/images/m$row_data->field_film_id_/large/";

        $film_profile_image = getFilmProfileImage($row_data->field_film_id_);
        if(isset($film_profile_image[0]) && $film_profile_image[0]->PFL_Images !=''){
        $row_data->field_profile_image = $img_url.$film_profile_image[0]->PFL_Images;
        }else{
        $row_data->field_profile_image = '';
       }
       $filmstage = getFilmStage($row_data->field_film_id_);      
       $row_data->field_stage = $filmstage;

       $film_category = get_film_category($row_data->field_film_id_);
       foreach ($film_category as $key => $value) {
          
          $row_data->field_category = $value;
       }      
     
      $film_details = getFilmDetails($row_data->field_film_id_);

      $row_data->FLDS_Starring_Two = add_artist_id($film_details[0]->FLDS_Starring_Two);
      $artist_ids = flip_artist_id($row_data->FLDS_Starring_Two,$artist_ids);

      $row_data->FLDS_Director = add_artist_id($film_details[0]->FLDS_Director);
      $artist_ids = flip_artist_id($row_data->FLDS_Director,$artist_ids);

      $row_data->FLDS_Associate_Director = add_artist_id($film_details[0]->FLDS_Associate_Director);
      $artist_ids = flip_artist_id($row_data->FLDS_Associate_Director,$artist_ids);

      $row_data->FLDS_Publicity_And_Promotions = add_artist_id($film_details[0]->FLDS_Publicity_And_Promotions);
      $artist_ids = flip_artist_id($row_data->FLDS_Publicity_And_Promotions,$artist_ids);

      $row_data->FLDS_Producer = add_artist_id($film_details[0]->FLDS_Producer);
      $artist_ids = flip_artist_id($row_data->FLDS_Producer,$artist_ids);

      $row_data->FLDS_Banner = add_artist_id($film_details[0]->FLDS_Banner);
      $artist_ids = flip_artist_id($row_data->FLDS_Banner,$artist_ids);

      $row_data->FLDS_Distribution = add_artist_id($film_details[0]->FLDS_Distribution);
      $artist_ids = flip_artist_id($row_data->FLDS_Distribution,$artist_ids);

      $row_data->FLDS_Story_And_Script = add_artist_id($film_details[0]->FLDS_Story_And_Script);
      $artist_ids = flip_artist_id($row_data->FLDS_Story_And_Script,$artist_ids);

      $row_data->FLDS_Music_Director = add_artist_id($film_details[0]->FLDS_Music_Director);
      $artist_ids = flip_artist_id($row_data->FLDS_Music_Director,$artist_ids);

      $row_data->FLDS_Lyrics = add_artist_id($film_details[0]->FLDS_Lyrics);
      $artist_ids = flip_artist_id($row_data->FLDS_Lyrics,$artist_ids);

      $row_data->FLDS_Singers = add_artist_id($film_details[0]->FLDS_Singers);
      $artist_ids = flip_artist_id($row_data->FLDS_Singers,$artist_ids);

      $row_data->FLDS_Cinematography = add_artist_id($film_details[0]->FLDS_Cinematography);
      $artist_ids = flip_artist_id($row_data->FLDS_Cinematography,$artist_ids);


      $row_data->FLDS_Art_Director = add_artist_id($film_details[0]->FLDS_Art_Director);
      $artist_ids = flip_artist_id($row_data->FLDS_Art_Director,$artist_ids);

      $row_data->FLDS_Production_Control = add_artist_id($film_details[0]->FLDS_Production_Control);
      $artist_ids = flip_artist_id($row_data->FLDS_Production_Control,$artist_ids);

      $row_data->FLDS_Choreography = add_artist_id($film_details[0]->FLDS_Choreography);
      $artist_ids = flip_artist_id($row_data->FLDS_Choreography,$artist_ids);

      $row_data->FLDS_Makeup = add_artist_id($film_details[0]->FLDS_Makeup);
      $artist_ids = flip_artist_id($row_data->FLDS_Makeup,$artist_ids);

      $row_data->FLDS_Costume_Designer = add_artist_id($film_details[0]->FLDS_Costume_Designer);
      $artist_ids = flip_artist_id($row_data->FLDS_Costume_Designer,$artist_ids);

      $row_data->FLDS_Editor = add_artist_id($film_details[0]->FLDS_Editor);
      $artist_ids = flip_artist_id($row_data->FLDS_Editor,$artist_ids);

      $row_data->FLDS_Dubbing = add_artist_id($film_details[0]->FLDS_Dubbing);
      $artist_ids = flip_artist_id($row_data->FLDS_Dubbing,$artist_ids);

      $row_data->FLDS_Mixing = add_artist_id($film_details[0]->FLDS_Mixing);
      $artist_ids = flip_artist_id($row_data->FLDS_Mixing,$artist_ids);

      $row_data->FLDS_PRO = add_artist_id($film_details[0]->FLDS_PRO);
      $artist_ids = flip_artist_id($row_data->FLDS_PRO,$artist_ids);

      $row_data->FLDS_Visual_Effects = add_artist_id($film_details[0]->FLDS_Visual_Effects);
      $artist_ids = flip_artist_id($row_data->FLDS_Visual_Effects,$artist_ids);

      $row_data->FLDS_Sound_Effects = add_artist_id($film_details[0]->FLDS_Sound_Effects);
      $artist_ids = flip_artist_id($row_data->FLDS_Sound_Effects,$artist_ids);

      $row_data->FLDS_Sound_Engineering = add_artist_id($film_details[0]->FLDS_Sound_Engineering);
      $artist_ids = flip_artist_id($row_data->FLDS_Sound_Engineering,$artist_ids);

      $row_data->FLDS_Studio = add_artist_id($film_details[0]->FLDS_Studio);
      $artist_ids = flip_artist_id($row_data->FLDS_Studio,$artist_ids);

      $row_data->FLDS_Assistant_Director = add_artist_id($film_details[0]->FLDS_Assistant_Director);
      $artist_ids = flip_artist_id($row_data->FLDS_Assistant_Director,$artist_ids);

      $row_data->FLDS_Backgound_Music = add_artist_id($film_details[0]->FLDS_Backgound_Music);
      $artist_ids = flip_artist_id($row_data->FLDS_Backgound_Music,$artist_ids);

      $row_data->FLDS_Production_Executive = add_artist_id($film_details[0]->FLDS_Production_Executive);
      $artist_ids = flip_artist_id($row_data->FLDS_Production_Executive,$artist_ids);

      $row_data->FLDS_Action = add_artist_id($film_details[0]->FLDS_Action);
      $artist_ids = flip_artist_id($row_data->FLDS_Action,$artist_ids);

      $row_data->FLDS_OutdoorUnit = add_artist_id($film_details[0]->FLDS_OutdoorUnit);
      $artist_ids = flip_artist_id($row_data->FLDS_OutdoorUnit,$artist_ids);

      $row_data->FLDS_IndoorUnit = add_artist_id($film_details[0]->FLDS_IndoorUnit);
      $artist_ids = flip_artist_id($row_data->FLDS_IndoorUnit,$artist_ids);

      $row_data->FLDS_Special_Thanks = add_artist_id($film_details[0]->FLDS_Special_Thanks);
      $artist_ids = flip_artist_id($row_data->FLDS_Special_Thanks,$artist_ids);

      $row_data->FLDS_Light_And_Sound = add_artist_id($film_details[0]->FLDS_Light_And_Sound);
      $artist_ids = flip_artist_id($row_data->FLDS_Light_And_Sound,$artist_ids);

      $row_data->FLDS_Stills = add_artist_id($film_details[0]->FLDS_Stills);
      $artist_ids = flip_artist_id($row_data->FLDS_Stills,$artist_ids);

      $row_data->FLDS_Transpotation = add_artist_id($film_details[0]->FLDS_Transpotation);
      $artist_ids = flip_artist_id($row_data->FLDS_Transpotation,$artist_ids);

      $row_data->FLDS_Certification = add_artist_id($film_details[0]->FLDS_Certification);
      $artist_ids = flip_artist_id($row_data->FLDS_Certification,$artist_ids);

      $row_data->FLDS_Advertisement = add_artist_id($film_details[0]->FLDS_Advertisement);
      $artist_ids = flip_artist_id($row_data->FLDS_Advertisement,$artist_ids);

      $row_data->FLDS_Executive_Producer = add_artist_id($film_details[0]->FLDS_Executive_Producer);
      $artist_ids = flip_artist_id($row_data->FLDS_Executive_Producer,$artist_ids);

      $row_data->FLDS_Dialogues = add_artist_id($film_details[0]->FLDS_Dialogues);
      $artist_ids = flip_artist_id($row_data->FLDS_Dialogues,$artist_ids);

      $row_data->FLDS_Line_Producer = add_artist_id($film_details[0]->FLDS_Line_Producer);
      $artist_ids = flip_artist_id($row_data->FLDS_Line_Producer,$artist_ids);

      $row_data->FLDS_Cheif_Associatedirector = add_artist_id($film_details[0]->FLDS_Cheif_Associatedirector);
      $artist_ids = flip_artist_id($row_data->FLDS_Cheif_Associatedirector,$artist_ids);

      $row_data->FLDS_Designs = add_artist_id($film_details[0]->FLDS_Designs);
      $artist_ids = flip_artist_id($row_data->FLDS_Designs,$artist_ids);

      $row_data->FLDS_Assistant_Cameracrew = add_artist_id($film_details[0]->FLDS_Assistant_Cameracrew);
      $artist_ids = flip_artist_id($row_data->FLDS_Cheif_Associatedirector,$artist_ids);

      $row_data->FLDS_Lab = add_artist_id($film_details[0]->FLDS_Lab);
      $artist_ids = flip_artist_id($row_data->FLDS_Lab,$artist_ids);

      $row_data->FLDS_Onlinepromotions = add_artist_id($film_details[0]->FLDS_Onlinepromotions);
      $artist_ids = flip_artist_id($row_data->FLDS_Onlinepromotions,$artist_ids);

      $row_data->FLDS_Painting_Designs = add_artist_id($film_details[0]->FLDS_Painting_Designs);
      $artist_ids = flip_artist_id($row_data->FLDS_Painting_Designs,$artist_ids);

      $row_data->FLDS_Online_Editor = add_artist_id($film_details[0]->FLDS_Online_Editor);
      $artist_ids = flip_artist_id($row_data->FLDS_Online_Editor,$artist_ids);

      $row_data->FLDS_Promo_Trailercuts = add_artist_id($film_details[0]->FLDS_Promo_Trailercuts);
      $artist_ids = flip_artist_id($row_data->FLDS_Promo_Trailercuts,$artist_ids);

      $row_data->FLDS_Story = add_artist_id($film_details[0]->FLDS_Story);
      $artist_ids = flip_artist_id($row_data->FLDS_Story,$artist_ids);

      $row_data->FLDS_Script = add_artist_id($film_details[0]->FLDS_Script);
      $artist_ids = flip_artist_id($row_data->FLDS_Script,$artist_ids);

      $row_data->FLDS_Screenplay = add_artist_id($film_details[0]->FLDS_Screenplay);
      $artist_ids = flip_artist_id($row_data->FLDS_Screenplay,$artist_ids);

      $row_data->FLDS_CoProducer = add_artist_id($film_details[0]->FLDS_CoProducer);
      $artist_ids = flip_artist_id($row_data->FLDS_CoProducer,$artist_ids);

      $row_data->FLDS_Other = add_artist_id($film_details[0]->FLDS_Other);
      $artist_ids = flip_artist_id($row_data->FLDS_Other,$artist_ids);

      $row_data->FLDS_DubbingArtist = add_artist_id($film_details[0]->FLDS_DubbingArtist);
      $artist_ids = flip_artist_id($row_data->FLDS_DubbingArtist,$artist_ids);

      $row_data->FLDS_Finance_Controller = add_artist_id($film_details[0]->FLDS_Finance_Controller);
      $artist_ids = flip_artist_id($row_data->FLDS_Finance_Controller,$artist_ids);

      $row_data->FLDS_Locations = add_artist_id($film_details[0]->FLDS_Locations);
      $artist_ids = flip_artist_id($row_data->FLDS_Locations,$artist_ids);

      $row_data->FLDS_Bank = add_artist_id($film_details[0]->FLDS_Bank);
      $artist_ids = flip_artist_id($row_data->FLDS_Bank,$artist_ids);

      $row_data->FLDS_DTS_Mixing = add_artist_id($film_details[0]->FLDS_DTS_Mixing);
      $artist_ids = flip_artist_id($row_data->FLDS_DTS_Mixing,$artist_ids);

      $row_data->FLDS_Digital_Inte = add_artist_id($film_details[0]->FLDS_Digital_Inte);
      $artist_ids = flip_artist_id($row_data->FLDS_Digital_Inte,$artist_ids);

      $rows[] = $row_data;
      //print_r($rows);exit;
  }

  $artist_ids = array_keys($artist_ids);
  $result = get_json_data_artist_nid($artist_ids);
  ksort($result);
     $new_rows = array();
      foreach ($rows as $key => $new_row) {

       $new_row->FLDS_Starring_Two = replace_nid($new_row->FLDS_Starring_Two,$result);
       $new_row->FLDS_Director = replace_nid($new_row->FLDS_Director,$result);
       $new_row->FLDS_Associate_Director = replace_nid($new_row->FLDS_Associate_Director,$result);
       $new_row->FLDS_Publicity_And_Promotions = replace_nid($new_row->FLDS_Publicity_And_Promotions,$result);
       $new_row->FLDS_Producer = replace_nid($new_row->FLDS_Producer,$result);
       $new_row->FLDS_Banner = replace_nid($new_row->FLDS_Banner,$result);
       $new_row->FLDS_Distribution = replace_nid($new_row->FLDS_Distribution,$result);
       $new_row->FLDS_Story_And_Script = replace_nid($new_row->FLDS_Story_And_Script,$result);
       $new_row->FLDS_Music_Director = replace_nid($new_row->FLDS_Music_Director,$result);
       $new_row->FLDS_Lyrics = replace_nid($new_row->FLDS_Lyrics,$result);
       $new_row->FLDS_Singers = replace_nid($new_row->FLDS_Singers,$result);
       $new_row->FLDS_Cinematography = replace_nid($new_row->FLDS_Cinematography,$result);
       $new_row->FLDS_Art_Director = replace_nid($new_row->FLDS_Art_Director,$result);
       $new_row->FLDS_Production_Control = replace_nid($new_row->FLDS_Production_Control,$result);
       $new_row->FLDS_Choreography = replace_nid($new_row->FLDS_Choreography,$result);
       $new_row->FLDS_Makeup = replace_nid($new_row->FLDS_Makeup,$result);
       $new_row->FLDS_Costume_Designer = replace_nid($new_row->FLDS_Costume_Designer,$result);
       $new_row->FLDS_Editor = replace_nid($new_row->FLDS_Editor,$result);
       $new_row->FLDS_Dubbing = replace_nid($new_row->FLDS_Dubbing,$result);
       $new_row->FLDS_Mixing = replace_nid($new_row->FLDS_Mixing,$result);
       $new_row->FLDS_PRO = replace_nid($new_row->FLDS_PRO,$result);
       $new_row->FLDS_Visual_Effects = replace_nid($new_row->FLDS_Visual_Effects,$result);
       $new_row->FLDS_Sound_Effects = replace_nid($new_row->FLDS_Sound_Effects,$result);
       $new_row->FLDS_Sound_Engineering = replace_nid($new_row->FLDS_Sound_Engineering,$result);
       $new_row->FLDS_Studio = replace_nid($new_row->FLDS_Studio,$result);
       $new_row->FLDS_Assistant_Director = replace_nid($new_row->FLDS_Assistant_Director,$result);
       $new_row->FLDS_Backgound_Music = replace_nid($new_row->FLDS_Backgound_Music,$result);
       $new_row->FLDS_Production_Executive = replace_nid($new_row->FLDS_Production_Executive,$result);
       $new_row->FLDS_Action = replace_nid($new_row->FLDS_Action,$result);
       $new_row->FLDS_OutdoorUnit = replace_nid($new_row->FLDS_OutdoorUnit,$result);
       $new_row->FLDS_IndoorUnit = replace_nid($new_row->FLDS_IndoorUnit,$result);
       $new_row->FLDS_Special_Thanks = replace_nid($new_row->FLDS_Special_Thanks,$result);
       $new_row->FLDS_Light_And_Sound = replace_nid($new_row->FLDS_Light_And_Sound,$result);
       $new_row->FLDS_Stills = replace_nid($new_row->FLDS_Stills,$result);
       $new_row->FLDS_Transpotation = replace_nid($new_row->FLDS_Transpotation,$result);
       $new_row->FLDS_Certification = replace_nid($new_row->FLDS_Certification,$result);
       $new_row->FLDS_Advertisement = replace_nid($new_row->FLDS_Advertisement,$result);
       $new_row->FLDS_Executive_Producer = replace_nid($new_row->FLDS_Executive_Producer,$result);
       $new_row->FLDS_Dialogues = replace_nid($new_row->FLDS_Dialogues,$result);
       $new_row->FLDS_Executive_Producer = replace_nid($new_row->FLDS_Executive_Producer,$result);
       $new_row->FLDS_Line_Producer = replace_nid($new_row->FLDS_Line_Producer,$result);
       $new_row->FLDS_Cheif_Associatedirector = replace_nid($new_row->FLDS_Cheif_Associatedirector,$result);
       $new_row->FLDS_Designs = replace_nid($new_row->FLDS_Designs,$result);
       $new_row->FLDS_Assistant_Cameracrew = replace_nid($new_row->FLDS_Assistant_Cameracrew,$result);
       $new_row->FLDS_Lab = replace_nid($new_row->FLDS_Lab,$result);
       $new_row->FLDS_Onlinepromotions = replace_nid($new_row->FLDS_Onlinepromotions,$result);
       $new_row->FLDS_Painting_Designs = replace_nid($new_row->FLDS_Painting_Designs,$result);
       $new_row->FLDS_Online_Editor = replace_nid($new_row->FLDS_Online_Editor,$result);
       $new_row->FLDS_Promo_Trailercuts = replace_nid($new_row->FLDS_Promo_Trailercuts,$result);
       $new_row->FLDS_Story = replace_nid($new_row->FLDS_Story,$result);
       $new_row->FLDS_Script = replace_nid($new_row->FLDS_Script,$result);
       $new_row->FLDS_Screenplay = replace_nid($new_row->FLDS_Screenplay,$result);
       $new_row->FLDS_CoProducer = replace_nid($new_row->FLDS_CoProducer,$result);
       $new_row->FLDS_Other = replace_nid($new_row->FLDS_Other,$result);
       $new_row->FLDS_DubbingArtist = replace_nid($new_row->FLDS_DubbingArtist,$result);
       $new_row->FLDS_Finance_Controller = replace_nid($new_row->FLDS_Finance_Controller,$result);
       $new_row->FLDS_Locations = replace_nid($new_row->FLDS_Locations,$result);
       $new_row->FLDS_Bank = replace_nid($new_row->FLDS_Bank,$result);
       $new_row->FLDS_DTS_Mixing = replace_nid($new_row->FLDS_DTS_Mixing,$result);
       $new_row->FLDS_Digital_Inte = replace_nid($new_row->FLDS_Digital_Inte,$result);

      $new_rows[] = $new_row;

      }
  return array("results" => $new_rows);
}

  function service_call($url,$parameters){
   $data = "";
   foreach ($parameters as $key => $value) {
     $data .= '&argu[]='.$value;
   }
   $options = array(
       'http' => array(
           'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
           'method'  => 'POST',
           'content' => $data,
       ),
   );
  $context  = stream_context_create($options);
  $result = file_get_contents($url, false, $context);

  return json_decode($result,1);
  }

  //film other details
  function getFilmDetails($id) {
    $select = mysql_query("SELECT * FROM bb_film_details fd where fd.FILM_Id = $id ;");
  	$film_details = array();
  	while($row = mysql_fetch_object($select))
  	  {
        $film_details[] = $row;

  	  }

  	return $film_details;
  }

  //film profile images
  function getFilmProfileImage($id) {
     $select = mysql_query("SELECT * FROM bb_film_Profile_Image pf where pf.FILM_Id = $id ORDER BY PFL_Id DESC LIMIT 1 ;");
     $profile_images = array();
     while($row = mysql_fetch_object($select))
  	   {
        $profile_images[] = $row;
  	   }
     return $profile_images;
  }

  //film galery images
  function getFilmImages($page,$itemsPerPage) {
    $offset = ($page - 1) * $itemsPerPage;
    $select = mysql_query("SELECT FILM_Id,FLM_Image FROM bb_film_image fi limit $offset,$itemsPerPage ;");
    $film_images = array();
    $gallery = array();
    $film_ids = array();

    while($row = mysql_fetch_object($select))
    	{
      $img_url = "http://old.metromatinee.com/movies/images/m$row->FILM_Id/large/";
      // if($row->FLM_Image == ''){
      //   $row_data->field_media_image == '';
      // }else{
      // $field_media_filmimage[] = $img_url.$row->FLM_Image;
      // }
      if((@fopen($img_url.$row->FLM_Image,"r")==true)){
        $gallery[$row->FILM_Id][] = $img_url.$row->FLM_Image;
        $film_ids[$row->FILM_Id] = $row->FILM_Id;
      }

    	}

      $film_ids = array_keys($film_ids);
      $url = 'http://www.metromatinee.com/?q=importfilmid';
      $result = service_call($url,$film_ids);
//print_r($result);
     foreach ($gallery as $key => $value) {
       $row_data = new stdClass();
       $row_data->field_film = $result[$key];
       $row_data->title = "Photo Gallery Films $page";
       $row_data->field_media_category = "Image";
       $row_data->field_media = "Films";
       $row_data->field_latest = "No";
       $row_data->field_media_image = $value;
       $rows[] = $row_data;

     }

      return array("results" => $rows);
  }

  //film trailers
  function getFilmTrailers($cat,$page,$itemsPerPage) {
    //print_r("hello");
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
      //  $row_data->field_biography = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '',$row->ASTM_Biography);
      //  $row_data->field_video_youtube_path = preg_match("(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=[0-9]/)[^&\n]+|(?<=v=)[^&\n]+", $row->FLMT_UTube_Path, $matches);
        //$row_data->field_video_youtube_path = $row->FLMT_UTube_Path;
        $row_data->field_media_type  = $field_media_type;
        $row_data->field_video_youtube_path = get_youtube_url($row->FLMT_UTube_Path);
        //print_r($row_data->field_video_youtube_path);
        $row_data->field_media_description = $row->FLMT_Descr;
        $film_ids[$row->FILM_Id] = $row->FILM_Id;
        $rows[] = $row_data;
      }

      $film_ids = array_keys($film_ids);
      $url = 'http://www.metromatinee.com/?q=importfilmid';
      $result = service_call($url,$film_ids);
//print_r($result);
      foreach ($rows as $key => $value) {
        $value->field_film = $result[$value->field_film];
      }
      return array("results" => $rows);
  }


  function get_youtube_url($url) {
  $url = current(explode("&",$url));
  return "https://www.youtube.com/watch?v=".end(@explode("/",$url));
 }

  //film clips
//   function getFilmClips($cat,$page,$itemsPerPage) {
//     $offset = ($page - 1) * $itemsPerPage;
//     $select = mysql_query("SELECT FLMCP_Id,FILM_Id,FLMCP_UTube_Path,FLMCP_Image,FLMCP_Descr,FLMCP_Title,FLMCP_Type FROM bb_film_clips fc limit $offset,$itemsPerPage;");
//     $film_clips = array();
//     $gallery = array();
//     $film_ids = array();

//     while($row = mysql_fetch_object($select))
//       {
//         $row_data = new stdClass();

//         if($row->FLMCP_Type == 'Interviews'){
//           $field_media_type = 86;
//         }
//         if($row->FLMCP_Type == 'Songs'){
//           $field_media_type = 87;
//         }
//         if($row->FLMCP_Type == 'Scene'){
//           $field_media_type = 5;
//         }
//         if($row->FLMCP_Type == 'Full Length Movie'){
//           $field_media_type = 90;
//         }
//         if($row->FLMCP_Type == 'TrailersPromos'){
//           $field_media_type = 4;
//         }
//         if($row->FLMCP_Type == 'Short Film'){
//           $field_media_type = 91;
//         }
//         if($row->FLMCP_Type == 'General'){
//           $field_media_type = 89;
//         }
//         if($row->FLMCP_Type == 'News'){
//           $field_media_type = 88;
//         }

//         $row_data->field_film = $row->FILM_Id;;
//         $row_data->title = $row->FLMCP_Title;
//         $row_data->field_media_category = "Video";
//         $row_data->field_media = "Films";
//         $row_data->field_latest = "No";
//         $row_data->field_video_youtube_path = get_youtube_url($row->FLMCP_UTube_Path);
//         //$row_data->field_video_youtube_path = $row->FLMCP_UTube_Path;
//         $row_data->field_media_type  = $field_media_type;
//         $row_data->field_media_description = $row->FLMCP_Descr;
//         $film_ids[$row->FILM_Id] = $row->FILM_Id;

//         $rows[] = $row_data;

//       }
//       $film_ids = array_keys($film_ids);
//       $url = 'http://192.168.27.100/metromatinee/?q=importfilmid';
//       $result = service_call($url,$film_ids);
// //print_r($result);
//       $new_rows = array();
//       foreach ($rows as $key => $value) {
//         $value->field_film = $result[$value->field_film];
//         $new_rows[] = $value;
//       }

//       return array("results" => $rows);
//   }

 //film clips with special category

  function getFilmClips($cat,$page,$itemsPerPage) {
    $offset = ($page - 1) * $itemsPerPage;
    $select = mysql_query("SELECT FLMCP_Id,FLMCP_UTube_Path,FLMCP_Image,FLMCP_Descr,FLMCP_Title,FLMCP_Type FROM bb_film_clips fc WHERE FLMCP_Type
    IN ('Interviews','Short Film', 'General', 'News') limit $offset,$itemsPerPage;");
    $film_clips = array();
    $gallery = array();
    //$film_ids = array();

    while($row = mysql_fetch_object($select))
      {
        $row_data = new stdClass();
        
        if($row->FLMCP_Type == 'Interviews'){
          $field_media_type = 86;
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

        $row_data->field_film = 0;
        $row_data->title = $row->FLMCP_Title;
        $row_data->field_media_category = "Video";
        $row_data->field_media = "Films";
        $row_data->field_latest = "No";
        $row_data->field_video_youtube_path = get_youtube_url($row->FLMCP_UTube_Path);
        //$row_data->field_video_youtube_path = $row->FLMCP_UTube_Path;
        $row_data->field_media_type  = $field_media_type;
        $row_data->field_media_description = $row->FLMCP_Descr;
        //$film_ids[$row->FILM_Id] = $row->FILM_Id;

        $rows[] = $row_data;

      }
      // $film_ids = array_keys($film_ids);
      // $url = 'http://192.168.27.100/metromatinee/?q=importfilmid';
      // $result = service_call($url,$film_ids);
      //print_r($result);
      // $new_rows = array();
      // foreach ($rows as $key => $value) {
      //   $value->field_film = $result[$value->field_film];
      //   $new_rows[] = $value;
      // }

      return array("results" => $rows);
  }

    function replace_nid($arrayRow,$result){
      $output = array();
      foreach ($arrayRow as $k => $val) {
        $output[] = $result[$val];
      }
      //print_r($output);
      return $output;
    }

    function add_artist_id($arrayRow){
      $explode = array();
      $result = array();
      if($arrayRow != '' && $arrayRow != '0'){
        $result = explode("-",$arrayRow);
      }
      //print_r($result);
     return $result;
    }

    function flip_artist_id($arrayRow,$artist_ids){
      $flipped_array = array_flip($arrayRow);
      foreach ($flipped_array as $key => $value) {
        $artist_ids[$key] = $value;
      }
      return $artist_ids;
    }


    //News importing

    function getNews($page,$itemsPerPage){
      $offset = ($page - 1) * $itemsPerPage;
      $select = mysql_query("SELECT NEWS_Id,NEWS_Title,NEWS_Description,NEWS_Image,NEWS_Date FROM bb_news_master nm limit $offset,$itemsPerPage;");
      $rows = array();

       while($row = mysql_fetch_object($select))
       {
         $row_data = new stdClass();
         $row_data->news_id = $row->NEWS_Id;
         $row_data->title = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '',$row->NEWS_Title);
         $row_data->field_description = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '',$row->NEWS_Description);
         $row_data->created = strtotime($row->NEWS_Date);         
         //$img_url = "http://www.metromatinee.com/MetromatineMoviewNews/images/";
         //if($row->NEWS_Image == ''){
         $row_data->field_image = '';
         //}
         //else{
         //$row_data->field_image = $img_url.$row->NEWS_Image;
         //}

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

      //  print_r($news_categories);
        $cat_ids = array();

        foreach($news_categories as $key => $value){
          //print_r($value);
           $cat_ids[] = $taxonomy_ids[$value->NC_Id];
        }
      //  print_r($cat_ids);
       $row_data->field_news_type = $cat_ids;
      // print_r($row_data);exit;
       $rows[] = $row_data;
       }
      // print_r($rows);exit;
      return array("results" => $rows);

  }

   function getNewsCategory($id){
    // $id= 10610;
    $select = mysql_query("SELECT NEWS_Id,NC_Id FROM bb_news_category_relation nc where nc.NEWS_Id = $id;");
    $rows = array();
    while($row = mysql_fetch_object($select))
      {
      $rows[] = $row;
      //print_r($rows);
      }
    return $rows;
   }

   //artist awards
   function getArtistAwards($cat,$page,$itemsPerPage) {
       $offset = ($page - 1) * $itemsPerPage;
       $select = mysql_query("SELECT baa.`AFA_Artist_Id` , bam.AM_Authority, bam.AM_Category, bam.AM_Relation_To_Movie, bam.AM_Year FROM  `bb_award_for_artist` baa LEFT JOIN bb_award_master bam ON baa.`AFA_Award_Master_Id` = bam.AM_Id
                 LIMIT $offset,$itemsPerPage;");
       $rows = array();
       $artist_ids = array();

       while($row = mysql_fetch_object($select))
       	{

         $row_data = new stdClass();
         $row_data->title = "Awards";
         //$row_data->field_artist = $row->AFA_Artist_Id;
         $row_data->field_artist = $row->AFA_Artist_Id;
         $artist_ids[$row->AFA_Artist_Id] = $row->AFA_Artist_Id;
         //print_r($artist_ids);
         $row_data->field_type = "Artist";

         $artisttype_taxonomy_ids = array('Producer' => 23,
                               'Screenplay' => 67,
                               'Actor' => 1,
                               'Background Music' => 44,
                               'Cinematography' => 29,
                               'Director' => 9,
                               'Sound Design' => 41,
                               'Actress' => 6,
                               'Story' => 65,
                               'Lyrics' => 28,
                               'Art Direction' => 101,
                               'Assistant Director' => 134,
                               'Bank' => 133,
                               'Cheif Associate Director' => 111,
                               'Choreographer' => 103,
                               'Cinematographer' => 98,
                               'Costume Desinger' => 109,
                               'Dancer' => 108,
                               'Dialogue' => 119,
                               'Digital Intermediate' => 127,
                               'Distributor' => 118,
                               'DTS Mixing' => 126,
                               'Dubbing Artist' => 131,
                               'Dubbing Studio' => 125,
                               'Finance Controller' => 113,
                               'Lab' => 129,
                               'Light & Sound' => 107,
                               'Locations' => 132,
                               'Make Up' => 104,
                               'Media Director' => 99,
                               'PRO' => 105,
                               'Production Controller' => 102,
                               'Screenplay Actor' => 100,
                               'Screenplay and Script' => 122,
                               'Script' => 66,
                               'Co Producer' => 68,
                               'Promo Trailer Cuts' => 64,
                               'Online Editor' => 63,
                               'Painting Designs' => 62,
                               'Online Promotions' => 61,
                               'Assistant Camera Crew' => 60,
                               'Designs' => 59,
                               'Associate Director' => 21,
                               'Publicity And Promotions' => 22,
                               'Stills' => 51,
                               'Action' => 46,
                               'Production Executive' => 45,
                               'Studio' => 42,
                               'Sound Effects' => 40,
                               'Visual Effects' => 39,
                               'Dubbing' => 36,
                               'Editor' => 35,
                               'Banner' => 24

           );

      $awardcategory_taxonomy_ids = array('1' =>136 ,
                                 '2' => 137,
                                 '3' => 138,
                                 '4' => 139,
                                 '5' => 140,
                                 '6' => 141,
                                 '7' => 142,
                                 '8' => 143,
                                 '9' => 144,
                                 '10' => 145,
                                 '11' => 146,
                                 '12' => 147,
                                 '13' => 148,
                                 '14' => 149,
                                 '15' => 150,
                                 '16' => 151,
                                 '17' => 152,
                                 '18' => 153,
                                 '19' => 154,
                                 '20' => 155,
                                 '22' => 156,
                                 '23' => 157,
                                 '25' => 158,
                                 '26' => 159,
                                 '27' => 160,
                                 '28' => 161,
                                 '29' => 162,
                                 '30' => 163,
                                 '31' => 164,
                                 '32' => 165,
                                 '33' => 166,
                                 '34' => 167,
                                 '35' => 168


             );

      $awardauthority_taxonomy_ids = array('1' => 169,
                                      '2' => 170,
                                      '3' => 171

            );

        $artisttype_ids = array();
        $artisttype_ids[] = $artisttype_taxonomy_ids[$row->AM_Relation_To_Movie];
        foreach($artisttype_ids as $key => $value){
        $row_data->field_artist_type = $value;
        }

        $awardcat_ids = array();
        $awardcat_ids[] = $awardcategory_taxonomy_ids[$row->AM_Category];
        foreach($awardcat_ids as $key1 => $value1){
        $row_data->field_award_category = $value1;
        }

        $awardauth_ids = array();
        $awardauth_ids[] = $awardauthority_taxonomy_ids[$row->AM_Authority];
        foreach($awardauth_ids as $key2 => $value2){
        $row_data->field_award_authority  = $value2;
        }

        if($row->AM_Year == '0000'){
          $row_data->field_year = '';
        }else{
          $row_data->field_year = $row->AM_Year;
        }

         $rows[] = $row_data;
     }

     $artist_ids = array_keys($artist_ids);
     $url = 'http://www.metromatinee.com/?q=importnid';
     $result = service_call($url,$artist_ids);

     $new_rows = array();
     foreach ($rows as $key => $value) {

      $value->field_artist = $result[$value->field_artist];

       $new_rows[] = $value;
     }
    return array("results" => $rows);

   }

   //film awards

   function getFilmAwards($cat,$page,$itemsPerPage) {
       $offset = ($page - 1) * $itemsPerPage;
       $select = mysql_query("SELECT baf.`AFF_Film_Id` , bam.AM_Authority, bam.AM_Category, bam.AM_Relation_To_Movie, bam.AM_Year FROM  `bb_award_for_films` baf LEFT JOIN bb_award_master bam ON baf.`AFF_Award_Master_Id` = bam.AM_Id
                LIMIT $offset,$itemsPerPage");
       $rows = array();
       $film_ids = array();

       while($row = mysql_fetch_object($select))
       	{

          $row_data = new stdClass();

          $row_data->field_film = $row->AFF_Film_Id;
          $film_ids[$row->AFF_Film_Id] = $row->AFF_Film_Id;
          $row_data->title = "Awards";
          $row_data->field_type = "Films";

          $artisttype_taxonomy_ids = array('Producer' => 23,
                                         'Screenplay' => 67,
                                         'Actor' => 1,
                                         'Background Music' => 44,
                                         'Cinematography' => 29,
                                         'Director' => 9,
                                         'Sound Design' => 41,
                                         'Actress' => 6,
                                         'Story' => 65,
                                         'Lyrics' => 28,
                                         'Art Direction' => 101,
                                         'Assistant Director' => 134,
                                         'Bank' => 133,
                                         'Cheif Associate Director' => 111,
                                         'Choreographer' => 103,
                                         'Cinematographer' => 98,
                                         'Costume Desinger' => 109,
                                         'Dancer' => 108,
                                         'Dialogue' => 119,
                                         'Digital Intermediate' => 127,
                                         'Distributor' => 118,
                                         'DTS Mixing' => 126,
                                         'Dubbing Artist' => 131,
                                         'Dubbing Studio' => 125,
                                         'Finance Controller' => 113,
                                         'Lab' => 129,
                                         'Light & Sound' => 107,
                                         'Locations' => 132,
                                         'Make Up' => 104,
                                         'Media Director' => 99,
                                         'PRO' => 105,
                                         'Production Controller' => 102,
                                         'Screenplay Actor' => 100,
                                         'Screenplay and Script' => 122,
                                         'Script' => 66,
                                         'Co Producer' => 68,
                                         'Promo Trailer Cuts' => 64,
                                         'Online Editor' => 63,
                                         'Painting Designs' => 62,
                                         'Online Promotions' => 61,
                                         'Assistant Camera Crew' => 60,
                                         'Designs' => 59,
                                         'Associate Director' => 21,
                                         'Publicity And Promotions' => 22,
                                         'Stills' => 51,
                                         'Action' => 46,
                                         'Production Executive' => 45,
                                         'Studio' => 42,
                                         'Sound Effects' => 40,
                                         'Visual Effects' => 39,
                                         'Dubbing' => 36,
                                         'Editor' => 35,
                                         'Banner' => 24

                     );

        $awardcategory_taxonomy_ids = array('1' =>136 ,
                                            '2' => 137,
                                            '3' => 138,
                                            '4' => 139,
                                            '5' => 140,
                                            '6' => 141,
                                            '7' => 142,
                                            '8' => 143,
                                            '9' => 144,
                                            '10' => 145,
                                            '11' => 146,
                                            '12' => 147,
                                            '13' => 148,
                                            '14' => 149,
                                            '15' => 150,
                                            '16' => 151,
                                            '17' => 152,
                                            '18' => 153,
                                            '19' => 154,
                                            '20' => 155,
                                            '22' => 156,
                                            '23' => 157,
                                            '25' => 158,
                                            '26' => 159,
                                            '27' => 160,
                                            '28' => 161,
                                            '29' => 162,
                                            '30' => 163,
                                            '31' => 164,
                                            '32' => 165,
                                            '33' => 166,
                                            '34' => 167,
                                            '35' => 168

          );
            $awardauthority_taxonomy_ids = array('1' => 169,
                                                  '2' => 170,
                                                  '3' => 171

                        );

            $artisttype_ids = array();
            $artisttype_ids[] = $artisttype_taxonomy_ids[$row->AM_Relation_To_Movie];
            foreach($artisttype_ids as $key => $value){
              $row_data->field_artist_type = $value;
            }

            $awardcat_ids = array();
            $awardcat_ids[] = $awardcategory_taxonomy_ids[$row->AM_Category];
            foreach($awardcat_ids as $key1 => $value1){
             $row_data->field_award_category = $value1;
            }

            $awardauth_ids = array();
            $awardauth_ids[] = $awardauthority_taxonomy_ids[$row->AM_Authority];
            foreach($awardauth_ids as $key2 => $value2){
             $row_data->field_award_authority  = $value2;
            }

            if($row->AM_Year == '0000'){
            $row_data->field_year = '';
            }else{
            $row_data->field_year = $row->AM_Year;
            }

            $rows[] = $row_data;

         	}

          $film_ids = array_keys($film_ids);
          $url = 'http://www.metromatinee.com/?q=importfilmid';
          $result = service_call($url,$film_ids);
          $new_rows = array();
          foreach ($rows as $key => $value) {
          $value->field_film = $result[$value->field_film];
          //print_r($value);
          $new_rows[] = $value;
          }
       return array("results" => $rows);
   }


function getMovieReviews($page,$itemsPerPage){
 // $id= 10610;
 $offset = ($page - 1) * $itemsPerPage;
 $select = mysql_query("SELECT MVR_Id,MVR_Film_Id,MVR_Critix_Order,MVR_Date,MVR_Title,MVR_Review,MVR_Image,MVR_Views,FeaturedReviews FROM bb_movie_review_master mr LIMIT $offset,$itemsPerPage");
 $rows = array();
 $film_ids = array();

 while($row = mysql_fetch_object($select))
   {
      $row_data = new stdClass();
    //  $row_data->mvr_id = $row->MVR_Id;
      $row_data->mvr_film_id = $row->MVR_Film_Id;
      $film_ids[$row->MVR_Film_Id] = $row->MVR_Film_Id;
      //$row_data->film_id = $row->MVR_Film_Id;
      $row_data->mvr_critics_order = $row->MVR_Critix_Order;
      $row_data->mvr_date = $row->MVR_Date;
      $img_url = "http://old.metromatinee.com/malayalam_movie_reviews/images/";
      if($row->MVR_Image == ''){
      $row_data->mvr_image = '';
      }else{
      $row_data->mvr_image = $img_url.$row->MVR_Image;
      }
      $row_data->title = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '',$row->MVR_Title);
      $row_data->mvr_review = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '',$row->MVR_Review);
      $row_data->mvr_featured_reviews = $row->FeaturedReviews;
      $row_data->status = '1';
      $rows[] = $row_data;

   }
   $film_ids = array_keys($film_ids);
   $url = 'http://www.metromatinee.com/?q=importfilmid';
   $result = service_call($url,$film_ids);
   //print_r($result);
   $new_rows = array();
   foreach ($rows as $key => $value) {
   $value->mvr_film_id = $result[$value->mvr_film_id];

   }
  return array("results" => $rows);
 //return $rows;
}

//get films stage
function getFilmStage($id){

$query = mysql_query("SELECT CSL_Id,FILM_Id FROM bb_film_ComingSoon_List fcl where fcl.FILM_Id = $id;");

if (mysql_num_rows($query) > 0)
{
  return '8';
}

$query = mysql_query("SELECT FILM_Id FROM bb_film_in_theatre fit where fit.FILM_Id = $id;");

if (mysql_num_rows($query) > 0)
{
  return '7';
}

$query = mysql_query("SELECT FILM_Id FROM bb_film_on_dvd fod where fod.FILM_Id = $id;");
if (mysql_num_rows($query) > 0)
{
  return '3';
}

return 0;
}


function get_film_category($id) {

  $select = mysql_query("SELECT * FROM bb_film_category_child fc where fc.FILM_Id = $id limit 1;");

  $rows = array();  
  while($row = mysql_fetch_object($select))
        {
          $row_data = new stdClass();
          $film_category_taxonomy_ids = array('Comedy' => 16,
                                              'Action' => 14,
                                              'Family Thriller' => 11,
                                              'Drama' => 12,                              
                                              'History' => 203,
                                              'Thriller' => 13,
                                              'War' => 205,
                                              'Romance' => 17,
                                              'Family Drama' => 206,
                                              'Suspense' => 15,
                                              'Crime' => 207,
                                              'Musical' => 208,
                                              'Detective' => 209,
                                              'Mystery' => 204,
                                              'Sport' => 18,
                                              'Horror' => 2,
                                              'Adventure' => 212,
                                              'Fantasy' => 20,
                                              'Family' => 210,
                                              'Social' => 211,
                                              'Children' => 19,                              
                                        );
          $film_category_values = array();          
          $category_values = film_category_name($row->FLCM_Id);
          $category = explode(",",$category_values[0]->FLCM_Name);
          foreach($category as $key => $val) {
            $film_category_values[] = $film_category_taxonomy_ids[trim($val)];
          }               
  }   
  //return $film_category_values; 
  return array("results" => $film_category_values);     
}

function film_category_name($id) {

  $select = mysql_query("SELECT FLCM_Name FROM bb_film_category_master fcm where  fcm.FLCM_Id = $id;");

  $rows = array();
     while($row = mysql_fetch_object($select))
       {      
        $rows[] = $row;
       }
    return $rows;
}

function get_json_data_artist_nid($artist_ids) {

  $str = file_get_contents('nid.json');
  $json_array = json_decode($str, true);
  $nids = array();

  foreach($artist_ids as $key => $value) {
    if( isset($json_array[$value])){
      $nids[$value] = $json_array[$value];
    }
  }

  return $nids;
}
