<?php
include "../../sysconf/global_func.php";
include "../../sysconf/session.php";
include "../../sysconf/db_config.php";

// load function from url
if(function_exists($_GET['f'])) {
   $_GET['f']();
}


function telesales_get_select_status_enable($idname, $name, $status) {
  $sel0 = "";
  $sel1 = "";

  if ($status == "0")
    $sel0 = "selected";
  else if ($status == "1")   
    $sel1 = "selected";

  $selectout = "<SELECT id=\"$idname\" name=\"$name\" class=\"select2 form-control\" style=\"width:100%;\">     
  <option value=\"0\" $sel0>Disable</option>
  <option value=\"1\" $sel1>Enable</option>
  </SELECT>";

  return $selectout;                     
}

function telesales_get_select_action($id, $name, $value) {
  $sel = "<div class='select2-input'>
  <SELECT id='$id' name='$name' class='select2 form-control' required='required' style='width:100%;'>";
  $sel .= "<option value='' >--Selected--</option>";    
  $sel .= "</SELECT>
  </div>";
  return $sel;
}

function telesales_skill_outbound($conDB, $id, $name, $skill,$agentid) {

  $sql = "SELECT * FROM cc_group_leader a WHERE a.agent_id='$agentid'";
  $res = mysqli_query($conDB, $sql);
  if ($row = mysqli_fetch_array($res)) {
    $group_id = $row['group_id'];
  }
  $sql3 = "SELECT DISTINCT(a.agent_id) FROM cc_group_agent a WHERE a.group_id='$group_id'";
  $res = mysqli_query($conDB, $sql3);
  while ($row = mysqli_fetch_array($res)) {
    $id_agent_arr[] = $row['agent_id'];
  }
  $id_agent = implode(",",$id_agent_arr);
  $sql4 = "SELECT DISTINCT(a.skill_id) FROM cc_skill_agent a WHERE a.agent_id IN ($id_agent)";
  $res = mysqli_query($conDB, $sql4);
  while ($row = mysqli_fetch_array($res)) {
    $id_skill_arr[] = $row['skill_id'];
  }
  $id_skill = implode(",",$id_skill_arr);
  $sel = "<SELECT id=\"$id\" name=\"$name\" class=\"select2 form-control\" style=\"width:100%;\">";
  $sel .= "<option value=\"\" selected>--Selected--</option>"; 
  $sel .= "<option value=\"0\" >All Skill Outbound</option>"; 
  $sql_str1 = "SELECT a.skill_id, b.skill_name FROM cc_skill_feature a, cc_skill b WHERE a.skill_id=b.id AND a.skill_feature = 10 AND b.id IN ($id_skill) ORDER BY a.skill_id";
  $sql_res1  = execSQL($conDB, $sql_str1);    
  while ($sql_rec1 = mysqli_fetch_array($sql_res1)) {
    if($sql_rec1['skill_id'] == $skill) {
      $sel .= "<option value=\"".$sql_rec1['skill_id']."\" selected>".$sql_rec1['skill_name']."</option>";  
    } else {
      $sel .= "<option value=\"".$sql_rec1['skill_id']."\" >".$sql_rec1['skill_name']."</option>";  

    }
  }

  $sel .= "</SELECT>";

  return $sel;
}

function get_agent_by_skill(){
  $condb = connectDB();
  $v_agentgroup = get_session('v_agentgroup');
  $skill_id     = get_param("skill_id");

  $skill_id != '0' && $skill_id != '' ? $where = "AND b.skill_id=$skill_id": $where=''; 

  $sel = '';
  $sel .= '<option value="" disabled selected>--- select agent ---</option>';
  $sql = "SELECT c.id, c.agent_id, c.agent_name, a.group_id, b.skill_id
            FROM cc_group_agent a, cc_skill_agent b
            LEFT JOIN cc_agent_profile c ON b.agent_id=c.id 
            WHERE a.agent_id=b.agent_id AND a.agent_id=c.id
            AND c.`status`=1
            AND c.agent_level=1
            AND a.group_id=$v_agentgroup
            $where  
            GROUP BY c.id";
  $res = mysqli_query($condb, $sql);
  while ($row = mysqli_fetch_array($res)) {
    $agent_id   = $row['id'];
    $agent_name = $row['agent_name'];
    $sel .= '<option value="'.$agent_id.'">'.$agent_name.'</option>';
  }

  $data['sel'] = $sel;
  $data['sql'] = $sql;
  echo json_encode($data);

}

/*
* desc function : get data bucket with agent_id = 0
* database target : cc_ts_data_bucket
* where agent_id = 0
*/
function get_datatable_bucket(){
  $condb  = connectDB();
    //$condb2 = connectDB2();
  $condb2 = connectDB();
  $v_agentid = get_session('v_agentid');
  $cmbbucket = $_GET['cmbbucket'];
  $filterBy     = $_GET['filterBy'];
  $filterValue  = $_GET['filterValue'];

  $cmbbucket != '' ? $wcampaign = 'AND c.id = '.$cmbbucket: $wcampaign = '';
  if ($cmbbucket=="") {
    $wcampaign = ' AND a.bucket_id = 0 ';
  }
  $filterValue != "" ? $wfilter = "AND ".$filterBy." = '".$filterValue."' ": $wfilter="";

  $aColumns = array("a.id", "c.bucket_code", "c.bucket_name", "a.order_no", "a.customer_name", "a.customer_id");

  $sIndexColumn = "a.id";

  //field date 
  // $start_date_field = "a.assignmentdate";
  // $end_date_field    = "a.assignmentdate";

  /*
  $sFromTable = "FROM cc_customer_profile_prv_prv a
  LEFT JOIN cc_ts_data_bucket_prv b ON a.id=b.cust_id
  LEFT JOIN cc_agent_profile c ON b.agent_id=c.id
  LEFT JOIN cc_teleupload_bucket_prv d ON b.subcam_id=d.id
  LEFT JOIN cc_teleupload_bucket_category_prv e ON b.cam_id=e.id
  WHERE 1=1 AND b.status in (1,0) $sqlfrom"; */ 

  $mindate = date('Y-m-d', strtotime('-90 days'))." 00:00:00";
  $maxdate = date('Y-m-d')." 23:59:59";

  $sFromTable = "FROM cc_teleupload_data a LEFT JOIN cc_teleupload_label_priority d ON a.label_priority=d.id, cc_teleupload_bucket c 
  WHERE 
  c.id=a.bucket_id AND a.spv_id=".$v_agentid." AND d.label_status=1 ".$wcampaign." ".$wfilter." 
  AND a.update_time >= '".$mindate."' AND a.update_time <= '".$maxdate."'";//AND (a.`agent_id`=$v_agentid OR a.`agent_id`=0)
  //                     left outer join ak_customer b on (a.id_cust=b.id)
  //echo $sFromTable;    
  $date_period  = $_GET['date_period'];
  $txt_search   = $_GET['txt_search'];

  $sDate = "";
  if($date_period!='') {
    $start_date   = trim(substr($date_period,0,10));
    $end_date     = trim(substr($date_period,12));
    /* search date hidden
    $sDate = " AND $start_date_field >= '$start_date 00:00:00'
    AND $end_date_field <= '$end_date 23:59:59' ";
    */
  }

  /* Individual column filtering */
  for ( $i=0 ; $i<count($aColumns) ; $i++ ) {
    if($_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' ) {
      if($sWhere == "" ) {
        $sWhere = "AND ";
      } else {
       $sWhere .= " AND ";
      }

     $sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($condb, $_GET['sSearch_'.$i])."%' ";
    }
  }
  // echo "### $sWhere";

  $sQuery = "
    SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
    $sFromTable
    $sWhere
    $sDate
    $sOrder
    $sLimit
  ";  //echo $sQuery;
  $rResult = mysqli_query($condb, $sQuery);
  $eQuery = $sQuery;

  /* Data set length after filtering */
  $sQuery = "
    SELECT FOUND_ROWS()
  ";
  $rResultFilterTotal = mysqli_query($condb,$sQuery);
  $aResultFilterTotal = mysqli_fetch_array($rResultFilterTotal);
  $iFilteredTotal = $aResultFilterTotal[0];

  /* Total data set length */
  $sQuery = "
    SELECT COUNT(".$sIndexColumn.")
    $sFromTable
    $sWhere
  ";  //echo $sQuery;
  $rResultTotal = mysqli_query($condb, $sQuery);
  $aResultTotal = mysqli_fetch_array($rResultTotal);
  $iTotal = $aResultTotal[0];


  /*
  * Output
  */
  $output = array(
    "sEcho" => intval($_GET['sEcho']),
    "iTotalRecords" => $iTotal,
    "iTotalDisplayRecords" => $iFilteredTotal,
    "aaData" => array(),
    "query" => $eQuery
  );

  while ($aRow = mysqli_fetch_array($rResult)) {
    $row = array();

    for ( $i=0 ; $i<count($aColumns) ; $i++ ) {
      if ( $aColumns[$i] == "version" ) {
        //$row[] = ($aRow[ $aColumns[$i] ]=="0") ? '-' : $aRow[ $aColumns[$i] ];
        $row[] = ($aRow[$i]=="0") ? '-' : $aRow[$i];
      } else if ( $aColumns[$i] != ' ' ) {
        if($i == "0") {
          $row[] = " <input type='checkbox' id='check_assign' name='check_assign' value='".$aRow[$i]."' class='row_bulk' onclick=\"checkBulk()\" required>";
        } else {
         $row[] = $aRow[$i];
        }
      }
    }

    $output['aaData'][] = $row;
  }

  freeResSQL($rResult);
  freeResSQL($rResultFilterTotal);
  freeResSQL($rResultTotal);
  disconnectDB($condb);
  echo json_encode($output);
}

function count_data(){
  $condb = connectDB();
  $v_agentid = get_session('v_agentid');

  $region               = get_param("region");
  $cabang               = get_param("cabang");
  $kategori_kendaraan   = get_param("kategori_kendaraan");
  $asset_type_kendaraan = get_param("asset_type_kendaraan");
  $status_call          = get_param("status_call");
  $bucket_id            = get_param("bucket");
  $priority_label       = get_param("priority_label");
  $assigned_agent       = get_param("assigned_agent");
  $last_call_dt         = get_param("last_call_dt");


  $total_data           = array();
  $where = '';


  $sqlsel="";
  if ($status_call == '') {
    $status_call = '0';
  }

  $dateperiod  = get_param("last_call_period");
  $date_from   = substr($dateperiod,0,10);
  $date_to     = substr($dateperiod,12);

  if($status_call != '0' && $status_call != 'null') {
    if ($status_call == 'Fresh') {
      $where .= " AND a.last_phonecall = '' ";
    }else{
      if ($last_call_dt == "1") {
        $wdateft = "";//where date from to
        if ($date_from != "" && $date_to != "") {
          $date_from  .= " 00:00:00";
          $date_to    .= " 23:59:59";
          $wdateft = "AND a.insert_time >= '".$date_from."' 
                      AND a.insert_time <= '".$date_to."' ";
        } 
        // get data by period
        // $sqlsel = "SELECT DISTINCT(a.buck_id) as iddes
        //             FROM cc_teleupload_call_session a 
        //             WHERE 
        //             a.result=$status_call AND 
        //             a.bucket_id=$bucket_id AND 
        //             a.insert_time >= '$date_from' AND
        //             a.insert_time <= '$date_to'";
        if($wdateft == ""){
          $date_default = date("Y-m-d", strtotime("-3 month"));
          $date_from    = $date_default." 00:00:00";
          $date_to      = $date_default." 23:59:59";
            $wdateft = "AND a.insert_time >= '".$date_from."' 
                        AND a.insert_time <= '".$date_to."' ";
        }
        $sqlsel = " SELECT a.buck_id as iddes
        FROM cc_teleupload_call_session a 
        WHERE 
        a.result=$status_call AND 
        a.bucket_id=$bucket_id 
        $wdateft
        GROUP BY a.buck_id ";
        $ressel = mysqli_query($condb, $sqlsel);
        // if ($rowsel = mysqli_fetch_array($ressel)) {
        //   $iddes = $rowsel["iddes"];

        //   $where .= " AND a.id IN ($iddes)";
        // }
        $rowsel = mysqli_fetch_all($ressel);
        $iddes =  implode(', ', array_map(function ($entry) {
                      return $entry[0];
                    }
                  , $rowsel ));

        if ($iddes != "") {
          $where .= " AND a.id IN ($iddes) ";
        }else{
          $where .= " AND a.id = 0 ";
        }
      }else{
        $where .= " AND a.last_phonecall IN ($status_call) ";
        $where = str_replace("99", "0", $where);
      }
    }
  }

  $assigned_agent != "" ? $where .= " AND a.agent_id = $assigned_agent " : 0;

  // $region != 0 ? $where .= " AND a.region_code IN ($region)" : 0;

  if ($region != '0') {
    $region=str_replace(",", "','", $region);
    $where .= " AND a.region IN ('$region') ";
  }

  // if ($last_call_dt == "1") {
  //   $where      .= "AND DATE(a.last_followup_time) >= '".$date_from."' AND DATE(a.last_followup_time) <= '".$date_to."'";      
  // }
  // $kategori_kendaraan != 0 ? $where .= " AND a.region_code IN ($asset_type_kendaraan)" : 0;

  if ($asset_type_kendaraan != '') {
    $where .= " AND a.asset_type IN ($asset_type_kendaraan) ";
  }

  ($cabang != '' OR $cabang != 0) ? $where .= " AND a.cabang IN ($cabang) " : 0;
  // ($priority_label != '') ? $where .= " AND a.label_priority IN ($priority_label) " : 0;
  if($priority_label != '') {
    $where .= " AND a.label_priority IN ($priority_label) ";
  } else{
    $sqlpl = "SELECT DISTINCT(b.id) AS code, b.label_desc AS name
          FROM cc_teleupload_data a
          LEFT JOIN cc_teleupload_label_priority b ON a.label_priority=b.id
          WHERE 1=1 AND b.id IS NOT NULL AND a.bucket_id='".$bucket_id."' AND a.spv_id='$v_agentid' AND b.label_status=1";
    $respl = mysqli_query($condb, $sqlpl);
    $rowpl = mysqli_fetch_all($respl);
    //id priority label
    $idpl  =  implode(', ', array_map(function ($entry) {
                  return $entry[0];
                }
              , $rowpl ));

    if ($idpl != "") {
      $where .= " AND a.label_priority IN ($idpl) ";
    }
    mysqli_free_result($respl);


  }
  
  $sql = "SELECT count(a.id) as total_data FROM cc_teleupload_data a WHERE a.bucket_id='$bucket_id' AND a.spv_id='$v_agentid' $where ";//AND (a.agent_id = $v_agentid OR a.agent_id=0)
  $res = mysqli_query($condb, $sql);
  if ($row = mysqli_fetch_array($res)) {
    $total_data = $row;
  }

  $total_data['sql_sel']  = $sqlsel;
  $total_data['sql']      = $sql;

  echo json_encode($total_data);
}

function load_det_bucket(){
  $condb  = connectDB();
    //$condb2 = connectDB2();
  $condb2 = connectDB();
  $v_agentid = get_session("v_agentid");
  $bucket_id  = get_param("bucket_id");
  $data = array();
  $iregion = 0;
  $itype = 0;

  $sql = "SELECT group_concat(distinct(a.asset_type)) AS typeasset,group_concat(distinct(a.region)) AS region FROM cc_teleupload_data a
WHERE a.bucket_id=$bucket_id AND a.spv_id=$v_agentid ";
  $res = mysqli_query($condb2, $sql);
  if ($row = mysqli_fetch_array($res)) {
    $data['prio_tipe_kendaraan'] = $row['kendaraan'];
    
    // get region code
    $region = $row['regional'];
    $region != '0' && $region != '' ? $wregion = "a.id IN ($region)" : $wregion = '';
    $sqlreg = "SELECT a.region_code FROM cc_master_region a WHERE $wregion";
    $resreg = mysqli_query($condb2, $sqlreg);
    $data['regional'] = '';
    while($rowreg = mysqli_fetch_array($resreg)){
      $iregion == 0 ? $data['regional'] .= $rowreg['region_code']: $data['regional'] .= ', '.$rowreg['region_code']; 
      $iregion++;
    }

    // get asset type
    $asset = $row['type_asset'];
    $asset != '0' && $asset != '' ? $wasset = "a.asset_type_id IN ($asset)" : $wasset = '';
    $sqlasset = "SELECT a.asset_type_code FROM cc_master_type_asset a WHERE $wasset";
    $resasset = mysqli_query($condb2, $sqlasset);
    $data['asset_type_kendaraan'] = '';  
    while($rowasset = mysqli_fetch_array($resasset)){
      $itype == 0 ? $data['asset_type_kendaraan'] .= "'".$rowasset['asset_type_code']."'": $data['asset_type_kendaraan'] .= ", '".$rowasset['asset_type_code']."'"; 
      $itype++;
    }
  }
  echo json_encode($data);
}

function assign_by_contract(){
  $condb = connectDB();

  $v_agentid            = get_session("v_agentid");
  $v_agentlevel         = get_session("v_agentlevel");

  $method               = get_param("assignment_method");
  $iddeb                = get_param("iddeb");
  $region               = get_param("regional");
  $region               = implode(',', $region);
  $cabang               = get_param("cabang");
  $kategori_kendaraan   = get_param("kategori_kendaraan");
  $asset_type_kendaraan = get_param("asset_type_kendaraan");
  $priority_label       = get_param("priority_label");
  $assigned_agent       = get_param("assigned_agent");
  $last_call_dt         = get_param("last_call_dt");

  $where = '';

  // if ($last_call_dt == "1") {
  //   $where      .= "AND DATE(a.last_followup_time) >= '".$date_from."' AND DATE(a.last_followup_time) <= '".$date_to."'";
  // }

  $status_call  = get_param("status_call");
  $status_calls = $status_call;

  // $status_calls = '';
  // foreach ($status_call as $key => $value) {
  //   if ($value != "" && $value!="0") {
  //     $key == 0 ? $status_calls .= "'".$value."'": $status_calls .= ",'".$value."'";
  //   }
  // }

  $agent_to             = get_param("agent");
  $bucket          = get_param("bucket");
  
  
  $total_data           = get_param("val_num");
  $fomni_id             = get_param("fomni_id");


  $where_status = '';

  $dateperiod  = get_param("last_call_periode");
  $date_from   = substr($dateperiod,0,10);
  $date_to     = substr($dateperiod,12);
  
  $sqlsel="";
  if($status_calls != '0') {
    if ($status_call == 'Fresh') {
      $where .= " AND a.last_phonecall = '' ";
    }else{
      if ($last_call_dt == "1") {
        // $date_from  .= " 00:00:00";
        // $date_to    .= " 23:59:59";
        $wdateft = "";//where date from to
        if ($date_from != "" && $date_to != "") {
          $date_from  .= " 00:00:00";
          $date_to    .= " 23:59:59";
          $wdateft = "AND a.insert_time >= '".$date_from."' 
                      AND a.insert_time <= '".$date_to."' ";
        }
        if($wdateft == ""){
          $date_default = date("Y-m-d", strtotime("-3 month"));
          $date_from    = $date_default." 00:00:00";
          $date_to      = $date_default." 23:59:59";
            $wdateft = "AND a.insert_time >= '".$date_from."' 
                        AND a.insert_time <= '".$date_to."' ";
        }
        // get data by period
        $sqlsel = "SELECT a.buck_id as iddes
                    FROM cc_teleupload_call_session a
                    WHERE 
                    a.result=$status_call AND 
                    a.bucket_id=$bucket 
                    $wdateft
                    GROUP BY a.buck_id ";
        $ressel = mysqli_query($condb, $sqlsel);
        // if ($rowsel = mysqli_fetch_array($ressel)) {
        //   $iddes = $rowsel["iddes"];

        //   $where .= " AND a.id IN ($iddes)";
        // }
        $rowsel = mysqli_fetch_all($ressel);
        $iddes =  implode(', ', array_map(function ($entry) {
                      return $entry[0];
                    }
                  , $rowsel ));

        if ($iddes != "") {
          $where .= " AND a.id IN ($iddes)";
        }else{
          $where .= " AND a.id = 0";
        }
      }else{
        $where .= " AND a.last_phonecall IN ($status_call) ";
        $where = str_replace("99", "0", $where);
      }
    }
  }

  $priority_label = implode(",", $priority_label);
  // ($priority_label != 0) ? $where .= " AND a.label_priority IN ($priority_label)" : 0;
  if($priority_label != 0) {
    $where .= " AND a.label_priority IN ($priority_label) ";
  } else{
    $sqlpl = "SELECT DISTINCT(b.id) AS code, b.label_desc AS name
          FROM cc_teleupload_data a
          LEFT JOIN cc_teleupload_label_priority b ON a.label_priority=b.id
          WHERE 1=1 AND b.id IS NOT NULL AND a.bucket_id='".$bucket_id."' AND a.spv_id='$v_agentid' AND b.label_status=1";
    $respl = mysqli_query($condb, $sqlpl);
    $rowpl = mysqli_fetch_all($respl);
    //id priority label
    $idpl  =  implode(', ', array_map(function ($entry) {
                  return $entry[0];
                }
              , $rowpl ));

    if ($idpl != "") {
      $where .= " AND a.label_priority IN ($idpl) ";
    }
    mysqli_free_result($respl);


  }
  $asset = '';
  foreach ($asset_type_kendaraan as $key => $value) {
    if ($value != 0) {
      $key == 0 ? $asset .= "'".$value."'": $asset .= ",'".$value."'";
    }
  }

  $cabangs = '';
  foreach ($cabang as $key => $value) {
    if ($value != "" && $value!="0") {
      $key == 0 ? $cabangs .= "'".$value."'": $cabangs .= ",'".$value."'";
    }
  }

  

  $assigned_agent != "" ? $where .= "AND a.agent_id = $assigned_agent " : 0;

//   $region != '0' ? $where .= " AND a.region_code IN ($region) " : 0;
//   // $kategori_kendaraan != '0' ? $where .= "AND a.region_code = '$asset_type_kendaraan'" : 0;
//   if ($asset != '') {
//     $where .= "AND a.asset_type IN ($asset) ";
//   }
// ($cabangs != '') ? $where .= " AND a.cabang_code IN ($cabangs)" : 0;
// if($status_call != '0') {
//   if ($status_call == 'Fresh') {
//     $where .= " AND a.prospect_stat = '' ";
//   }else{
//     $where .= " AND a.prospect_stat = '$status_call' ";
//   }
// }


  // $region != 0 ? $where .= " AND a.region_code IN ($region)" : 0;

  if ($region != "0") {
    $region=str_replace(",", "','", $region);
    $where .= "AND a.region IN ('$region')";
  }
  
  // $kategori_kendaraan != 0 ? $where .= " AND a.region_code IN ($asset_type_kendaraan)" : 0;

  if ($asset_type_kendaraan != '') {
    if (in_array('0', $asset_type_kendaraan)) {
        
    }else{
        $where .= "AND a.asset_type IN ($asset)";
    }
    
  }

  ($cabangs != '') ? $where .= "AND a.cabang IN ($cabangs)" : 0;
  // if($status_call != '0') {
  //   if ($status_call == 'Fresh') {
  //     $where .= " AND a.last_followup_call = '' ";
  //   }else{
  //     $where .= " AND a.last_followup_call = '$status_call' ";
  //   }
  // }

  
  // if($status_call != '0') {
  //   if ($status_call == 'Fresh') {
  //     $where .= " AND a.last_followup_call = '' ";
  //   }else{
  //     $where .= " AND a.last_followup_call IN ($status_call) ";
  //   }
  // }
  switch ($method) {
    case '1':
      $tot_agent            = count($fomni_id);
      $loop_agent           = 0;
      // echo $total_data;
      if ($total_data != 0 || $total_data != '') {
        $tot_assign   = 0;
        $index_assign = 0;
        $sql          = "SELECT a.id FROM cc_teleupload_data a WHERE a.bucket_id='$bucket' AND a.spv_id=$v_agentid $where ORDER BY a.id ASC";//echo "string $sql"; AND (a.agent_id = $v_agentid OR a.agent_id=0)
        $temp_sql     = $sql;
        $res          = mysqli_query($condb, $sql);
        while ($row = mysqli_fetch_array($res)) {
          if ($index_assign < $total_data) {
            $id = $row['id'];
            if ($loop_agent == $tot_agent) {
              $loop_agent = 0;
            }
            $sqlsa = "UPDATE cc_teleupload_data SET 
                    agent_id            ='$fomni_id[$loop_agent]',
                    assign_status       ='0',
                    assign_time     =now()
                    where id ='$id'";  //echo $sqlsa; 
            if(mysqli_query($condb,$sqlsa)){
              // $sqllog = "INSERT INTO cc_teleupload_data_log (id_cust_detail,  polo_order_in_id,  distributed_date,  source_data,  region_code,  region_name,  cabang_code,  cabang_name,  cabang_coll,  cabang_coll_name,  kapos_name,  agrmnt_no,  order_no,  product,  product_cat,  product_offering_code,  order_no_ro,  customer_id,  customer_name,  nik_ktp,  religion,  tempat_lahir,  tanggal_lahir,  nama_pasangan,  tanggal_lahir_pasangan,  child_name,  child_birthdate,  legal_alamat,  legal_rt,  legal_rw,  legal_provinsi,  legal_kabupaten,  legal_city,  legal_kecamatan,  legal_kelurahan,  legal_kodepos,  legal_sub_kodepos,  survey_alamat,  survey_rt,  survey_rw,  survey_provinsi,  survey_kabupaten,  survey_city,  survey_kecamatan,  survey_kelurahan,  survey_kodepos,  survey_sub_kodepos,  city_id,  gender,  mobile_1,  mobile_2,  phone_1,  phone_2,  office_phone_1,  office_phone_2,  profession_name,  profession_cat_name,  job_position,  industry_type_name,  monthly_income,  monthly_instalment,  plafond,  cust_rating,  suppl_name,  suppl_code,  pekerjaan,  jenis_pekerjaan,  detail_pekerjaan,  oth_biz_name,  hobby,  kepemilikan_rumah,  customer_id_ro,  customer_rating,  nama_dealer,  kode_dealer,  no_mesin,  no_rangka,  asset_type,  asset_category,  asset_desc,  asset_price_amount,  item_id,  item_type,  item_desc,  item_year,  otr_price,  kepemilikan_bpkb,  agrmnt_rating,  status_kontrak,  angsuran_ke,  sisa_tenor,  tenor,  release_date_bpkb,  max_past_due_date,  tanggal_jatuh_tempo,  maturity_date,  os_principal,  product_category,  sisa_piutang,  kilat_pintar,  aging_pembiayaan,  jumlah_kontrak_per_cust,  estimasi_terima_bersih,  cycling,  task_id,  jenis_task,  soa,  down_payment,  ltv,  call_stat,  answer_call,  prospect_stat,  reason_not_prospect,  confirmation,  notes,  sla_remaining,  started_date,  emp_position,  application_id,  application_ia,  dukcapil_stat,  field_person_name,  negative_cust,  notes_new_lead,  visit_dt,  input_dt,  sub_sitrict_kat_code,  contact_no,  source_data_mss,  referantor_code,  referantor_name,  supervisor_name,  note_telesales,  submited_dt,  mss_stat,  wise_stat,  visit_stat,  survey_stat,  flag_void_sla,  eligible_flag,  eligible_flag_dt,  dtm_crt,  usr_crt,  rtm_upd,  usr_upd,  app_no,  application_stat,  bpkb_out,  brand,  city_leg,  city_res,  cust_photo,  dp_pct,  f_card_photo,  ia_app,  id_photo,  jenis_pembiayaan,  monthly_expense,  npwp_no,  order_id,  other_biz_name,  ownership,  pos_dealer,  promotion_activity,  referantor_code_1,  referantor_code_2,  referantor_name_1,  referantor_name_2,  sales_dealer,  send_flag_wise,  spouse_id_photo,  send_flag_mss,  flag_pre_ia,  task_id_mss,  profession_code,  sales_dealer_id,  profession_category_code,  flag_void_sla_tele,  status_task_mss,  priority_level,  outstand_principal,  outstand_monthly_instalment,  rrd_date,  group_id,  sumber_order,  special_cash_flag,  created_by,  modif_by,  insert_time,  modif_time,  bucket_id,  external_code,  priority,  branch_name,  phone,  product_type,  vehicle_year,  plafond_price,  installment_price,  referentor,  desc_note,  desc_note_adv,  assign_by,  agent_id,  assign_time,  reassign,  reassign_by,  reassign_time,  first_call_time,  first_followup_by,  last_call_time,  last_followup_by,  call_status,  call_status_sub1,  call_status_sub2,  total_dial,  total_phone,  total_course,  status,  status_bypass,  status_approve,  close_time,  close_by,  close_approve_time,  close_approve_by,  qa_approve_status,  qa_approve_note,  qa_approve_time,  qa_approve_by)
              //                 SELECT * FROM cc_teleupload_data
              //                 WHERE id='$id'";
              // mysqli_query($condb,$sqllog);
              //start new
              $agnt_id='';
              $agnt_id = $fomni_id[$loop_agent];
              $tot_peragent[$agnt_id] = $tot_peragent[$agnt_id]+1; 

              $param_updt .="|".$sqlsa;
              //end new
              $tot_assign += 1;
              $loop_agent++;
            }
            $index_assign++;
          }else{
            break;
          }
        }

        //start new 
        $detail_assign="";
        foreach($tot_peragent as $x => $val) {
          if ($detail_assign=="") {
            $detail_assign = "$x : $val ";
          }else{
            $detail_assign .= ", $x : $val ";
          }
          
        }
        $param_updt = mysqli_real_escape_string($condb, $param_updt);
        //end new

        if ($tot_assign > 0) {
          $sqlins = "INSERT INTO cc_agent_trail_log SET agent_id = '".$v_agentid."', trail_desc='Success, ".$index_assign.", ".$tot_assign.", ".$total_data.",|$detail_assign|, QUERY : ".mysqli_real_escape_string($condb, $sql)."', insert_time=now()";
          mysqli_query($condb, $sqlins);
          echo 'Success!|'.$tot_assign.'|'.$total_data;
          // echo 'Success!|'.$temp_sql;
        }else{
          if ($total_data > 0) {
            $sqlins = "INSERT INTO cc_agent_trail_log SET agent_id = '".$v_agentid."', trail_desc='Failed Error, ".$index_assign.", ".$tot_assign.", ".$total_data.",|$detail_assign|,$param_updt, QUERY : ".mysqli_real_escape_string($condb, $sql)."', insert_time=now()";
            mysqli_query($condb, $sqlins);
            echo 'Failed! Error ';//.$sql
            // echo 'Failed! Error '.$sql." | ".$sqlsa;//.$sql
                    
          }else{
            $sqlins = "INSERT INTO cc_agent_trail_log SET agent_id = '".$v_agentid."', trail_desc='Failed Data Not Found, ".$index_assign.", ".$tot_assign.", ".$total_data.",|$detail_assign|,$param_updt, QUERY : ".mysqli_real_escape_string($condb, $sql)."', insert_time=now()";
            mysqli_query($condb, $sqlins);
            echo "Failed! Data Not Found";//$sql;
          }
        }
      }

      break;
    case '2':
      $total      = 0; 
      $success    = 0; 
      $failed     = 0; 
      $lengtideb  = explode(",", $iddeb);
      for ($i=1; $i <= count($lengtideb)-1; $i++) {
        $sqlsa = "UPDATE cc_teleupload_data SET 
                  agent_id            = '$agent_to',
                  assign_time         = now(),
                  assign_status       = '0',
                  update_by           = '$v_agentid'
                  where id ='$lengtideb[$i]'"; //echo $sqlsa; 
        if(mysqli_query($condb,$sqlsa)){
          // $sqllog = "INSERT INTO cc_teleupload_data_log (id_cust_detail,  polo_order_in_id,  distributed_date,  source_data,  region_code,  region_name,  cabang_code,  cabang_name,  cabang_coll,  cabang_coll_name,  kapos_name,  agrmnt_no,  order_no,  product,  product_cat,  product_offering_code,  order_no_ro,  customer_id,  customer_name,  nik_ktp,  religion,  tempat_lahir,  tanggal_lahir,  nama_pasangan,  tanggal_lahir_pasangan,  child_name,  child_birthdate,  legal_alamat,  legal_rt,  legal_rw,  legal_provinsi,  legal_kabupaten,  legal_city,  legal_kecamatan,  legal_kelurahan,  legal_kodepos,  legal_sub_kodepos,  survey_alamat,  survey_rt,  survey_rw,  survey_provinsi,  survey_kabupaten,  survey_city,  survey_kecamatan,  survey_kelurahan,  survey_kodepos,  survey_sub_kodepos,  city_id,  gender,  mobile_1,  mobile_2,  phone_1,  phone_2,  office_phone_1,  office_phone_2,  profession_name,  profession_cat_name,  job_position,  industry_type_name,  monthly_income,  monthly_instalment,  plafond,  cust_rating,  suppl_name,  suppl_code,  pekerjaan,  jenis_pekerjaan,  detail_pekerjaan,  oth_biz_name,  hobby,  kepemilikan_rumah,  customer_id_ro,  customer_rating,  nama_dealer,  kode_dealer,  no_mesin,  no_rangka,  asset_type,  asset_category,  asset_desc,  asset_price_amount,  item_id,  item_type,  item_desc,  item_year,  otr_price,  kepemilikan_bpkb,  agrmnt_rating,  status_kontrak,  angsuran_ke,  sisa_tenor,  tenor,  release_date_bpkb,  max_past_due_date,  tanggal_jatuh_tempo,  maturity_date,  os_principal,  product_category,  sisa_piutang,  kilat_pintar,  aging_pembiayaan,  jumlah_kontrak_per_cust,  estimasi_terima_bersih,  cycling,  task_id,  jenis_task,  soa,  down_payment,  ltv,  call_stat,  answer_call,  prospect_stat,  reason_not_prospect,  confirmation,  notes,  sla_remaining,  started_date,  emp_position,  application_id,  application_ia,  dukcapil_stat,  field_person_name,  negative_cust,  notes_new_lead,  visit_dt,  input_dt,  sub_sitrict_kat_code,  contact_no,  source_data_mss,  referantor_code,  referantor_name,  supervisor_name,  note_telesales,  submited_dt,  mss_stat,  wise_stat,  visit_stat,  survey_stat,  flag_void_sla,  eligible_flag,  eligible_flag_dt,  dtm_crt,  usr_crt,  rtm_upd,  usr_upd,  app_no,  application_stat,  bpkb_out,  brand,  city_leg,  city_res,  cust_photo,  dp_pct,  f_card_photo,  ia_app,  id_photo,  jenis_pembiayaan,  monthly_expense,  npwp_no,  order_id,  other_biz_name,  ownership,  pos_dealer,  promotion_activity,  referantor_code_1,  referantor_code_2,  referantor_name_1,  referantor_name_2,  sales_dealer,  send_flag_wise,  spouse_id_photo,  send_flag_mss,  flag_pre_ia,  task_id_mss,  profession_code,  sales_dealer_id,  profession_category_code,  flag_void_sla_tele,  status_task_mss,  priority_level,  outstand_principal,  outstand_monthly_instalment,  rrd_date,  group_id,  sumber_order,  special_cash_flag,  created_by,  modif_by,  insert_time,  modif_time,  bucket_id,  external_code,  priority,  branch_name,  phone,  product_type,  vehicle_year,  plafond_price,  installment_price,  referentor,  desc_note,  desc_note_adv,  assign_by,  agent_id,  assign_time,  reassign,  reassign_by,  reassign_time,  first_call_time,  first_followup_by,  last_call_time,  last_followup_by,  call_status,  call_status_sub1,  call_status_sub2,  total_dial,  total_phone,  total_course,  status,  status_bypass,  status_approve,  close_time,  close_by,  close_approve_time,  close_approve_by,  qa_approve_status,  qa_approve_note,  qa_approve_time,  qa_approve_by)
          //                     SELECT * FROM cc_teleupload_data
          //                     WHERE id='$lengtideb[$i]'";
          // mysqli_query($condb,$sqllog);

          $success++; 
        }else{
          $failed++; 
        }
        $total++;
      }
      if ($success > 0) {
        $detail_assign = "$agent_to : $total ";
        $sqlins = "INSERT INTO cc_agent_trail_log SET agent_id = '".$v_agentid."', trail_desc='Success, ".$success.", ".$total.",|$detail_assign|, where : ".mysqli_real_escape_string($condb, $where)."', insert_time=now()";
        mysqli_query($condb, $sqlins);
        echo 'Success!|'.$success.'|'.$total;
      }
      break;
    default:
      echo "Method Not Found";
      break;
  }
}

function check_value_days2($endMonth) {
  $dayNow = date('d');
    $monNow = date('m');
  $yerNow = date('Y');
  
  if($endMonth == $monNow) {
  $EndDay = date('t',strtotime('Y-m-d'));
    if($dayNow > 1) {
        $dvalue = $dayNow - 1;
    } else {
        $dvalue = $dayNow;
    }
  } else {
      $vmon = $yerNow."-".$endMonth."-".$dayNow;
      $dvalue = date('t',strtotime($vmon));
  } 
    return $dvalue;
}

function validate_number_priority_campaign(){
  $condb = connectDB();
  $number = get_param('number');
  $messages = 'fail';

  $sql = "SELECT COUNT(*) AS tot_prio FROM cc_teleupload_bucket WHERE campaign_priority=$number";
  $res = mysqli_query($condb, $sql);
  if($row = mysqli_fetch_array($res)){
    $tot_prio = $row['tot_prio'];
    if ($tot_prio != 0) {
      $messages = 'protect';
    }else{
      $messages = 'safe';
    }
  }
  mysqli_free_result($res);

  echo json_encode($messages);
}

// by group
function get_select_campaign_by_group($conDB, $id, $name, $required, $bucket_id){
  $v_agentid = get_session('v_agentid');
  $sel = "<SELECT id=\"$id\" name=\"$name\" class=\"select2 form-control\" style=\"width:100%;\" \"$required\">";
  $sel .= "<option value='' selected disabled>--Select Campaign--</option>"; 
  $sql_str1 = "SELECT DISTINCT(a.bucket_id) as id, b.campaign_code, b.campaign_name 
              FROM cc_teleupload_data a LEFT JOIN cc_teleupload_bucket b ON a.bucket_id=b.id
              WHERE (a.agent_id='$v_agentid' OR a.agent_id=0) AND b.status!=0 AND b.spv_id='$v_agentid'";//a.agent_id=69 OR 
  $sql_res1  = execSQL($conDB, $sql_str1);
  while ($sql_rec1 = mysqli_fetch_array($sql_res1)) {
    if($sql_rec1['id'] == $bucket_id) {
      $sel .= "<option value=\"".$sql_rec1['id']."\" selected>".$sql_rec1['campaign_name']."</option>";  
    } else {
      $sel .= "<option value=\"".$sql_rec1['id']."\" >".$sql_rec1['campaign_name']."</option>";  

    }
  }
  $sel .= "</SELECT>";

  return $sel;
}

// by campaign
function get_select_regional(){
  $condb  = connectDB();
    //$condb2 = connectDB2();
  $condb2 = connectDB();
  $bucket_id  = get_param('bucket_id');
  $region       = get_param('regional');
  $v_agentid    = get_session('v_agentid');
  $where = '';
  $bucket_id  != '' ? $where .= ' AND a.bucket_id='.$bucket_id : 0;
  ($region    != '' && $region != '0') ? $where .= ' AND b.region_code IN ('.$region.')' : 0;


  $sel = array();
  $sel[] = "<option value='' disabled>--- Select Regional ---</option>";
  $sel[] = "<option value='0' >All</option>";
  // $sql = "SELECT DISTINCT(a.region) as code FROM cc_teleupload_data a 
  // WHERE 1=1 AND a.spv_id='$v_agentid' $where";
  $sql = "SELECT DISTINCT(a.region) as code FROM cc_master_assign_region a 
  WHERE 1=1 AND a.spv_id='$v_agentid' AND a.is_active=1 $where";
  $res = mysqli_query($condb2, $sql);
  while($row = mysqli_fetch_array($res)){
    $code = $row['code'];
    $name = $row['name'];
    // if ($code == $region) {
      $sel[] = "<option value='$code' selected>$code</option>";
    // }else if($code != '0'){
      // $sel[] = "<option value='$code'>$name</option>";
    // }
  }

  $sel[] = "</SELECT>";

  $data['arrSel'] = $sel;
  $data['sql'] = $sql;

  echo json_encode($data);

}

// by campaign
function get_select_asset_type(){
  $condb  = connectDB();
    //$condb2 = connectDB2();
  $condb2 = connectDB();
  $bucket_id  = get_param('bucket_id');
  $type       = get_param('type');
  $v_agentid    = get_session('v_agentid');
  $where = '';
  $bucket_id  != '' ? $where .= ' AND a.bucket_id='.$bucket_id : 0;
  ($type       != '' || $type != '0') ? $where .= " " : 0;//AND b.asset_type_code IN ($type)


  $sel = array();
  $sel[] = "<option value='' disabled>--- Select Asset Type ---</option>";
  $sel[] = "<option value='0' >All</option>";
  // $sql = "SELECT DISTINCT(a.asset_type) AS code, b.asset_type_name AS name
  //         FROM cc_teleupload_data a
  //         LEFT JOIN cc_master_type_asset b ON a.asset_type=b.asset_type_id
  //         WHERE 1=1 AND a.spv_id='$v_agentid' $where";
  $sql = "SELECT DISTINCT(a.asset_type) AS code, b.asset_type_name AS name
          FROM cc_master_assign_asset a
          LEFT JOIN cc_master_type_asset b ON a.asset_type=b.asset_type_id
          WHERE 1=1 AND a.spv_id='$v_agentid'AND a.is_active=1 $where";
  $res = mysqli_query($condb2, $sql);
  while($row = mysqli_fetch_array($res)){
    $code = $row['code'];
    $name = $row['name'];
    // if ($code == $type) {
      $sel[] = "<option value='$code' selected>$name</option>";
    // }else if($code != '0'){
      // $sel[] = "<option value='$code'>$name</option>";
    // }
  }

  $sel[] = "</SELECT>";

  $data['arrSel'] = $sel;
  $data['sql'] = $sql;

  echo json_encode($data);
}


function get_select_priority(){
  $condb  = connectDB();
    //$condb2 = connectDB2();
  $condb2 = connectDB();
  $bucket_id  = get_param('bucket_id');
  $type       = get_param('type');
  $v_agentid    = get_session('v_agentid');
  $where = '';
  $bucket_id  != '' ? $where .= ' AND a.bucket_id='.$bucket_id : 0;
  ($type       != '' || $type != '0') ? $where .= " " : 0;//AND b.asset_type_code IN ($type)


  $sel = array();
  $sel[] = "<option value='' disabled>--- Select Label Priority ---</option>";
  $sel[] = "<option value='0' >All</option>";
  // $sql = "SELECT DISTINCT(b.id) AS code, b.label_desc AS name
  //         FROM cc_teleupload_data a
  //         LEFT JOIN cc_teleupload_label_priority b ON a.label_priority=b.id
  //         WHERE 1=1 AND b.id IS NOT NULL AND b.label_status=1 AND a.spv_id='$v_agentid' $where";
  $sql = "SELECT DISTINCT(b.id) AS code, b.label_desc AS name
          FROM cc_master_assign_priority a
          LEFT JOIN cc_teleupload_label_priority b ON a.label_priority=b.id
          WHERE 1=1 AND b.id IS NOT NULL AND b.label_status=1 AND a.spv_id='$v_agentid' AND a.is_active=1 $where";
  $res = mysqli_query($condb2, $sql);
  while($row = mysqli_fetch_array($res)){
    $code = $row['code'];
    $name = $row['name'];
    // if ($code == $type) {
      $sel[] = "<option value='$code' selected>$name</option>";
    // }else if($code != '0'){
      // $sel[] = "<option value='$code'>$name</option>";
    // }
  }

  $sel[] = "</SELECT>";

  $data['arrSel'] = $sel;
  $data['sql'] = $sql;

  echo json_encode($data);
}

// by campaign
function get_select_kategori_kendaraan(){
  $condb = connectDB();
  $bucket_id  = get_param('bucket_id');
  $type       = get_param('type');
  $where = '';
  // $bucket_id  != '' ? $where .= ' AND a.bucket_id='.$bucket_id : 0;
  // $type       != '' ? $where .= " AND b.asset_type_code='$type'" : 0;


  $sel = array();
  $sel[] = "<option value='' disabled>--- Select Asset Type ---</option>";
  $sel[] = "<option value='0' selected>All</option>";
  $sql = "SELECT a.id AS code, b.asset_category_name as name FROM cc_teleupload_data a LEFT JOIN  cc_master_type_asset b ON a.asset_type=b.asset_type_code
          WHERE b.is_active=1 AND a.agent_id=0 $where";
  $res = mysqli_query($condb, $sql);
  // while($row = mysqli_fetch_array($res)){
  //   $code = $row['code'];
  //   $name = $row['name'];
  //   if ($code == $type) {
  //     $sel[] = "<option value='$code' selected>$name</option>";
  //   }else{
  //     $sel[] = "<option value='$code'>$name</option>";
  //   }
  // }

  $sel[] = "</SELECT>";

  $data['arrSel'] = $sel;
  $data['sql'] = $sql;

  echo json_encode($data);
}

// by campaign
function get_select_cabang(){
  $condb  = connectDB();
    //$condb2 = connectDB2();
  $condb2 = connectDB();
  $bucket_id       = get_param('bucket_id');
  $regional_param  = get_param('regional_param');
  $v_agentid  = get_session('v_agentid');

  $where = '';
  $bucket_id  != '' ? $where .= ' AND a.bucket_id='.$bucket_id : 0;
  if ($regional_param!="" AND $regional_param!="0") {
    // $where .= ' AND a.bucket_id='.$bucket_id
    $regional_param=str_replace(",", "','", $regional_param);
    // $where .= " AND a.region IN ('$regional_param')";
    $where .= " AND a.region_name IN ('$regional_param')";
  }
  // $type       != '' ? $where .= " AND b.asset_type_code='$type'" : 0;


  $sel = array();
  $sel[] = "<option value='' disabled>--- Select Asset Type ---</option>";
  $sel[] = "<option value='0' >All</option>";
  // $sql = "SELECT DISTINCT(a.cabang) AS code FROM cc_teleupload_data a 
  //         WHERE 1=1 $where";
  // $sql = "SELECT a.cabang AS code FROM cc_teleupload_data a 
  // WHERE 1=1 AND a.spv_id='$v_agentid' $where GROUP BY a.cabang ";
  $sql = "SELECT a.cabang_name AS code FROM cc_master_assign_cabang a 
  WHERE 1=1 AND a.spv_id='$v_agentid' AND a.is_active=1 $where GROUP BY a.cabang_name ";
  $res = mysqli_query($condb2, $sql);
  while($row = mysqli_fetch_array($res)){
    $code = $row['code'];
    $name = $row['name'];
    if ($code == $type) {
      $sel[] = "<option value='$code' selected>$code</option>";
    }else if($code != '0'){
      $sel[] = "<option value='$code'>$code</option>";
    }
  }

  $sel[] = "</SELECT>";

  $data['arrSel'] = $sel;
  $data['sql'] = $sql;

  echo json_encode($data);
}

// by campaign
function get_select_call_status(){
  $condb = connectDB();
  $bucket_id  = get_param('bucket_id');
  $v_agentid  = get_session('v_agentid');

  $where = '';
  $bucket_id  != '' ? $where .= ' AND a.bucket_id='.$bucket_id : 0;
  // $type       != '' ? $where .= " AND b.asset_type_code='$type'" : 0;


  $sel = array();
  $sel[] = "<option value='' selected disabled>--- Select Asset Type ---</option>";
  // $sel[] = "<option value='0' >All</option>";
  $sel[] = "<option value='99' >New</option>";                      
  // $sql = "SELECT DISTINCT(a.last_phonecall) AS code, b.call_status as name
  //         FROM cc_teleupload_data a
  //         LEFT JOIN cc_ts_call_status b ON a.last_phonecall=b.id
  //         WHERE a.last_phonecall>0 AND a.spv_id='$v_agentid' $where";
  $sql = "SELECT DISTINCT(a.last_phonecall) AS code, b.call_status as name
          FROM cc_master_assign_status a
          LEFT JOIN cc_ts_call_status b ON a.last_phonecall=b.id
          WHERE a.last_phonecall>0 AND a.spv_id='$v_agentid' AND a.is_active=1 $where";
  $res = mysqli_query($condb, $sql);
  $list_code = ""; 
  while($row = mysqli_fetch_array($res)){
    $code = $row['code'];
    $name = $row['name'];
    $list_code .= $code.", ";
    if ($code == "") {
      $sel[] = "<option value='Fresh'>Fresh</option>";
    }elseif($code != "0"){
      $sel[] = "<option value='$code'>$name</option>";
    }
  }

  $sel[] = "</SELECT>";

  $data['arrSel'] = $sel;
  $data['sql'] = $sql.' - '.$list_code;

  echo json_encode($data);
}

function get_select_lastcall(){
  $condb       = connectDB();
  $bucket_id   = get_param('bucket_id');
  $status_call = get_param("status_call");

  $where = '';
  $bucket_id  != '' ? $where .= ' AND a.bucket_id='.$bucket_id : 0;
  // $type       != '' ? $where .= " AND b.asset_type_code='$type'" : 0;


  $sel = array();
  $sel[] = "<option value='' disabled>--- Select Lastdate Call ---</option>";
  $sel[] = "<option value='0' >All</option>";
  // old
  // $sql = "SELECT DISTINCT(a.last_followup_time) AS code
  //         FROM cc_teleupload_data a
  //         WHERE a.last_followup_time > '0000-00-00 00:00:00' $where";

  // prod
  // $sql = "SELECT MIN(a.last_followup_time) as mindate, MAX(a.last_followup_time) as maxdate
  //         FROM cc_teleupload_data a
  //         WHERE a.last_followup_time > '0000-00-00 00:00:00' $where";

  // $sql = "SELECT MIN(a.insert_time) as mindate, MAX(a.insert_time) as maxdate 
  //         FROM cc_teleupload_call_session a 
  //         WHERE 1=1 AND a.result=$status_call $where";
  // $res = mysqli_query($condb, $sql);
  // $list_code = ""; 
  // while($row = mysqli_fetch_array($res)){
  //   // $code = $row['code'];
  //   // $name = $row['code'];
  //   // $list_code .= $code.", ";
  //   // if ($code == "") {
  //   //   // $sel[] = "<option value='Fresh'>Fresh</option>";
  //   // }elseif($code != "0"){
  //   //   $sel[] = "<option value='$code'>$name</option>";
  //   // }

  //   $data['mindate'] = $row['mindate'];
  //   $data['maxdate'] = $row['maxdate'];
  // }

  // $sql = "SELECT a.insert_time as mindate 
  //         FROM cc_teleupload_call_session a 
  //         WHERE 1=1 AND a.result=$status_call $where
  //         ORDER BY a.insert_time ASC
  //         LIMIT 1";
  // $res = mysqli_query($condb, $sql);
  // $list_code = ""; 
  // if($row = mysqli_fetch_array($res)){
  //   $data['mindate'] = $row['mindate'];
  // }

  $data['mindate'] = date('Y-m-d', strtotime('-90 days'))." 00:00:00";

  // $sql = "SELECT a.insert_time as maxdate 
  //         FROM cc_teleupload_call_session a 
  //         WHERE 1=1 AND a.result=$status_call $where
  //         ORDER BY a.insert_time DESC
  //         LIMIT 1";
  // $res = mysqli_query($condb, $sql);
  // $list_code = ""; 
  // if($row = mysqli_fetch_array($res)){
  //   $data['maxdate'] = $row['maxdate'];
  // }

  $data['maxdate'] = date('Y-m-d')." 23:59:59";

  $sel[] = "</SELECT>";

  // $data['sql'] = $sql.' - '.$list_code;
  $data['arrSel'] = $sel;
  $data['sql'] = $sql.' - '.$list_code;

  echo json_encode($data);
}

function tele_idno($param, $conDB) {
    $sql = "SELECT SUBSTR(MAX(`task_id`),-7) AS ID  FROM cc_teleupload_data WHERE source_data = 'NEW'";
        $dataMax = mysqli_fetch_assoc(mysqli_query($conDB,$sql)); // ambil data maximal dari id transaksi
     // $param = $param.rand(10,99);
      $param = $param;
        if($dataMax['ID']=='') { // bila data kosong
            $ID = $param."0000001";
        }else {
            $MaksID = $dataMax['ID'];
            $MaksID++;
            if($MaksID < 10) $ID = $param."000000".$MaksID; // nilai kurang dari 10
            else if($MaksID < 100) $ID = $param."00000".$MaksID; // nilai kurang dari 100
            else if($MaksID < 1000) $ID = $param."0000".$MaksID; // nilai kurang dari 1000
            else if($MaksID < 10000) $ID = $param."000".$MaksID; // nilai kurang dari 10000
            else if($MaksID < 100000) $ID = $param."00".$MaksID; // nilai kurang dari 100000
            else if($MaksID < 1000000) $ID = $param."0".$MaksID; // nilai kurang dari 1000000
            else $ID = $MaksID; // lebih dari 10000
        }

        return $ID;
}

function button_upload($priv_generate,$priv_proses,$priv_error){
  $temp  = "";
  if($priv_generate=='1'){
  $temp .= "&nbsp;&nbsp;";
  $temp .= "<button class=\"btn btn-success\" id=\"btnGenerateForm\" value=\"save\">Generate</button>";//<i class=\"fas fa-save\"></i>&nbsp;  
  }
  if($priv_proses=='1'){
  $temp .= "&nbsp;&nbsp;";
  $temp .= "<button class=\"btn btn-warning\" id=\"btnProssesForm\" value=\"cancel\">Prosess</button>";//<i class=\"fas fa-backspace\"></i>&nbsp;
  }
  if($priv_error=='1'){
  $temp .= "&nbsp;&nbsp;";
  $temp .= "<button class=\"btn btn-danger\" id=\"btnErrorForm\" value=\"del\">Download Error</button>";//<i class=\"fas fa-trash-alt\"></i>&nbsp;
  }
  
  return $temp;
  
}


// by group
function get_select_bucket_by_group($conDB, $id, $name, $required, $bucket_id){
  $v_agentid = get_session('v_agentid');
  $sel = "<SELECT id=\"$id\" name=\"$name\" class=\"select2 form-control\" style=\"width:100%;\" \"$required\">";
  $sel .= "<option value='' selected >--Select Bucket--</option>"; 
  // WHERE (a.agent_id='$v_agentid' OR a.agent_id=0) AND b.bucket_status!=0 AND b.spv_id='$v_agentid'";//a.agent_id=69 OR 
  $sql_str1 = "SELECT DISTINCT(a.bucket_id) as id, b.bucket_code, b.bucket_name 
              FROM cc_teleupload_data a LEFT JOIN cc_teleupload_bucket b ON a.bucket_id=b.id
              WHERE b.bucket_status!=0 AND b.spv_id='$v_agentid'";//a.agent_id=69 OR 
  $sql_res1  = execSQL($conDB, $sql_str1);
  while ($sql_rec1 = mysqli_fetch_array($sql_res1)) {
    if($sql_rec1['id'] == $bucket_id) {
      $sel .= "<option value=\"".$sql_rec1['id']."\" selected>".$sql_rec1['bucket_name']."</option>";  
    } else {
      $sel .= "<option value=\"".$sql_rec1['id']."\" >".$sql_rec1['bucket_name']."</option>";  

    }
  }
  $sel .= "</SELECT>";

  return $sel;
}

function assigned_agent(){
  $condb = connectDB();
  $v_agentid = get_session('v_agentid');

  $region               = get_param("region");
  $cabang               = get_param("cabang");
  $kategori_kendaraan   = get_param("kategori_kendaraan");
  $asset_type_kendaraan = get_param("asset_type_kendaraan");
  $status_call          = get_param("status_call");
  $bucket_id          = get_param("bucket");
  $priority_label       = get_param("priority_label");
  $last_call_dt         = get_param("last_call_dt");
  
  $result           = array();
  $where = '';

  $region != 0 ? $where .= " AND a.region_code IN ($region)" : 0;

  if ($region != '') {
    $region=str_replace(",", "','", $region);
    $where .= "AND a.region IN ('$region')";
  }
  
  // $kategori_kendaraan != 0 ? $where .= " AND a.region_code IN ($asset_type_kendaraan)" : 0;

  if ($asset_type_kendaraan != '') {
    $where .= "AND a.asset_type IN ($asset_type_kendaraan)";
  }

  ($cabang != '' OR $cabang != 0) ? $where .= " AND a.cabang IN ($cabang)" : 0;
  // ($priority_label != '') ? $where .= " AND a.label_priority IN ($priority_label)" : 0;
  if($priority_label != '') {
    $where .= " AND a.label_priority IN ($priority_label) ";
  } else{
    $sqlpl = "SELECT DISTINCT(b.id) AS code, b.label_desc AS name
          FROM cc_teleupload_data a
          LEFT JOIN cc_teleupload_label_priority b ON a.label_priority=b.id
          WHERE 1=1 AND a.spv_id=".$v_agentid." AND b.id IS NOT NULL AND a.bucket_id='".$bucket_id."' AND b.label_status=1";
    $respl = mysqli_query($condb, $sqlpl);
    $rowpl = mysqli_fetch_all($respl);
    //id priority label
    $idpl  =  implode(', ', array_map(function ($entry) {
                  return $entry[0];
                }
              , $rowpl ));

    if ($idpl != "") {
      $where .= " AND a.label_priority IN ($idpl) ";
    }
    mysqli_free_result($respl);


  }
  
  if ($status_call == '') {
    $status_call = '0';
  }

  $dateperiod  = get_param("last_call_period");
  $date_from   = substr($dateperiod,0,10);
  $date_to     = substr($dateperiod,12);
                     
  $sqlsel="";
  if($status_call != '0' && $status_call != 'null') {
    if ($status_call == 'Fresh') {
      $where .= " AND a.last_phonecall = '' ";
    }else{
      if ($last_call_dt == "1") {
        // $date_from  .= " 00:00:00";
        // $date_to    .= " 23:59:59";
        $wdateft = "";//where date from to
        if ($date_from != "" && $date_to != "") {
          $date_from  .= " 00:00:00";
          $date_to    .= " 23:59:59";
          $wdateft = "AND a.insert_time >= '".$date_from."' 
                      AND a.insert_time <= '".$date_to."' ";
        }
        if($wdateft == ""){
          $date_default = date("Y-m-d", strtotime("-3 month"));
          $date_from    = $date_default." 00:00:00";
          $date_to      = $date_default." 23:59:59";
            $wdateft = "AND a.insert_time >= '".$date_from."' 
                        AND a.insert_time <= '".$date_to."' ";
        }
        // get data by period
        $sqlsel = "SELECT DISTINCT(a.buck_id) as iddes
                    FROM cc_teleupload_call_session a 
                    WHERE 
                    a.result=$status_call AND 
                    a.bucket_id=$bucket_id 
                    $wdateft";
        $ressel = mysqli_query($condb, $sqlsel);
        // if ($rowsel = mysqli_fetch_array($ressel)) {
        //   $iddes = $rowsel["iddes"];

        //   $where .= " AND a.id IN ($iddes)";
        // }
        $rowsel = mysqli_fetch_all($ressel);
        $iddes =  implode(', ', array_map(function ($entry) {
                      return $entry[0];
                    }
                  , $rowsel ));

        if ($iddes != "") {
          $where .= " AND a.id IN ($iddes)";
        }else{
          $where .= " AND a.id = 0";
        }
      }else{
        $where .= " AND a.last_phonecall IN ($status_call) ";
        $where = str_replace("99", "0", $where); 
      }
    }
  }


  $view = "<option value='' selected>All</option>";
  $view .= "<option value='0'>New</option>";
  $sql = "SELECT DISTINCT(a.agent_id) as agent_id, b.agent_name FROM cc_teleupload_data a LEFT JOIN cc_agent_profile b ON a.agent_id=b.id WHERE a.bucket_id='$bucket_id' AND a.spv_id=$v_agentid $where ORDER BY a.agent_id ASC";//AND (a.agent_id = $v_agentid OR a.agent_id=0)
  $res = mysqli_query($condb, $sql);
  while ($row = mysqli_fetch_array($res)) {
    // $total_data = $row;
    if ($row["agent_id"] != 0) {
      $view .= "<option value='".$row["agent_id"]."'>".$row["agent_name"]."</option>";
    }else{
      // $view .= "<option value='".$row["agent_id"]."'>New</option>";
    }

  }

  $result['view']    = $view;

  $result['asset_type_kendaraan'] = $asset_type_kendaraan;
  $result['sql'] = $sql;
  $result['last_call_dt'] = $last_call_dt;
  $result['last_phonecall'] = $status_call;

  echo json_encode($result);
}
?>