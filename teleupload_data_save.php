<?php
include "../../sysconf/global_func.php";
include "../../sysconf/session.php";
include "../../sysconf/db_config.php";
require_once '../../library/excel/simplexlsx.class.php';

function parserer($word){
  $word = addslashes($word);
  $word = str_replace("`", "", $word);

  return $word;
}

function convertdob($dobdate) {
  $unixdate = ($dobdate - 25569) * 86400;
  $dobdate = 25569 + ($unixdate / 86400);
  $unixdate = ($dobdate - 25569) * 86400;
  $vdob = gmdate("Y-m-d", $unixdate);

  return $vdob;
}

$condb = connectDB();

$v_agentid      = get_session("v_agentid");
$v_agentlevel   = get_session("v_agentlevel");
$v              = get_param("v");
$fblist         = get_param("fblist");

$bucket_id = mysqli_real_escape_string($condb,get_param("bucket_id"));
$assignOpsi = mysqli_real_escape_string($condb,get_param("assignOpsi"));
$date_dist = mysqli_real_escape_string($condb,get_param("date_dist"));
$time_dist = mysqli_real_escape_string($condb,get_param("time_dist"));
$reassigntime = $date_dist." ".$time_dist;
// $totcheck = mysqli_real_escape_string($condb,get_param("totcheck"));

$checkid = get_param("fomni_id");
$totcheck = (count($checkid)-1);

// echo "totcheck : ".$totcheck;

$abandon = mysqli_real_escape_string($condb,get_param("abandon"));
$answered = mysqli_real_escape_string($condb,get_param("answered"));
$idsla = mysqli_real_escape_string($condb,get_param("idsla"));
$idcal = mysqli_real_escape_string($condb,get_param("idcal"));
$sl_target = mysqli_real_escape_string($condb,get_param("sl_target"));

function real_escape($condb, $value) {
  $data = mysqli_real_escape_string($condb, $value);
  return $data;
}
// function unixstamp( $excelDateTime ) {
//     $d = floor( $excelDateTime ); // seconds since 1900
//     $t = $excelDateTime - $d;
//     return ($d > 0) ? ( $d - 25569 ) * 86400 + $t * 86400 : $t * 86400;
// }
// print_r($checkid);
if ($v=="prosses") {
  $sql  = "SELECT * FROM cc_teleupload_data_det a
            WHERE update_by='$v_agentid' AND flag_temp=0 AND flag_status!=2";//echo "string $sql";
  // $res  = mysqli_query($condb, $sql);echo "string $sql";
  if($res  = mysqli_query($condb, $sql)) {
    while($rec = mysqli_fetch_array($res)) {
       $id        = $rec["id"];//echo "string $sql | $label_id";
       $bucket_id = $rec["bucket_id"];
       $region = $rec["region"];
       $kapos_name = $rec["kapos_name"];
       $order_no = $rec["order_no"];
       $cabang = $rec["cabang"];
       $no_rangka               = $rec["no_rangka"];
       $customer_id             = $rec["customer_id"];
       $customer_name           = $rec["customer_name"];
       $item_description        = $rec["item_description"];
       $mobile1                 = $rec["mobile1"];
       $mobile2                 = $rec["mobile2"];
       $phone1                  = $rec["phone1"];
       $office_phone1           = $rec["office_phone1"];
       $otr_price               = $rec["otr_price"];
       $item_year               = $rec["item_year"];
       $monthly_income          = $rec["monthly_income"];
       $monthly_instalment      = $rec["monthly_instalment"];
       $address_cust            = $rec["address_cust"];
       $kecamatan               = $rec["kecamatan"];
       $kelurahan               = $rec["kelurahan"];
       $kode_kat                = $rec["kode_kat"];
       $tenor_id                = $rec["tenor_id"];
       $max_past_due_dt         = $rec["max_past_due_dt"];
       $religion                = $rec["religion"];
       $cust_rating             = $rec["cust_rating"];
       $agrmnt_rating           = $rec["agrmnt_rating"];
       $status_kontrak          = $rec["status_kontrak"];
       $tanggal_jatuh_tempo     = $rec["tanggal_jatuh_tempo"];
       $tgl_lahir_konsumen      = $rec["tgl_lahir_konsumen"];
       $tgl_lahir_pasangan      = $rec["tgl_lahir_pasangan"];
       $gender_konsumen         = $rec["gender_konsumen"];
       $kepemilikan_rumah       = $rec["kepemilikan_rumah"];
       $kepemilikan_bpkb        = $rec["kepemilikan_bpkb"];
       $asset_type              = $rec["asset_type"];
       $asset_temp              = $rec["asset_temp"];
       $label_priority_temp     = $rec["label_priority_temp"];
       $ketegori_product        = $rec["ketegori_product"];
       $sisa_piutang            = $rec["sisa_piutang"];
       $sisa_tenor              = $rec["sisa_tenor"];
       $profession_name         = $rec["profession_name"];
       $profession_category_name = $rec["profession_category_name"];
       $job_position            = $rec["job_position"];
       $industry_type_name      = $rec["industry_type_name"];
       $jumlah_kontrak_perasset = $rec["jumlah_kontrak_perasset"];
       $estimasi_terima_bersih  = $rec["estimasi_terima_bersih"];
       $label_priority      = $rec["label_priority"];
       $spv_id              = $rec["spv_id"];
       $agent_id            = $rec["agent_id"];
       $assign_time         = $rec["assign_time"];
       $last_phoneno        = $rec["last_phoneno"];
       $last_phonecall      = $rec["last_phonecall"];
       $last_phonecall_sub  = $rec["last_phonecall_sub"];
       $last_followup_by    = $rec["last_followup_by"];
       $last_followup_date  = $rec["last_followup_date"];
       $last_followup_time  = $rec["last_followup_time"];
       $remark_desc         = $rec["remark_desc"];
       $create_by           = $rec["create_by"];
       $create_time         = $rec["create_time"];
       $flag_status = $rec["flag_status"];
       if ($flag_status==3) {
         //data UPDATE
          $sqlv = "SELECT * FROM cc_teleupload_data a 
              where a.order_no='$order_no'";//echo "## $sqlv";
          $resv = mysqli_query($condb,$sqlv);
          if($recv = mysqli_fetch_array($resv)){
            $create_time      = $recv['create_time'];
          }
       }


       $spv_idsa =0;
       $sqlvuc = "SELECT spv_id FROM cc_teleupload_bucket WHERE id='$bucket_id'";
       $ressvu = mysqli_query($condb,$sqlvuc);
       if($recvu = mysqli_fetch_array($ressvu)){
         $spv_idsa = $recvu['spv_id']; 
       }
       

       $sqlin  = "INSERT INTO cc_teleupload_data SET
           bucket_id               ='".real_escape($condb, $bucket_id)."',
           region                  ='".real_escape($condb, $region)."',
           kapos_name              ='".real_escape($condb, $kapos_name)."',
           order_no                ='".real_escape($condb, $order_no)."',
           cabang                  ='".real_escape($condb, $cabang)."',
           no_rangka               ='".real_escape($condb, $no_rangka)."',
           customer_id             ='".real_escape($condb, $customer_id)."',
           customer_name           ='".real_escape($condb, $customer_name)."',
           item_description        ='".real_escape($condb, $item_description)."',
           mobile1                 ='".real_escape($condb, $mobile1)."',
           mobile2                 ='".real_escape($condb, $mobile2)."',
           phone1                  ='".real_escape($condb, $phone1)."',
           office_phone1           ='".real_escape($condb, $office_phone1)."',
           otr_price               ='".real_escape($condb, $otr_price)."',
           item_year               ='".real_escape($condb, $item_year)."',
           monthly_income          ='".real_escape($condb, $monthly_income)."',
           monthly_instalment      ='".real_escape($condb, $monthly_instalment)."',
           address_cust            ='".real_escape($condb, $address_cust)."',
           kecamatan               ='".real_escape($condb, $kecamatan)."',
           kelurahan               ='".real_escape($condb, $kelurahan)."',
           kode_kat                ='".real_escape($condb, $kode_kat)."',
           tenor_id                ='".real_escape($condb, $tenor_id)."',
           max_past_due_dt         ='".real_escape($condb, $max_past_due_dt)."',
           religion                ='".real_escape($condb, $religion)."',
           cust_rating             ='".real_escape($condb, $cust_rating)."',
           agrmnt_rating           ='".real_escape($condb, $agrmnt_rating)."',
           status_kontrak          ='".real_escape($condb, $status_kontrak)."',
           tanggal_jatuh_tempo     ='".real_escape($condb, $tanggal_jatuh_tempo)."',
           tgl_lahir_konsumen      ='".real_escape($condb, $tgl_lahir_konsumen)."',
           tgl_lahir_pasangan      ='".real_escape($condb, $tgl_lahir_pasangan)."',
           gender_konsumen         ='".real_escape($condb, $gender_konsumen)."',
           kepemilikan_rumah       ='".real_escape($condb, $kepemilikan_rumah)."',
           kepemilikan_bpkb        ='".real_escape($condb, $kepemilikan_bpkb)."',
           asset_type              ='".real_escape($condb, $asset_type)."',
           asset_temp              ='".real_escape($condb, $asset_temp)."',
           label_priority_temp     ='".real_escape($condb, $label_priority_temp)."',
           ketegori_product        ='".real_escape($condb, $ketegori_product)."',
           sisa_piutang            ='".real_escape($condb, $sisa_piutang)."',
           sisa_tenor              ='".real_escape($condb, $sisa_tenor)."',
           profession_name         ='".real_escape($condb, $profession_name)."',
           profession_category_name ='".real_escape($condb, $profession_category_name)."',
           job_position            ='".real_escape($condb, $job_position)."',
           industry_type_name      ='".real_escape($condb, $industry_type_name)."',
           jumlah_kontrak_perasset ='".real_escape($condb, $jumlah_kontrak_perasset)."',
           estimasi_terima_bersih  ='".real_escape($condb, $estimasi_terima_bersih)."',
           label_priority      ='".real_escape($condb, $label_priority)."',
           spv_id              ='".real_escape($condb, $spv_idsa)."',
           agent_id            ='".real_escape($condb, $agent_id)."',
           assign_time         ='".real_escape($condb, $assign_time)."',
           last_phoneno        ='".real_escape($condb, $last_phoneno)."',
           last_phonecall      ='".real_escape($condb, $last_phonecall)."',
           last_phonecall_sub  ='".real_escape($condb, $last_phonecall_sub)."',
           last_followup_by    ='".real_escape($condb, $last_followup_by)."',
           last_followup_date  ='".real_escape($condb, $last_followup_date)."',
           last_followup_time  ='".real_escape($condb, $last_followup_time)."',
           remark_desc         ='".real_escape($condb, $remark_desc)."',
           create_by           ='".real_escape($condb, $create_by)."',
           create_time         ='".real_escape($condb, $create_time)."',
           update_by           ='".real_escape($condb, $v_agentid)."',
           update_time         = now()
      ON DUPLICATE KEY UPDATE
           bucket_id               ='".real_escape($condb, $bucket_id)."',
           region                  ='".real_escape($condb, $region)."',
           kapos_name              ='".real_escape($condb, $kapos_name)."',
           order_no                ='".real_escape($condb, $order_no)."',
           cabang                  ='".real_escape($condb, $cabang)."',
           no_rangka               ='".real_escape($condb, $no_rangka)."',
           customer_id             ='".real_escape($condb, $customer_id)."',
           customer_name           ='".real_escape($condb, $customer_name)."',
           item_description        ='".real_escape($condb, $item_description)."',
           mobile1                 ='".real_escape($condb, $mobile1)."',
           mobile2                 ='".real_escape($condb, $mobile2)."',
           phone1                  ='".real_escape($condb, $phone1)."',
           office_phone1           ='".real_escape($condb, $office_phone1)."',
           otr_price               ='".real_escape($condb, $otr_price)."',
           item_year               ='".real_escape($condb, $item_year)."',
           monthly_income          ='".real_escape($condb, $monthly_income)."',
           monthly_instalment      ='".real_escape($condb, $monthly_instalment)."',
           address_cust            ='".real_escape($condb, $address_cust)."',
           kecamatan               ='".real_escape($condb, $kecamatan)."',
           kelurahan               ='".real_escape($condb, $kelurahan)."',
           kode_kat                ='".real_escape($condb, $kode_kat)."',
           tenor_id                ='".real_escape($condb, $tenor_id)."',
           max_past_due_dt         ='".real_escape($condb, $max_past_due_dt)."',
           religion                ='".real_escape($condb, $religion)."',
           cust_rating             ='".real_escape($condb, $cust_rating)."',
           agrmnt_rating           ='".real_escape($condb, $agrmnt_rating)."',
           status_kontrak          ='".real_escape($condb, $status_kontrak)."',
           tanggal_jatuh_tempo     ='".real_escape($condb, $tanggal_jatuh_tempo)."',
           tgl_lahir_konsumen      ='".real_escape($condb, $tgl_lahir_konsumen)."',
           tgl_lahir_pasangan      ='".real_escape($condb, $tgl_lahir_pasangan)."',
           gender_konsumen         ='".real_escape($condb, $gender_konsumen)."',
           kepemilikan_rumah       ='".real_escape($condb, $kepemilikan_rumah)."',
           kepemilikan_bpkb        ='".real_escape($condb, $kepemilikan_bpkb)."',
           asset_type              ='".real_escape($condb, $asset_type)."',
           asset_temp              ='".real_escape($condb, $asset_temp)."',
           label_priority_temp     ='".real_escape($condb, $label_priority_temp)."',
           ketegori_product        ='".real_escape($condb, $ketegori_product)."',
           sisa_piutang            ='".real_escape($condb, $sisa_piutang)."',
           sisa_tenor              ='".real_escape($condb, $sisa_tenor)."',
           profession_name         ='".real_escape($condb, $profession_name)."',
           profession_category_name ='".real_escape($condb, $profession_category_name)."',
           job_position            ='".real_escape($condb, $job_position)."',
           industry_type_name      ='".real_escape($condb, $industry_type_name)."',
           jumlah_kontrak_perasset ='".real_escape($condb, $jumlah_kontrak_perasset)."',
           estimasi_terima_bersih  ='".real_escape($condb, $estimasi_terima_bersih)."',
           label_priority      ='".real_escape($condb, $label_priority)."',
           spv_id              ='".real_escape($condb, $spv_idsa)."',
           agent_id            ='".real_escape($condb, $agent_id)."',
           assign_time         ='".real_escape($condb, $assign_time)."',
           last_phoneno        ='".real_escape($condb, $last_phoneno)."',
           last_phonecall      ='".real_escape($condb, $last_phonecall)."',
           last_phonecall_sub  ='".real_escape($condb, $last_phonecall_sub)."',
           last_followup_by    ='".real_escape($condb, $last_followup_by)."',
           last_followup_date  ='".real_escape($condb, $last_followup_date)."',
           last_followup_time  ='".real_escape($condb, $last_followup_time)."',
           remark_desc         ='".real_escape($condb, $remark_desc)."',
           create_by           ='".real_escape($condb, $create_by)."',
           create_time         ='".real_escape($condb, $create_time)."',
           update_by           ='".real_escape($condb, $v_agentid)."',
           update_time         = now()";
       //echo "</br>string2 $sqlin";
       if($resin  = mysqli_query($condb, $sqlin)) {//echo "string2";
         $sqlupd = "UPDATE cc_teleupload_data_det SET 
                   flag_temp     ='1', 
                   update_by     ='$v_agentid',
                   update_time   =now()
                   WHERE id=$id";//echo "string $sqlupd </br></br>";
         mysqli_query($condb,$sqlupd);  
         $param=1;

         //mstr region
         $sqlmstr = "INSERT INTO cc_master_assign_region SET 
                      bucket_id        ='$bucket_id',
                      spv_id           ='$spv_idsa',    
                      region           ='$region',
                      is_active        ='1',
                      modif_by         ='$v_agentid',
                      modif_time       =now()
                    ON DUPLICATE KEY UPDATE 
                      bucket_id        ='$bucket_id',
                      spv_id           ='$spv_idsa',    
                      region           ='$region',
                      is_active        ='1',
                      modif_by         ='$v_agentid',
                      modif_time       =now() ";
          mysqli_query($condb,$sqlmstr);  

         //mstr cabang
         $sqlmstr = "INSERT INTO cc_master_assign_cabang SET 
                      bucket_id        ='$bucket_id',
                      spv_id           ='$spv_idsa',    
                      region_name      ='$region',  
                      cabang_name      ='$cabang',
                      is_active        ='1',
                      modif_by         ='$v_agentid',
                      modif_time       =now()
                    ON DUPLICATE KEY UPDATE 
                      bucket_id        ='$bucket_id',
                      spv_id           ='$spv_idsa',    
                      region_name      ='$region',  
                      cabang_name      ='$cabang',
                      is_active        ='1',
                      modif_by         ='$v_agentid',
                      modif_time       =now() ";
          mysqli_query($condb,$sqlmstr);     

         //mstr priority
         $sqlmstr = "INSERT INTO cc_master_assign_priority SET 
                      bucket_id        ='$bucket_id',
                      spv_id           ='$spv_idsa',    
                      label_priority   ='$label_priority', 
                      is_active        ='1',
                      modif_by         ='$v_agentid',
                      modif_time       =now()
                    ON DUPLICATE KEY UPDATE 
                      bucket_id        ='$bucket_id',
                      spv_id           ='$spv_idsa',    
                      label_priority   ='$label_priority', 
                      is_active        ='1',
                      modif_by         ='$v_agentid',
                      modif_time       =now() ";
          mysqli_query($condb,$sqlmstr);       

         //mstr asset
         $sqlmstr = "INSERT INTO cc_master_assign_asset SET 
                      bucket_id        ='$bucket_id',
                      spv_id           ='$spv_idsa',    
                      asset_type       ='$asset_type', 
                      is_active        ='1',
                      modif_by         ='$v_agentid',
                      modif_time       =now()
                    ON DUPLICATE KEY UPDATE 
                      bucket_id        ='$bucket_id',
                      spv_id           ='$spv_idsa',    
                      asset_type       ='$asset_type', 
                      is_active        ='1',
                      modif_by         ='$v_agentid',
                      modif_time       =now()";
          mysqli_query($condb,$sqlmstr);      

         //mstr status
         $sqlmstr = "INSERT INTO cc_master_assign_status SET 
                      bucket_id        ='$bucket_id',
                      spv_id           ='$spv_idsa',    
                      last_phonecall       ='$last_phonecall', 
                      is_active        ='1',
                      modif_by         ='$v_agentid',
                      modif_time       =now()
                    ON DUPLICATE KEY UPDATE 
                      bucket_id        ='$bucket_id',
                      spv_id           ='$spv_idsa',    
                      last_phonecall       ='$last_phonecall', 
                      is_active        ='1',
                      modif_by         ='$v_agentid',
                      modif_time       =now()";
          mysqli_query($condb,$sqlmstr);          
       }
        
    }
    
  }else{
    echo "Data Sudah Diprosses";
  }
if ($param==1) {
    echo "Success"; 
    }
}else{
  $target = basename($_FILES['fileName']['name'][0]);
// echo "string $target";
    if(!empty($target)) {
      //echo "ada file $target";
      //echo "Assignt to".$assignto2." karena ".$assignOpsi;
      
      $generate = date('Y').date('m').date('d').date('His')."|";
      $newfile = $generate.$target;
      $move = move_uploaded_file($_FILES['fileName']['tmp_name'][0], "../../public/telecollection/".$newfile);
      // echo "## $move";
      if ($move == 1) {
        echo "Success Upload File"; 
      } else {
        echo "Error! Can't Upload File"; 
      }
      /**if ($move == 1) {
        
            $success = 0;
            $duplicate = 0;
            $Failed  = 0;
            $note    = "";
            $nomorUrut = 1;
             if ($xlsx = SimpleXLSX::parse("../../public/telecollection/".$newfile)) { 

                  list($cols,) = $xlsx->dimension();
                  
                  $mulaidari = 0;
                  $param=0;
                  $sqlv = "TRUNCATE cc_teleupload_data_det";//echo "## $sqlv";
                  $resv = mysqli_query($condb,$sqlv);
                  foreach ( $xlsx->rows() as $k => $r ) {
                  if ($r[0] != "") {
                      // if ($k == 0) continue;
                    if ($k == 0) continue;
                          $lanjut = 'ok';
                          $nomorUrut++;
                          
                          $region = $r[0];
                          $kapos_name = $r[1];
                          $order_no = $r[2];
                          $param_agrmnt_no = strlen($order_no);
                          $flag_status=1;
                          $error_desc="";
                          if ($param_agrmnt_no > 11 && $param_agrmnt_no < 17) {
                            $cek  = is_numeric($order_no);
                            if ($cek == true) { 
                              
                            }
                            else {
                              $flag_status=2;
                              $error_desc="param_agrmnt_no bukan numeric";
                            }
                          }else{
                              $flag_status=2;
                              if ($error_desc!="") {
                                $error_desc.=";param_agrmnt_no tidak sesuai";
                              }else{
                                $error_desc="param_agrmnt_no tidak sesuai";  
                              }
                              
                          }
                          $cabang = $r[3];
                          $no_rangka = $r[4];
                          $customer_id = $r[5];
                          $customer_name = $r[6];
                          $item_description = $r[7];
                          $mobile1 = $r[8];
                          $mobile2 = $r[9];
                          $phone1 = $r[10];
                          $office_phone1 = $r[11];
                          $otr_price = $r[12];
                          $item_year = $r[13];
                          $monthly_income = $r[14];
                          $monthly_instalment = $r[15];
                          $address_cust = $r[16];
                          $kecamatan = $r[17];
                          $kelurahan = $r[18];
                          $kode_kat = $r[19];
                          $tenor_id = $r[20];
                          $max_past_due_dt = $r[21];
                          $religion = $r[22];
                          $cust_rating = $r[23];
                          $agrmnt_rating = $r[24];
                          $status_kontrak = $r[25];
                          $tanggal_jatuh_tempo = $r[26];
                          $tgl_lahir_konsumen = $r[27];
                          $tgl_lahir_pasangan = $r[28];
                          $gender_konsumen = $r[29];
                          $kepemilikan_rumah = $r[30];
                          $kepemilikan_bpkb = $r[31];
                          $asset_type = $r[32];
                          $ketegori_product = $r[33];
                          $sisa_piutang = $r[34];
                          $sisa_tenor = $r[35];
                          $profession_name = $r[36];
                          $profession_category_name = $r[37];
                          $job_position = $r[38];
                          $industry_type_name = $r[39];
                          $jumlah_kontrak_perasset = $r[40];
                          $estimasi_terima_bersih = $r[41];
                          $label_priority = $r[42];


                          

                          $label_id = 0;
                          $sql  = " SELECT * FROM cc_teleupload_label_priority a
                                    WHERE a.label_desc LIKE '".$label_priority."%' ";
                          $res  = mysqli_query($condb, $sql);
                          if($rec = mysqli_fetch_array($res)) {
                            $label_id = $rec["id"];
                          }
                          if ($label_id==0||$label_id=="") {
                            $flag_status=2;
                            // $error_desc="param_label_priority tidak sesuai";
                            if ($error_desc!="") {
                              $error_desc.=";param_label_priority tidak sesuai";
                            }else{
                              $error_desc="param_label_priority tidak sesuai";  
                             }
                          }

                          $flag_upload=0;
                          $sql  = " SELECT a.flag_upload FROM cc_teleupload_data_det a
                                    WHERE a.update_by=3
                                    ORDER BY a.flag_upload DESC LIMIT 1 ";
                          $res  = mysqli_query($condb, $sql);
                          if($rec = mysqli_fetch_array($res)) {
                            $flag_upload = $rec["flag_upload"];
                          }

                          $flag_upload=$flag_upload+1;

                          $order_no_data="";
                          if ($flag_status!=2) {
                            $sql  = " SELECT a.order_no FROM cc_teleupload_data a
                                      WHERE a.order_no='$order_no'";
                            $res  = mysqli_query($condb, $sql);
                            if($rec = mysqli_fetch_array($res)) {
                              $order_no_data = $rec["order_no"];
                            }
                            if ($order_no_data!="") {
                              $flag_status=3;
                            }
                          }

                          $id_prod = $ketegori_product;
                          // $sql  = " SELECT * FROM cc_teleupload_kategori_prod a
                          //           WHERE a.kategoryprod_desc LIKE '%$ketegori_product%' ";
                          // $res  = mysqli_query($condb, $sql);
                          // if($rec = mysqli_fetch_array($res)) {
                          //   $id_prod = $rec["id"];
                          // }


                          $sql  = " SELECT * FROM cc_master_type_asset a
                                    WHERE a.asset_type_code LIKE '%$asset_type%' ";
                          $res  = mysqli_query($condb, $sql);
                          if($rec = mysqli_fetch_array($res)) {
                            $asset_type_id = $rec["asset_type_id"];
                          }

                            $query = " INSERT INTO cc_teleupload_data_det SET
                                          bucket_id                   = '".real_escape($condb, $bucket_id)."',
                                          region                      = '".real_escape($condb, $region)."',
                                          kapos_name                  = '".real_escape($condb, $kapos_name)."',
                                          order_no                    = '".real_escape($condb, $order_no)."',
                                          cabang                      = '".real_escape($condb, $cabang)."',
                                          no_rangka                   = '".real_escape($condb, $no_rangka)."',
                                          customer_id                 = '".real_escape($condb, $customer_id)."',
                                          customer_name               = '".real_escape($condb, $customer_name)."',
                                          item_description            = '".real_escape($condb, $item_description)."',
                                          mobile1                     = '".real_escape($condb, $mobile1)."',
                                          mobile2                     = '".real_escape($condb, $mobile2)."',
                                          phone1                      = '".real_escape($condb, $phone1)."',
                                          office_phone1               = '".real_escape($condb, $office_phone1)."',
                                          otr_price                   = '".real_escape($condb, $otr_price)."',
                                          item_year                   = '".real_escape($condb, $item_year)."',
                                          monthly_income              = '".real_escape($condb, $monthly_income)."',
                                          monthly_instalment          = '".real_escape($condb, $monthly_instalment)."',
                                          address_cust                = '".real_escape($condb, $address_cust)."',
                                          kecamatan                   = '".real_escape($condb, $kecamatan)."',
                                          kelurahan                   = '".real_escape($condb, $kelurahan)."',
                                          kode_kat                    = '".real_escape($condb, $kode_kat)."',
                                          tenor_id                    = '".real_escape($condb, $tenor_id)."',
                                          max_past_due_dt             = '".real_escape($condb, $max_past_due_dt)."',
                                          religion                    = '".real_escape($condb, $religion)."',
                                          cust_rating                 = '".real_escape($condb, $cust_rating)."',
                                          agrmnt_rating               = '".real_escape($condb, $agrmnt_rating)."',
                                          status_kontrak              = '".real_escape($condb, $status_kontrak)."',
                                          tanggal_jatuh_tempo         = '".real_escape($condb, $tanggal_jatuh_tempo)."',
                                          tgl_lahir_konsumen          = '".real_escape($condb, $tgl_lahir_konsumen)."',
                                          tgl_lahir_pasangan          = '".real_escape($condb, $tgl_lahir_pasangan)."',
                                          gender_konsumen             = '".real_escape($condb, $gender_konsumen)."',
                                          kepemilikan_rumah           = '".real_escape($condb, $kepemilikan_rumah)."',
                                          kepemilikan_bpkb            = '".real_escape($condb, $kepemilikan_bpkb)."',
                                          asset_type                  = '".real_escape($condb, $asset_type_id)."',
                                          ketegori_product            = '".real_escape($condb, $id_prod)."',
                                          sisa_piutang                = '".real_escape($condb, $sisa_piutang)."',
                                          sisa_tenor                  = '".real_escape($condb, $sisa_tenor)."',
                                          profession_name             = '".real_escape($condb, $profession_name)."',
                                          profession_category_name    = '".real_escape($condb, $profession_category_name)."',
                                          job_position                = '".real_escape($condb, $job_position)."',
                                          industry_type_name          = '".real_escape($condb, $industry_type_name)."',
                                          jumlah_kontrak_perasset     = '".real_escape($condb, $jumlah_kontrak_perasset)."',
                                          estimasi_terima_bersih      = '".real_escape($condb, $estimasi_terima_bersih)."',
                                          label_priority              = '".real_escape($condb, $label_id)."',
                                          error_desc                  = '".real_escape($condb, $error_desc)."',
                                          flag_temp                   = '".real_escape($condb, 0)."',
                                          flag_status                 = '".real_escape($condb, $flag_status)."',
                                          flag_upload                 = '".real_escape($condb, $flag_upload)."',
                                          create_by                   = '".real_escape($condb, $v_agentid)."',
                                          create_time                 = now(),
                                          update_by                   = '".real_escape($condb, $v_agentid)."',
                                          update_time                 = now()"; //echo $query."</br>";
                            if(mysqli_query($condb, $query)){
                              $custid = mysqli_insert_id($condb);
                              // echo "Success"; 
                              $param = "1";
                            } else {
                              $lanjut = 'nok';
                                if ($note != "") {
                                   $note .= ",";
                                }
                                $note    .= " $nomorUrut";
                                $Failed++;
                            }
                          //}xxx

                      }
                  }
                  if ($param==1) {
                    echo "Success";
                  }
                  // if ($note != '') {
                  //   $note = "Fail Description : \n Fail to save your Excel at row :".$note."\n\nYou may check your Excel Upload File again";
                  // }
                  // if ($duplicate!='0') {
                  //       $duplicatexx = "\n Data Duplicate : $duplicate";
                  // }
                  // if($success==0 && $Failed==0){
                  //   echo "Data Blank";
                  // }else{
                  //   echo "Resault Summary\n\nData Uploaded : $success \n Data Discard : $Failed $duplicatexx ";
                  // }
                  
                  // $reason_log = "cc_sms_blast ".trial_log_add($condb, $iddet);
                  // $traildesc = "Insert $reason_log Success";
                  //         $traildesc = mysqli_real_escape_string($condb,$traildesc);
                  // $sqlci = "INSERT INTO cc_agent_trail_log SET
                  //                         agent_id    ='$agentid',
                  //                         trail_desc  ='$traildesc',
                  //                         insert_time =now()";
                  //         mysqli_query($condb,$sqlci);
              } else {
                  echo SimpleXLSX::parse_error();
                  //echo "Error: Can't Parse Data!";
              }
              
      }else{

       echo "Error! Can't Upload File"; 
      }**/
    }else{
      echo "Error! File Not Selected";
    }
}

?>