<?php
include "../../sysconf/global_func.php";
include "../../sysconf/session.php";
include "../../sysconf/db_config.php";
include "global_func_teleupload.php";


$condb = connectDB();
$v_agentid      = get_session("v_agentid");
$v_agentlevel   = get_session("v_agentlevel");
$v_agentgroup   = get_session("v_agentgroup");

$iddet      = $library['iddet'];

$ffolder    = $library['folder'];
$fmenu_link   = $library['menu_link'];
$menu_label   = $library['title'];
$fdescription = $library['description'];
$fmenu_id   = $library['menu_id'];
$ficon      = $library['icon'];
$fiddet     = $library['iddet'];
$fblist     = $library['blist'];



$fmenu_link_back = "cust_account_list";
$menu_linkdet = "cust_contact_det";
      
$blist      = $library['blist'];
$strblist       = explode(";", $blist); 
$blist_date   = $strblist[0];
$blist_fcount = $strblist[1];
$blist_csearch0 = $strblist[2];
$blist_tsearch0 = $strblist[3];
$blist_csearch1 = $strblist[4];
$blist_tsearch1 = $strblist[5];
$blist_csearch2 = $strblist[6];
$blist_tsearch2 = $strblist[7];
$blist_csearch3 = $strblist[8];
$blist_tsearch3 = $strblist[9];
$blist_csearch4 = $strblist[10];
$blist_tsearch4 = $strblist[11];


//file save data
$save_form = "view/teleupload/global_func_teleupload.php?f=assign_by_contract";
//data server side
$link_data_acc= "view/teleupload/global_func_teleupload.php?f=get_datatable_bucket"; 
$get_data = "view/teleupload/get_data_value.php";


if($iddet  == "") {
  $desc_iddet = "Detail";
}else{
  $desc_iddet = "View";
}

// $desc_iddet = "Assignment Agent";


$varassignto = "<select id=\"assignto\" name=\"assignto\" class=\"select2 form-control js-example-basic-multiple\" multiple=\"multiple\" >";
$sql_str1 = "SELECT id, agent_id, agent_name FROM cc_agent_profile WHERE agent_level IN(1) ORDER BY agent_name";
     $sql_res1 = mysqli_query($condb,$sql_str1);
     while($sql_rec1 = mysqli_fetch_array($sql_res1)) {
     $varassignto .= "<option value=\"".$sql_rec1["id"]."\">[".$sql_rec1["agent_id"]."] ".$sql_rec1["agent_name"]."</option>";
     }                                                                                          
$varassignto .= "</select>";
?>
<style type="text/css">
.dataTables_filter {
  /*display: none; */
}

.select2-search{
  width:100%!important;
}
.select2-search__field{
  width:100%!important;
}

.datatablelist_wrapper {
  /*position: inherit; relative*/
}
</style>
<form name="frmDataDet" id="frmDataDet" method="POST">
<input type="hidden" name="iddet" id="iddet" value="<?php echo $iddet;?>">

<input type="hidden" name="blist_date" id="blist_date" value="<?php echo $blist_date;?>">
<input type="hidden" name="blist_fcount" id="blist_fcount" value="<?php echo $blist_fcount;?>">
<input type="hidden" name="blist_csearch0" id="blist_csearch0" value="<?php echo $blist_csearch0;?>">
<input type="hidden" name="blist_tsearch0" id="blist_tsearch0" value="<?php echo $blist_tsearch0;?>">
<input type="hidden" name="blist_csearch1" id="blist_csearch1" value="<?php echo $blist_csearch1;?>">
<input type="hidden" name="blist_tsearch1" id="blist_tsearch1" value="<?php echo $blist_tsearch1;?>">
<input type="hidden" name="blist_csearch2" id="blist_csearch2" value="<?php echo $blist_csearch2;?>">
<input type="hidden" name="blist_tsearch2" id="blist_tsearch2" value="<?php echo $blist_tsearch2;?>">
<input type="hidden" name="blist_csearch3" id="blist_csearch3" value="<?php echo $blist_csearch3;?>">
<input type="hidden" name="blist_tsearch3" id="blist_tsearch3" value="<?php echo $blist_tsearch3;?>">
<input type="hidden" name="blist_csearch4" id="blist_csearch4" value="<?php echo $blist_csearch4;?>">
<input type="hidden" name="blist_tsearch4" id="blist_tsearch4" value="<?php echo $blist_tsearch4;?>">
<input type="hidden" name="search" id="search" value="<?php echo $search;?>">
<input type="hidden" name="assignto2" id="assignto2" value="" />
<input type="hidden" name="temp_regional" id="temp_regional" value="" />
<input type="hidden" name="temp_asset" id="temp_asset" value="" />
<input type="hidden" name="temp_kendaraan" id="temp_kendaraan" value="" />
<input type="hidden" name="temp_cabang" id="temp_cabang" value="" />
<input type="hidden" name="temp_call_status" id="temp_call_status" value="" />
<input type="hidden" name="temp_priority" id="temp_priority" value="" />


<div class="page-inner">
  <div class="page-header"  style="margin-bottom:0px;margin-top:-15px;padding-left:0px;padding:0px;margin-left:-20px;">
    <ul class="breadcrumbs" style="border-left:0px;margin:0px;">
      <li class="nav-home">
        <a href="index.php">
          <i class="fas fa-home"></i>
        </a>
      </li>
      <li class="separator">
        <i class="fas fa-chevron-right"></i>
      </li>
      <?php
        $menu_tree = explode("|", $library['page']);
        for ($i=0; $i <count($menu_tree) ; $i++) { 
          if ($i != 0) {
            echo "<li class=\"separator\"><i class=\"fas fa-chevron-right\"></i></li>";
          }
          echo "<li class=\"nav-item\">".$menu_tree[$i]."</li>";;
        }
        // echo "<li class=\"separator\"><i class=\"fas fa-chevron-right\"></i></li>";
        // echo "<li class=\"nav-item\">".$desc_iddet."</li>";;       
      ?>
    </ul>
  </div>

  <div class="content" style="margin-top: 10px;">
    <div class="row">
      <!-- table 3 start -->
      <div class="col-md-12">
        <div class="card" style="padding-bottom: 30px;">
          <div class="col-md-12">
            <!-- form body -->
            <div class="form-body">
              <!-- Form title -->
              <h4 class="form-section"><i class="fas fa-list-ul"></i> Assignment Agent</h4>
              
              <!-- assignment method form -->
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label for="assignment_method">Assignment Method <span class='required-label'>*</span></label>
                    <select  name="assignment_method" id="assignment_method"  class="form-control select2" required>
                      <option value="0" selected disabled>--select assignment method--</option>
                      <option value="1">Generate Data</option>
                      <option value="2">Assign By Kontrak</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="generate_data2" style="display: none;">
              <?php
              $x         = 0;
              $txtlabel[$x]      = "Bucket <span class='required-label'>*</span>";
              $bodycontent[$x]   = get_select_bucket_by_group($conDB, "bucket", "bucket", "required", "");
              $x++; 

              echo label_form_det($txtlabel,$bodycontent,$x);
              ?>
              </div>

              

              <!-- start assignment method : generate data -->
              <div class="generate_data" style="display: none;">
                
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="regional">Regional <span class='required-label'>*</span></label>
                      <select  name="regional[]" id="regional"  class="form-control" style="width:100%;" required>
                        <!-- <option value="" selected disabled>--Select Regional--</option> -->
                      </select>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="cabang">Cabang <span class='required-label'>*</span></label>
                      <select  name="cabang[]" id="cabang"  class="form-control" style="width:100%;" required>
                        <option value="0" selected disabled>--Select Cabang--</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="asset_type_kendaraan">Asset Type <span class='required-label'>*</span></label>
                      <select  name="asset_type_kendaraan[]" id="asset_type_kendaraan"  class="form-control" style="width:100%;" required>
                        <!-- <option value="0" selected disabled>--Select Asset Type--</option> -->
                      </select>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="status_call">Last Status Call <span class='required-label'>*</span></label>
                      <select  name="status_call" id="status_call"  class="form-control select2" style="width:100%;" required>
                        <option value="0" selected disabled>--Select Last Status Call--</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="last_call_dt">Last Call Date <span class='required-label'>*</span></label>
                      <select  name="last_call_dt" id="last_call_dt"  class="form-control select2" style="width:100%;" required>
                        <option value="0" selected>All</option>
                        <option value="1">By Period</option>
                      </select>
                      <input type="text" name="last_call_periode" id="last_call_period" class="form-control last_call_period" style="width:100%; display: none;" placeholder="Last Call Date">
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="priority_label">Priority Label <span class='required-label'>*</span></label>
                      <select  name="priority_label[]" id="priority_label"  class="form-control" style="width:100%;" required>
                        <!-- <option value="0" selected disabled>--Select Asset Type--</option> -->
                      </select>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="assigned_agent">Agent</label>
                      <select  name="assigned_agent" id="assigned_agent" class="form-control select2" style="width:100%;">
                        <option value="">All</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="row" id="total_data_assign">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="val_num">Total Data Assign</label>
                      <input type="number" class="form-control" name="val_num" id="val_num" onkeyup="check_number_value()" min=0  placeholder="Total Data Assign" readonly>
                      <input type="number" class="form-control" name="val_num_max" id="val_num_max" min=1  placeholder="Total Data Assign" hidden>
                    </div>
                  </div>
                </div>
              </div>
              <!-- end  assignment method : generate data -->

              <!-- start assignment method : assign by contract-->
              <div class="assign_by_contracts" style="display: none;">
                <div style="display:flex; flex-wrap: nowrap;">
                  <div class="form-group">
                    <select class="form-control" id="filter_list" name="filter_list">
                      <option value="order_no">Order No / Contract</option>
                      <option value="customer_name">Customer Name</option>
                      <option value="customer_id">Customer No</option>
                    </select>
                  </div>
                  <div class="form-group" style="flex:1;">
                    <input type="text" name="contract_search" id="contract_search" class="form-control" placeholder="Search...">
                  </div>
                  <div class="form-group">
                    <button class="btn btn-success" type="button" onclick="searchDataContract()">Search</button>
                  </div>
                </div>
                <table id="datatablelist" class="table table-head-bg-info table-striped table-hover" style="width:100%; margin-bottom: 10px;" > 
                  <thead>
                    <tr>
                      <th style="width:20px"><input type="checkbox" id="checkall"/></th>
                      <th>bucket Code</th>
                      <th>bucket Name</th>
                      <th>Order No/  Contract</th>
                      <th>Customer Name</th>
                      <th>Customer No</th>
                    </tr>
                  </thead>
                    
                  <tbody>
                    <tr>
                      <td colspan="8" class="dataTables_empty">Loading data...</td>
                    </tr>   
                  </tbody>
                </table>

              </div>
              <!-- end assignment method : assign by contract-->
              <div class="generate_data3" style="display: none;">
              <?php
              $x=0;
              $txtlabel[$x]      = "Skill Name <span class='required-label'>*</span>";
              $bodycontent[$x]   = telesales_skill_outbound($conDB, "agt_skill_id", "agt_skill_id", $agt_skill_id,$v_agentid) ;
              $x++;
              echo label_form_det($txtlabel,$bodycontent,$x);
              echo "</div>";

              echo "<div id='agentsearch' style='display:none'>";
              
              $x=0;
              $txtlabel[$x]      = "Agent Name";
              $bodycontent[$x]   = "<input id=\"param_search\" name=\"param_search\" type=\"text\" class=\"form-control\"
              onkeyup=\"search_funct(this.value)\" placeholder=\"Search\">";
              $x++;
              echo label_form_det($txtlabel,$bodycontent,$x);

              echo "</div>";
              echo "<div id='agt_agent_wskill' style='padding:0px 22px;'></div>";

              echo "<div id='agent_select' style='display:none'>";
              $x=0;
              $txtlabel[$x]      = "Agent Name <span class='required-label'>*</span>";
              $bodycontent[$x]   = get_select_agents($condb, "agent", "agent", 'required', $v_agentid);
              $x++;
              echo label_form_det($txtlabel,$bodycontent,$x);
              
              echo "</div>";
              ?>
              

              <div class="row">
                <div class="col-md-12">
                  <button class="btn btn-primary" id="sendassign"  name="sendassign" onclick="sendAssignment();return false;" style="margin: 0px 10px; float: right;"><i class="fa fa-paper-plane" aria-hidden="true"></i> Assign</button>
                </div>
              </div>

            </div>
            <!-- form body -->

          </div>
        </div>
      </div>
    </div>
    <!-- table 3 end -->    
  </div>
</div>


</form>

<div style="height:100%;top:0px;left:0px;position: fixed;z-index: 999999;text-align: center;width:100%;display: none" id="tempatLoading">
   <div style="width:400px;margin:auto;margin-top:200px;padding:10px;">
     <img src="assets/img/elyphsoft.gif" width="140px" style="border: 0px;border-radius: 4px;padding: 1px;border-radius:150px">
      <h1 style="font-weight:bold;color: white; text-shadow: -2px -2px 0 <?php echo $dominant_mastercolor_darker ?>, 2px -2px 0 <?php echo $dominant_mastercolor_darker ?>, -2px 2px 0 <?php echo $dominant_mastercolor_darker ?>, 2px 2px 0 <?php echo $dominant_mastercolor_darker ?>;">Please Wait</h1>
      <h2 style="font-weight:bold;color: white; text-shadow: -2px -2px 0 <?php echo $dominant_mastercolor_darker ?>, 2px -2px 0 <?php echo $dominant_mastercolor_darker ?>, -2px 2px 0 <?php echo $dominant_mastercolor_darker ?>, 2px 2px 0 <?php echo $dominant_mastercolor_darker ?>;">While We're Hit Your Data</h2>
   </div>
</div>
<?php
disconnectDB($condb);
?>

  <link rel="stylesheet" type="text/css" href="assets/css/pickers/daterange/daterangepicker.css">


<!--   Core JS Files   -->
  <script src="assets/js/core/jquery.3.2.1.min.js"></script>
  <script src="assets/js/core/popper.min.js"></script>
  <script src="assets/js/core/bootstrap.min.js"></script>
  <!-- jQuery UI -->
  <script src="assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
  <script src="assets/js/plugin/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js"></script>
  
  <!-- Sweet Alert -->
  <script src="assets/js/plugin/sweetalert/sweetalert.min.js"></script>
  <!-- Bootstrap Toggle -->
  <script src="assets/js/plugin/bootstrap-toggle/bootstrap-toggle.min.js"></script>
  <!-- jQuery Scrollbar -->
  <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
  <!-- Select2 -->
  <script src="assets/js/plugin/select2/select2.full.min.js"></script>
  <!-- jQuery Validation -->
  <!-- Datatables -->
  <script src="assets/js/plugin/datatables/datatables.min.js"></script>
  <script src="assets/js/plugin/jquery.validate/jquery.validate.min.js"></script>
  <!-- Bootstrap Tagsinput -->
  <script src="assets/js/plugin/bootstrap-tagsinput/bootstrap-tagsinput.min.js"></script>
  <!-- Atlantis JS -->
  <script src="assets/js/atlantis.min.js"></script>
  <script src="assets/js/setting.js"></script>

  <!-- datepicker -->
  <script src="assets/js/plugin/moment/moment.min.js"></script>
  <script src="assets/js/plugin/datepicker/bootstrap-datetimepicker.min.js"></script>
  <script src="assets/js/plugin/pickers/daterange/daterangepicker.js" type="text/javascript"></script>
  
  <script lang="javascript">
  $('#agt_skill_id').prop("required", "required");
  $('#agt_skill_id').prop("disabled", "disabled");
  $('#bucket').prop("disabled", "disabled");
  // $("#region").prepend("<option value='0' selected>All</option>");
  // $("#asset_type_kendaraan").prepend("<option value='0' selected>All</option>");
  // $("#kategori_kendaraan").prepend("<option value='0' selected>All</option>");
  // $("#last_call_dt").prepend("<option value='0' selected>All</option>");
  $("#cabang").prepend("<option value='0' selected>All</option>");
  $("#status_call").prepend("<option value='0' selected>All</option>");
  $('#total_data_assign').css("display", "none");

    $(document).ready( function () {
      $('#regional').select2({
        theme: "bootstrap",
        placeholder: "--- select regional ---",
        multiple: true
      });

      $('#asset_type_kendaraan').select2({
        theme: "bootstrap",
        placeholder: "--- select type asset ---",
        multiple: true
      });

      $('#priority_label').select2({
        theme: "bootstrap",
        placeholder: "--- select priority label ---",
        multiple: true
      });

      $('#kategori_kendaraan').select2({
        theme: "bootstrap",
        placeholder: "--- select kategory kendaraan ---",
        multiple: true
      });

      $('#cabang').select2({
        theme: "bootstrap",
        placeholder: "--- select cabang ---",
        multiple: true
      });

      $('#status_call').select2({
        theme: "bootstrap",
        placeholder: "--- select Last Status Call ---"
      });


    //   var oTable = $('#datatablelist').dataTable({
    //   // "lengthMenu": [[10], [10, 25, 50, "All"]],
    //   dom: 'Bfrtip<"top"><"bottom"l><"clear">',
    //   "bPaginate"     : true,
    //   "bSort"         : false,
    //   "bInfo"         : true,
    //   "bProcessing"   : true,
    //   "bLengthChange" : false,
    //   "sAjaxSource"   : "<?php echo $link_data_acc;?>",
    //   "fnServerParams": function (aoData) {
    //     aoData.push(
    //       { "name": "cmbbucket", "value": $("#bucket").val() }    
    //     );
    //   }         
    // });


    var oTable = $('#datatablelist').dataTable({
      data            : [],
      "fnrowCallback"     : function(nRow, aData, iDisplayIndex, iDisplayIndexFull ){},
      // "lengthMenu": [[10], [10, 25, 50, "All"]],
      dom: 'Bfrtip<"top"><"bottom"l><"clear">',
      "bPaginate"     : true,
      "bFilter"       : false,
      "bSort"         : false,
      "bInfo"         : true,
      "bProcessing"   : true,
      "bLengthChange" : false,
      "fnServerParams": function (aoData) {
        aoData.push(
          { "name": "cmbbucket", "value": $("#bucket").val() }    
        );
      }         
    });


      $('#bucket').on("change", function(){
        method = $('#assignment_method').val();
        if (method == 2) {
        // table = $('#datatablelist').DataTable();
        //  $.ajax({
        //     url: "<?php echo $link_data_acc;?>&cmbbucket="+this.value,
        //     type: "post"
        //   }).done(function (result) {
        //     result = jQuery.parseJSON(result);
        //     table.clear().draw();
        //     table.rows.add(result.aaData).draw();
        //   }).fail(function (jqXHR, textStatus, errorThrown) { 
        //           // needs to implement if it fails
        //   });
        }else{
          loadDetbucket();
         
          loadCabang();
          loadCallStatus();
          var val = $("#last_call_dt").val();
          if (val == 1) {
            $("#last_call_period").css("display", "block");
            loadLastcall();
          }
          // loadLastcall();
        }
        return true;
      });

    });

    $('#regional').on("change", function(){
      method = $('#assignment_method').val();
      var vgroup_id = document.getElementById("regional").value;
      var s = vgroup_id.indexOf("0");
      if(s=="0"){
        $('#regional').val('').trigger("change");
        $('#regional').val('0');
      }
      if (method == 1) {
        count_data();
        agent();
        var val = $("#last_call_dt").val();
        if (val == 1) {
          $("#last_call_period").css("display", "block");
          loadLastcall();
        }
      }
      loadCabang();
      return true;
    });

    $('#cabang').on("change", function(){
      method = $('#assignment_method').val();

      var vgroup_id = document.getElementById("cabang").value;
      // console.log(" cabang : "+vgroup_id);
      var s = vgroup_id.indexOf("0");
      // console.log(" indexOf : "+s);
      if(parseInt(vgroup_id)=="0"){
        $('#cabang').val('').trigger("change");
        $('#cabang').val('0');
      }
      if (method == 1) {
        count_data();
        agent();
        var val = $("#last_call_dt").val();
        if (val == 1) {
          $("#last_call_period").css("display", "block");
          loadLastcall();
        }
      }
      return true;
    });

    $('#status_call').on("change", function(){
      method = $('#assignment_method').val();
      value = $(this).val();
      last_call_dt = $('#last_call_dt option[value="1"]');


      // var vgroup_id = document.getElementById("status_call").value;
      // console.log(" cabang : "+vgroup_id);
      // var s = vgroup_id.indexOf("0");
      // console.log(" indexOf : "+s);
      // if(parseInt(vgroup_id)=="0"){
      //   $('#status_call').val('').trigger("change");
      //   $('#status_call').val('0');
      // }

      // console.log(last_call_dt);
      if(value == "99"){
        // console.log("disabled");
        last_call_dt.prop("disabled", "disabled");
      }else{
        // console.log("remove");
        last_call_dt.removeAttr("disabled");
      }
      last_call_dt.select2();

      if (method == 1) {
        count_data();
        agent();
        var val = $("#last_call_dt").val();
        if (val == 1) {
          $("#last_call_period").css("display", "block");
          loadLastcall();
        }else{
          $("#last_call_period").css("display", "none");
          $('#last_call_dt').select2("val", "0")
        }
      }
      return true;
    });

    $('#priority_label').on("change", function(){
      method = $('#assignment_method').val();

      var vgroup_id = document.getElementById("priority_label").value;
      // console.log(" cabang : "+vgroup_id);
      var s = vgroup_id.indexOf("0");
      // console.log(" indexOf : "+s);
      if(parseInt(vgroup_id)=="0"){
        $('#priority_label').val('').trigger("change");
        $('#priority_label').val('0');
      }
      if (method == 1) {
        count_data();
        agent();
        // var val = $("#last_call_dt").val();
        // if (val == 1) {
        //   $("#last_call_period").css("display", "block");
        //   loadLastcall();
        // }
      }
      return true;
    });

    $('#asset_type_kendaraan').on("change", function(){
      method = $('#assignment_method').val();
      
      var vgroup_id = document.getElementById("asset_type_kendaraan").value;
      var s = vgroup_id.indexOf("0");
      if(s=="0"){
        $('#asset_type_kendaraan').val('').trigger("change");
        $('#asset_type_kendaraan').val('0');
      }
      if (method == 1) {
        count_data();
        agent();
        var val = $("#last_call_dt").val();
        if (val == 1) {
          $("#last_call_period").css("display", "block");
          loadLastcall();
        }
      }
      return true;
    });

    $('#kategori_kendaraan').on("change", function(){
      method = $('#assignment_method').val();

      var vgroup_id = document.getElementById("kategori_kendaraan").value;
      var s = vgroup_id.indexOf("0");
      if(s=="0"){
        $('#kategori_kendaraan').val('').trigger("change");
        $('#kategori_kendaraan').val('0');
      }
      if (method == 1) {
        count_data();
        agent();
        var val = $("#last_call_dt").val();
        if (val == 1) {
          $("#last_call_period").css("display", "block");
          loadLastcall();
        }
      }
      return true;
    });

    $('#assigned_agent').on("change", function(){
      method = $('#assignment_method').val();
      if (method == 1) {
        // var val = $("#last_call_dt").val();
        // if (val == 1) {
        //   $("#last_call_period").css("display", "block");
        //   loadLastcall();
        // }
        count_data();
      }
      return true;
    });

    $("#last_call_dt").on("change", function(){
      var val = this.value;
      if (val == 1) {
        $("#last_call_period").css("display", "block");
        loadLastcall();
      }else{
        $("#last_call_period").css("display", "none");
      }
      count_data();
      agent();
    })

    $("#last_call_period").on("change", function(){
      count_data();
      agent();
    })

    $("#agt_skill_id").on('change',function() {
      var skill = document.getElementById("agt_skill_id").value;
      var grpid = "<?php echo $v_agentgroup;?>";
      method = $('#assignment_method').val();
      if(skill !== "" && (method == '1' && method !== 0)) {
        document.getElementById('agent_select').style.display = 'none';
        var dStr  = "v=wskill&skillid="+skill+"&grpid="+grpid;

        $.ajax({ 
          type: 'POST', 
          url: "<?php echo $get_data; ?>",
          data: dStr,
          dataType:'json', 
          success: function (data) {
            // console.log(data.sql)
            $('#agt_agent_wskill').html(data.arr_agent);
            document.getElementById('agentsearch').style.display = 'block';
          }
        });
      }else {
        if (method == '2') {
          document.getElementById('agent_select').style.display = 'block';

          var dStr  = "f=get_agent_by_skill&skill_id="+skill;

          $.ajax({ 
            type: 'POST', 
            url: "view/teleupload/global_func_teleupload.php?f=get_agent_by_skill",
            data: dStr,
            dataType:'json', 
            success: function (data) {
              // console.log(data['sql']);
              $('#agent').html(data.sel);
              // document.getElementById('agentsearch').style.display = 'block';
            }
          });
        }

        $('#agt_agent_wskill').html("");
        document.getElementById('agentsearch').style.display = 'none';
      }
    });

    $('#assignment_method').on('change', function() {
      $("#agt_skill_id").val('').trigger('change');
      $("#agt_skill_id").removeAttr("disabled");
      $('#bucket').removeAttr("disabled");
      document.getElementById('agent_select').style.display = 'none';
      $('#agt_agent_wskill').html("");
      switch(this.value){
        case '1':
        bucket = $('#bucket').val();
        if (bucket != "") {
            $('#bucket').val("");        
            $('#bucket').val(bucket).trigger("change");        
        }

        $('.assign_by_contracts').css("display", "none");
        $('.generate_data').css("display", "block");
        $('.generate_data2').css("display", "block");
        $('.generate_data3').css("display", "block");
        $('#total_data_assign').css("display", "block");
        break;

        case '2':
        $('.generate_data').css("display", "none");
        $('.generate_data2').css("display", "block");
        $('.generate_data3').css("display", "block");
        $('.assign_by_contracts').css("display", "block");
        $('#total_data_assign').css("display", "none");
        break;
      }
    });


      $('#checkall').click(function(e){
        var oTable = $('#datatablelist').DataTable();
        var allPages = oTable.cells( ).nodes( );

        if (document.getElementById('checkall').checked == true) {
           swal({
            title: 'Are you sure want to check all data?',
            text: "",
            type: 'warning',
            buttons:{
              confirm: {
                text : 'Yes',
                className : 'btn btn-success'
              },
              cancel: {
                visible: true,
                className: 'btn btn-danger'
              }
            }
          }).then((Save) => {
            if (Save) {
              $(allPages).find('input[id="check_assign"]').prop('checked', true);
              $(allPages).find('input[id="check_assign"]').parent().parent().css('backgroundColor',"#cfbdd1");
            } else {
              $(this).prop("checked", false);
              swal.close();
            }
          });
        } else if (document.getElementById('checkall').checked == false) {
          $(allPages).find('input[id="check_assign"]').prop('checked', false);
          $(allPages).find('input[id="check_assign"]').parent().parent().css('backgroundColor',"transparent");

        }
      });


    function check_number_value(){
      var value = $('#val_num').val();
      var max = $('#val_num_max').val();
      
      if (parseInt(value) < 0) {
        $('#val_num').val(0)
      }else if(parseInt(value) > parseInt(max)) {
        $('#val_num').val(max);
      }

    }


    function count_data(){
      $("#tempatLoading").css("display", "block");
      method                = $('#assignment_method').val();
      bucket              = $('#bucket').val();
      region                = $('#regional').val();
      cabang                = $('#cabang').val();
      asset_type_kendaraan  = $('#asset_type_kendaraan').val();
      kategori_kendaraan    = $('#kategori_kendaraan').val();
      status_call           = $('#status_call').val();
      priority_label        = $('#priority_label').val();
      assigned_agent        = $('#assigned_agent').val();
      // console.log(assigned_agent);
      last_call_dt          = $('#last_call_dt').val();
      last_call_period      = $('#last_call_period').val();
      // result = asset_type_kendaraan;
      result = '';
      $.each(asset_type_kendaraan, function( index, value ) {
        if (value != 0) {
          index == 0 ? result +=  "'"+value+"'" : result +=  ",'"+value+"'";
        }
      });


      result_cabang = '';
      $.each(cabang, function( index, value ) {
        if (value != 0) {
          index == 0 ? result_cabang +=  "'"+value+"'" : result_cabang +=  ",'"+value+"'";
        }
      });


      result_status_call = status_call;
      // result_status_call = '';
      // $.each(status_call, function( index, value ) {
      //   if (value != 0) {
      //     index == 0 ? result_status_call +=  "'"+value+"'" : result_status_call +=  ",'"+value+"'";
      //   }
      // });

      result_priority = '';
      $.each(priority_label, function( index, value ) {
        if (value != 0) {
          index == 0 ? result_priority +=  "'"+value+"'" : result_priority +=  ",'"+value+"'";
        }
      });
      // console.log(result_cabang);
      if (method == 1) {

      $("#val_num").removeAttr('readonly');
        $.get( "view/teleupload/global_func_teleupload.php?f=count_data&bucket="+bucket+"&region="+region+"&cabang="+result_cabang+"&kategori_kendaraan="+kategori_kendaraan+"&status_call="+result_status_call+"&asset_type_kendaraan="+result+"&priority_label="+result_priority+"&assigned_agent="+assigned_agent+"&last_call_dt="+last_call_dt+"&last_call_period="+last_call_period, function(total) {
          total = jQuery.parseJSON(total);
          // console.log(total);
          $("#val_num").val(total.total_data);
          $("#val_num_max").val(total.total_data);
          $("#val_num").prop("max", total.total_data);
          $("#val_num").removeAttr('readonly');

          $("#tempatLoading").css("display", "none");
        });

      }
    }

    function agent(){
      method                = $('#assignment_method').val();
      bucket              = $('#bucket').val();
      region                = $('#regional').val();
      cabang                = $('#cabang').val();
      asset_type_kendaraan  = $('#asset_type_kendaraan').val();
      kategori_kendaraan    = $('#kategori_kendaraan').val();
      status_call           = $('#status_call').val();
      priority_label        = $('#priority_label').val();
      last_call_dt          = $('#last_call_dt').val();
      last_call_period      = $('#last_call_period').val();
      // result = asset_type_kendaraan;
      result = '';
      $.each(asset_type_kendaraan, function( index, value ) {
        if (value != 0) {
          index == 0 ? result +=  "'"+value+"'" : result +=  ",'"+value+"'";
        }
      });


      result_cabang = '';
      $.each(cabang, function( index, value ) {
        if (value != 0) {
          index == 0 ? result_cabang +=  "'"+value+"'" : result_cabang +=  ",'"+value+"'";
        }
      });


      result_status_call = status_call;
      // result_status_call = '';
      // $.each(status_call, function( index, value ) {
      //   if (value != 0) {
      //     index == 0 ? result_status_call +=  "'"+value+"'" : result_status_call +=  ",'"+value+"'";
      //   }
      // });

      result_priority = '';
      $.each(priority_label, function( index, value ) {
        if (value != 0) {
          index == 0 ? result_priority +=  "'"+value+"'" : result_priority +=  ",'"+value+"'";
        }
      });
      // console.log(result_cabang);
      if (method == 1) {

      $("#val_num").removeAttr('readonly');
        $.get( "view/teleupload/global_func_teleupload.php?f=assigned_agent&bucket="+bucket+"&region="+region+"&cabang="+result_cabang+"&kategori_kendaraan="+kategori_kendaraan+"&status_call="+result_status_call+"&asset_type_kendaraan="+result+"&priority_label="+result_priority+"&last_call_dt="+last_call_dt+"&last_call_period="+last_call_period, function(data) {
          data = jQuery.parseJSON(data);
          // console.log(data);

          $("#assigned_agent").html(data.view);
        });

      }
    }

    function loadDetbucket(){
      bucket_id = $("#bucket").val();
      $.get( "view/teleupload/global_func_teleupload.php?f=load_det_bucket&bucket_id="+bucket_id, function(data) {
        data = jQuery.parseJSON(data);
        $('#temp_regional').val(data.regional);
        $('#temp_asset').val(data.asset_type_kendaraan).trigger("change");
        $('#temp_kendaraan').val(data.prio_tipe_kendaraan).trigger("change");
        $('#temp_priority').val(data.priority_label).trigger("change");
        loadRegion(data.regional);
        loadAssetType(data.asset_type_kendaraan);
        loadKategoriKendaraan(data.prio_tipe_kendaraan); 
        loadPriority(data.priority_label);        
      });
    }

    function search_funct(val_search){
      var paramcheck2 = '';
      var skill = document.getElementById("agt_skill_id").value;
      document.getElementById("param_search").value = val_search;
      var grpid = "<?php echo $v_agentgroup;?>";

      var dStr  = "v=wskill&skillid="+skill+"&grpid="+grpid+"&val_search="+val_search+"&paramcheck="+paramcheck2;
      
        $.ajax({ 
              type: 'POST', 
              url: "<?php echo $get_data; ?>",
              data: dStr,
              dataType:'json', 
              success: function (data) {
                $('#agt_agent_wskill').html(data.arr_agent);
              }
          });
    }

    function loadRegion(region){
      bucket  = $('#bucket').val();

      var dStr  = "&bucket_id="+bucket+"&regional="+region;
      
      $.ajax({ 
          type: 'POST', 
          url: "view/teleupload/global_func_teleupload.php?f=get_select_regional&bucket_id="+bucket,
          data: dStr,
          dataType:'json', 
          success: function (data) {
            $('#regional').html(data.arrSel);
            count_data();
            agent();
          }
      });
    }
    function loadAssetType(type){
      bucket  = $('#bucket').val();

      var dStr  = "&bucket_id="+bucket+"&type="+type;
      
      $.ajax({ 
          type: 'POST', 
          url: "view/teleupload/global_func_teleupload.php?f=get_select_asset_type&bucket_id="+bucket,
          data: dStr,
          dataType:'json', 
          success: function (data) {
            // console.log(data.sql);
            $('#asset_type_kendaraan').html(data.arrSel);
            count_data();
            agent();
          }
      });
    }
    function loadPriority(type){
      bucket  = $('#bucket').val();

      var dStr  = "&bucket_id="+bucket+"&type="+type;
      
      $.ajax({ 
          type: 'POST', 
          url: "view/teleupload/global_func_teleupload.php?f=get_select_priority&bucket_id="+bucket,
          data: dStr,
          dataType:'json', 
          success: function (data) {
            // console.log(data.sql);
            $('#priority_label').html(data.arrSel);
            count_data();
            agent();
          }
      });
    }

    function loadKategoriKendaraan(kendaraan){
      bucket  = $('#bucket').val();

      var dStr  = "&bucket_id="+bucket+"&kendaraan="+kendaraan;
      
      $.ajax({ 
          type: 'POST', 
          url: "view/teleupload/global_func_teleupload.php?f=get_select_kategori_kendaraan&bucket_id="+bucket,
          data: dStr,
          dataType:'json', 
          success: function (data) {
            // console.log(data);
            $('#kategori_kendaraan').html(data.arrSel);
            count_data();
            agent();
          }
      });
    }
    function loadCabang(){
      bucket    = $('#bucket').val();
      regional  = $('#regional').val();
      var dStr  = "&bucket_id="+bucket+"&regional_param="+regional;
      
      $.ajax({ 
          type: 'POST', 
          url: "view/teleupload/global_func_teleupload.php?f=get_select_cabang&bucket_id="+bucket+"&regional_param="+regional,
          data: dStr,
          dataType:'json', 
          success: function (data) {
            // console.log(data.sql);
            $('#cabang').html(data.arrSel);
            count_data();
            agent();
          }
      });
    }
    function loadCallStatus(){
      bucket  = $('#bucket').val();
      var dStr  = "&bucket_id="+bucket;
      
      $.ajax({ 
          type: 'POST', 
          url: "view/teleupload/global_func_teleupload.php?f=get_select_call_status&bucket_id="+bucket,
          data: dStr,
          dataType:'json', 
          success: function (data) {
            // console.log(data.sql);
            $('#status_call').html(data.arrSel);
            // count_data();
          }
      });
    }

    function loadLastcall(){
      bucket        = $('#bucket').val();
      status_call   = $('#status_call').val();
      var dStr      = "&bucket_id="+bucket;
      
      $.ajax({ 
          type: 'POST', 
          url: "view/teleupload/global_func_teleupload.php?f=get_select_lastcall&bucket_id="+bucket+"&status_call="+status_call,
          data: dStr,
          dataType:'json', 
          success: function (data) {
            // console.log(data);
            if (data.mindate != null) {
              $('.last_call_period').daterangepicker({
                  locale: {
                    format: 'YYYY-MM-DD'
                  },
                  startDate: data.mindate,
                  minDate: data.mindate,
                  maxDate: data.maxdate
              });
            }else{
              $('.last_call_period').css("display", "none");
              $('#last_call_dt').val(0).trigger("change");
            }
            count_data();
          }
      });
    }

    function checkstatus(){
      var totalCheckboxes = $('input[name="fomni_id[]"]').length;
      var totalCheck = parseInt(totalCheckboxes);
      var inputElems = $('input[name="fomni_id[]"]');

      if(document.getElementById("fomni_0").checked){
        $('input[name="fomni_id[]"]').prop("checked", true);
        var listcek = "";
        for (var i=0; i<inputElems.length; i++) { 
          if (inputElems[i].type == "checkbox" && inputElems[i].checked == true){

            if(listcek == "") {
              listcek += inputElems[i].value;
            } else {
              listcek += "|"+inputElems[i].value;

            }
          }
        }
        document.getElementById("param_check").value = listcek;
      } else {
        $('input[name="fomni_id[]"]').prop("checked", false);
        document.getElementById("param_check").value = "";
      }
    }

      function checkBulk(){
            var row_bulk = document.getElementsByClassName('row_bulk');
            // alert(row_bulk);
            for (var i = 0; i < row_bulk.length; i++) {
                if(row_bulk[i].checked == true){
                    row_bulk[i].parentNode.parentNode.style.backgroundColor = "#cfbdd1";
                     // alert("1"+row_bulk.value);
                }
            else{
                    row_bulk[i].parentNode.parentNode.style.backgroundColor = "transparent";
            //          // alert("2"+row_bulk.value);
                }
            }
        }

        function sendAssignment(){
          // console.log("click")
      var countcek    = $(":checkbox:checked").length;
      var countdata   = $("#val_num_max").val();
      var method      = $("#assignment_method").val();
      // console.log(method);
      if(countcek==0){
        return alert("You must select data first!");
      }else if(countdata == 0 && method == 1){
        return alert("Data Not Found, You can filter data again");
      }else{
        var fvalid = form.valid();
        if(fvalid==true){

          swal({
            title: 'Are you sure want to save?',
            text: "",
            type: 'warning',
            buttons:{
              confirm: {
                text : 'Yes',
                className : 'btn btn-success'
              },
              cancel: {
                visible: true,
                className: 'btn btn-danger'
              }
            }
          }).then((Save) => {
            if (Save) {
               var data = new FormData();
               var form_data = $('#frmDataDet').serializeArray();
               $.each(form_data, function (key, input) {
                  data.append(input.name, input.value);
                  // console.log(input.name+' - '+input.value);
               });

               // var row_bulk = document.getElementsByClassName("row_bulk");
                // var capture = "";
                  // for (var i = 0; i < row_bulk.length; i++) {
              //      if (row_bulk[i].checked == true) {
                // var capture = "";
              //        capture = capture + "," + row_bulk[i].value;     
              //       }
            
                  // }

                  // count checked value start
                  var oTable = $('#datatablelist').DataTable();
                  var allPages = oTable.cells( ).nodes( );
                  var capture = "";
                  values = $(allPages).find('input[id="check_assign"]:checked');
                  values.each(function( index, element ) {
                    capture = capture + "," + element.value;     
                  });
                  capture = "0" + capture;
                  // count checked value end
                  data.append('iddeb', capture);

               data.append('key', 'value'); 
              
               $.ajax({
                    url: "<?php echo $save_form; ?>",
                    type: "post",
                    data: data,
                  processData: false,
                  contentType: false,
                    success: function(d) {
                      var warn = d;
                      // console.log(d);

                      warn = warn.split("|");
                      if(warn[0]=="Success!") {
                        var vtype = "Success";
                        var text  = "Success!\nData: "+warn[1]+'/'+warn[2];
                      } else {
                        var vtype = "error";  
                        var text  = warn[0];
                      }
                      // console.log(text);
                      swal({ title: "Save Data!", type: vtype,  text: text,   timer: 5000,   showConfirmButton: false });
                      if(warn=="Success") {
                        table = $('#datatablelist').DataTable();
                        method = $('#assignment_method').val();
                      } 
                      setTimeout('location.reload(true);', 1500);
                    }
                });
            } else {
              swal.close();
            }
          });
      }
      }
        }

        function formchange(){
          var paramfrom     = document.getElementById('cmbfrom').value;
          // alert(paramfrom);
          if (paramfrom==0) {
            document.getElementById('search').value='AND NOT EXISTS(SELECT d.debiturid FROM coll_assignment d WHERE d.debiturid = b.debiturid)';
          }
        }

    
  var form = $( "#frmDataDet" );
  form.validate();

    $("#btnSaveForm").click(function(){ 
       var fvalid = form.valid();
       if(fvalid==true){

      swal({
            title: 'Are you sure want to save?',
            text: "",
            type: 'warning',
            buttons:{
              confirm: {
                text : 'Yes',
                className : 'btn btn-success'
              },
              cancel: {
                visible: true,
                className: 'btn btn-danger'
              }
            }
          }).then((Save) => {
            if (Save) {
               var data = new FormData();
               var form_data = $('#frmDataDet').serializeArray();
               $.each(form_data, function (key, input) {
                  data.append(input.name, input.value);
               });


               data.append('key', 'value'); 
              
               $.ajax({
                  url: "<?php echo $save_form; ?>",
                  type: "post",
                  data: data,
                  processData: false,
                  contentType: false,
                    success: function(d) {
                      var warn = d;
                      if(warn=="Success!") {
                        var vtype = "success";
                      } else {
                        var vtype = "error";  
                      }
                        // swal({ title: "Save Data!", type: vtype,  text: warn,   timer: 1000,   showConfirmButton: false });
                      if(warn=="Success") {
                          //setTimeout(function(){history.back();}, 1500);
                      } 
                    }
                });
            } else {
              swal.close();
            }
          });
      
    }else{
      swal({ title: "Info!", type: "error",  text: "Please fill in all mandatory",   timer: 1000,   showConfirmButton: false });
    }
        return false;
  }) 


   $("#btnCancelForm").click(function(){
      swal({
            title: 'Are you sure to return to the previous page?',
            text: "",
            type: 'warning',
            buttons:{
              confirm: {
                text : 'Yes',
                className : 'btn btn-success'
              },
              cancel: {
                visible: true,
                className: 'btn btn-danger'
              }
            }
          }).then((Save) => {
            if (Save) {
              var alink= "<?php echo $ffolder;?>|<?php echo $fmenu_link_back;?>|<?php echo $fdescription;?>|<?php echo $fmenu_id;?>|<?php echo $ficon;?>|<?php echo $fiddet;?>|<?php echo $fblist;?>"
              var link = "index.php?v="+encodeURI(btoa(alink));
              window.location.href = link;
              //window.history.back();
            } else {
              swal.close();
            }
          });
        return false;
  })
  
    $("#btnDeleteForm").click(function(){
    var iddet = document.getElementById("iddet").value;

         swal({
            title: 'Are you sure want to delete?',
            text: "",
            type: 'warning',
            buttons:{
              confirm: {
                text : 'Yes',
                className : 'btn btn-success'
              },
              cancel: {
                visible: true,
                className: 'btn btn-danger'
              }
            }
          }).then((Save) => {
            if (Save) {
               var data = new FormData();
               var form_data = $('#frmDataDet').serializeArray();
               $.each(form_data, function (key, input) {
                  data.append(input.name, input.value);
               });

               data.append('key', 'value'); 
              
               $.ajax({
                    url: "<?php echo $save_form; ?>?v=del&iddet="+iddet,
                    type: "post",
                    data: data,
                  processData: false,
                  contentType: false,
                    success: function(d) {
                      var warn = d;
                        if(warn=="Success") {
                          var vtype = "success";
                        } else {
                    var vtype = "error";  
                        }
                        swal({ title: "Save Data!", type: vtype,  text: warn,   timer: 1000,   showConfirmButton: false });
                        if(warn=="Success") {
                          setTimeout(function(){history.back();}, 1500);
                        } 
                    }
                });
            } else {
              swal.close();
            }
          });
        return false;    
  }) 
    </script>

<script language="javascript">
function edit(param){
   var idIndex = param; 
   var link;

  <?php
  // $menuact  = $modfolder."|".$menu_linkdet."|".$menu_label."|".$menu_id."|".$menu_icon."|";
  $menuact  = $ffolder."|".$menu_linkdet."|".$menu_label."|".$fmenu_id."|".$ficon."|";
  ?>
  var link = "index.php?v="+encodeURI(btoa('<?php echo $menuact ?>'+idIndex));
    
    window.location = link;
  }
</script>

<script type="text/javascript">
  function searchDataContract(){
    cmbbucket = $('#bucket').val();
    method = $('#assignment_method').val();
    filter_list = $("#filter_list").val();
    contract_search = $("#contract_search").val();
    if (method == 2) {
      if(contract_search != ""){
        table = $('#datatablelist').DataTable();
        $.ajax({
          url: "<?php echo $link_data_acc;?>&cmbbucket="+cmbbucket+"&filterBy="+filter_list+"&filterValue="+contract_search,
          type: "post"
        }).done(function (result) {
          result = jQuery.parseJSON(result);
          table.clear().draw();
          table.rows.add(result.aaData).draw();
        }).fail(function (jqXHR, textStatus, errorThrown) { 
                // needs to implement if it fails
        });
      }else{
        swal({ title: "Warning!", type: "warning",  text: "Please enter your search",   timer: 1000,   showConfirmButton: false });
      }
    }
  }
</script>