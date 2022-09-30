
<?php
include('../template/template_admin.php');
?>


<!-- Template for graph on 3 row -->

<section class="page-content-general">

<div class="card-deck">
  <div class="card">
	<div class="pull-left">
		<div class="popup" onclick="showGraph()">
		<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title=""><span class="fas fa-info-circle  em088 opacityhigh"></span>
		<span class="popuptext" id="firstPop">
			<h4> Détails des informations / calculs </h4>
			<ul>
				<li><strong><?php print $firstPop_info1 ?></strong><br><?php print $firstPop_data1 ?></li><hr>
				<li><strong><?php print $firstPop_info2 ?></strong><br><?php print $firstPop_data2 ?> </li><hr>
				<li><strong><?php print $firstPop_info3 ?></strong><br><?php print $firstPop_data3 ?> </li>
			</ul>
		</span>
	</div>
</div>
<script>
// When the user clicks on div, open the popup
function showGraph() {
	var firstPopup = document.getElementById("firstPop");
	firstPopup.classList.toggle("show");
}
</script>
<ul>
	<h4><?php print $titleItem1 ?></h4>
	<h2><?php print $dataItem1 ?></p></h2>
</ul>
    <div class="card-body">
		<div class="center-block">
			<div class="pull-left"><?php print $info1 ?> <h4 class="center"><?php print $dataInfo1 ?></h4></div>
			<div class="pull-right"><?php print $info2 ?> <h4 class="center"><?php print $dataInfo2 ?></h4></div>
		</div>
    </div>
	<div class="chart "><?php print $graphiqueA ?></div>
  </div>


	<!-- 2 boxes -->
	<div class="card">
	<div class="pull-left">
		<div class="popup" onclick="showGraph()">
		<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title=""><span class="fas fa-info-circle  em088 opacityhigh"></span>
		<span class="popuptext" id="firstPop">
			<h4> Détails des informations / calculs </h4>
			<ul>
				<li><strong><?php print $firstPop_info4 ?></strong><br><?php print $firstPop_data4 ?></li><hr>
				<li><strong><?php print $firstPop_info5 ?></strong><br><?php print $firstPop_data5 ?> </li><hr>
				<li><strong><?php print $firstPop_info6 ?></strong><br><?php print $firstPop_data6 ?> </li>
			</ul>
		</span>
	</div>
</div>
<script>
// When the user clicks on div, open the popup
function showGraph() {
	var firstPopup = document.getElementById("firstPop");
	firstPopup.classList.toggle("show");
}
</script>
<ul>
	<h4><?php print $titleItem2 ?></h4>
	<h2><?php print $dataItem2 ?></p></h2>
</ul>
    <div class="card-body">
		<div class="center-block">
			<div class="pull-left"><?php print $info3 ?> <h4 class="center"><?php print $dataInfo3 ?></h4></div>
			<div class="pull-right"><?php print $info4 ?> <h4 class="center"><?php print $dataInfo4 ?></h4></div>
		</div>
    </div>
	<div class="chart "><?php print $graphiqueB ?></div>
  </div>


  	<!-- 2 boxes -->
	<div class="card">
	<div class="pull-left">
		<div class="popup" onclick="showGraph()">
		<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title=""><span class="fas fa-info-circle  em088 opacityhigh"></span>
		<span class="popuptext" id="firstPop">
			<h4> Détails des informations / calculs </h4>
			<ul>
				<li><strong><?php print $firstPop_info1 ?></strong><br><?php print $firstPop_data1 ?></li><hr>
				<li><strong><?php print $firstPop_info2 ?></strong><br><?php print $firstPop_data2 ?> </li><hr>
				<li><strong><?php print $firstPop_info3 ?></strong><br><?php print $firstPop_data3 ?> </li>
			</ul>
		</span>
	</div>
</div>
<script>
// When the user clicks on div, open the popup
function showGraph() {
	var firstPopup = document.getElementById("firstPop");
	firstPopup.classList.toggle("show");
}
</script>
<ul>
	<h4><?php print $titleItem3 ?></h4>
	<h2><?php print $dataItem3 ?></p></h2>
</ul>
    <div class="card-body">
		<div class="center-block">
			<div class="pull-left"><?php print $info5 ?> <h4 class="center"><?php print $dataInfo5 ?></h4></div>
			<div class="pull-right"><?php print $info6 ?> <h4 class="center"><?php print $dataInfo6 ?></h4></div>
		</div>
    </div>
	<div class="chart "><?php print $graphiqueC ?></div>
  </div>
</div>

<div class="card-deck">

  <!-- 3 list -->
  <div class="card">
	<div class="pull-left">
		<div class="popup" onclick="showGraph()">
		<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title=""><span class="fas fa-info-circle  em088 opacityhigh"></span>
		<span class="popuptext" id="firstPop">
			<h4> Détails des informations / calculs </h4>
			<ul>
				<li><strong><?php print $firstPop_info1 ?></strong><br><?php print $firstPop_data1 ?></li><hr>
				<li><strong><?php print $firstPop_info2 ?></strong><br><?php print $firstPop_data2 ?> </li><hr>
				<li><strong><?php print $firstPop_info3 ?></strong><br><?php print $firstPop_data3 ?> </li>
			</ul>
		</span>
	</div>
</div>
<script>
// When the user clicks on div, open the popup
function showGraph() {
	var firstPopup = document.getElementById("firstPop");
	firstPopup.classList.toggle("show");
}
</script>
<ul>
	<h4><?php print $titleItem4 ?></h4>
	<h2><?php print $dataItem4 ?></p></h2>
</ul>
    <div class="card-body">
		<div class="center-block">
			<?php print $listeA ?>
		</div>
    </div>
</div>


  <!-- 2 list -->
  <div class="card">
	<div class="pull-left">
		<div class="popup" onclick="showGraph()">
		<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title=""><span class="fas fa-info-circle  em088 opacityhigh"></span>
		<span class="popuptext" id="firstPop">
			<h4> Détails des informations / calculs </h4>
			<ul>
				<li><strong><?php print $firstPop_info1 ?></strong><br><?php print $firstPop_data1 ?></li><hr>
				<li><strong><?php print $firstPop_info2 ?></strong><br><?php print $firstPop_data2 ?> </li><hr>
				<li><strong><?php print $firstPop_info3 ?></strong><br><?php print $firstPop_data3 ?> </li>
			</ul>
		</span>
	</div>
</div>
<script>
// When the user clicks on div, open the popup
function showGraph() {
	var firstPopup = document.getElementById("firstPop");
	firstPopup.classList.toggle("show");
}
</script>
<ul>
	<h4><?php print $titleItem5 ?></h4>
	<h2><?php print $dataItem5 ?></p></h2>
</ul>
    <div class="card-body">
		<div class="center-block">
		<?php print $listeB ?>
		</div>
    </div>
</div>

<!-- 2 list -->
<div class="card">
	<div class="pull-left">
		<div class="popup" onclick="showGraph()">
		<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title=""><span class="fas fa-info-circle  em088 opacityhigh"></span>
		<span class="popuptext" id="firstPop">
			<h4> Détails des informations / calculs </h4>
			<ul>
				<li><strong><?php print $firstPop_info1 ?></strong><br><?php print $firstPop_data1 ?></li><hr>
				<li><strong><?php print $firstPop_info2 ?></strong><br><?php print $firstPop_data2 ?> </li><hr>
				<li><strong><?php print $firstPop_info3 ?></strong><br><?php print $firstPop_data3 ?> </li>
			</ul>
		</span>
	</div>
</div>
<script>
// When the user clicks on div, open the popup
function showGraph() {
	var firstPopup = document.getElementById("firstPop");
	firstPopup.classList.toggle("show");
}
</script>
<ul>
	<h4><?php print $titleItem6 ?></h4>
	<h2><?php print $dataItem6 ?></p></h2>
</ul>
    <div class="card-body">
		<div class="center-block">
		<?php print $listeB ?>
		</div>
    </div>
</div>


</section>