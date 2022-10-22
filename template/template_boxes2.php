<?php
include('../template/template_admin.php');
?>
<!-- grid-based layout -->
<div class="grid-container-4">
	<div class="grid-1">
		<div class="card bg-c-blue order-card">
			<div class="card-body">
				<div class="pull-left">
					<div class="popup" onclick="showGraph()">
						<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title=""><span class="fas fa-info-circle  em088 opacityhigh"></span>
							<span class="popuptext" id="firstPop">
								<h4> Détails des informations / calculs </h4>
								<ul>
									<li><strong><?php print $firstPop_info1 ?></strong><br><?php print $firstPop_data1 ?></li>
									<hr>
									<li><strong><?php print $firstPop_info2 ?></strong><br><?php print $firstPop_data2 ?> </li>
									<hr>
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
				<h4 class="text-center">
					<?php print $titleItem1 ?>
				</h4>
				<h1 class="text-center">
					<?php print $dataItem1 ?>
				</h1>
				<hr>
				<div class="col-lg-14">
					<div class="center-block">
						<div class="pull-left"><?php print $info1 ?> : <h4 class="center"><?php print $dataInfo1 ?></h4>
						</div>
						<div class="pull-right"><?php print $info2 ?> : <h4 class="center"><?php print $dataInfo2 ?></h4>
						</div>
					</div>
				</div>
			</div>
			<?php print $graphiqueA ?>
		</div>
	</div>


	<!-- bloc 1 -->
	<div class="grid-1">
		<div class="card bg-c-blue order-card">
			<!-- Corps de la carte -->
			<div class="card-body">
				<div class="pull-left">
					<div class="popup" onclick="showGraph2()">
						<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title=""><span class="fas fa-info-circle  em088 opacityhigh"></span>
							<span class="popuptext" id="secondPop">
								<h4> Détails des informations / calculs </h4>
								<ul>
									<li><strong><?php print $secondPop_info1 ?></strong><br><?php print $secondPop_data1 ?></li>
									<hr>
									<li><strong><?php print $secondPop_info2 ?></strong><br><?php print $secondPop_data2 ?> </li>
									<hr>
									<li><strong><?php print $secondPop_info3 ?></strong><br><?php print $secondPop_data3 ?> </li>
								</ul>
							</span>
					</div>
				</div>
				<script>
					// When the user clicks on div, open the popup
					function showGraph2() {
						var secondPopup = document.getElementById("secondPop");
						secondPopup.classList.toggle("show");
					}
				</script>
				<h4 class="text-center">
					<?php print $titleItem2 ?>
				</h4>
				<h1 class="text-center">
					<?php print $dataItem2 ?>
				</h1>
				<hr>
				<div class="col-lg-14">
					<div class="center-block">
						<div class="pull-left"><?php print $info3 ?> : <h4 class="center"><?php print $dataInfo3 ?></h4>
						</div>
						<div class="pull-right"><?php print $info4 ?> : <h4 class="center"><?php print $dataInfo4 ?></h4>
						</div>
					</div>
				</div>
			</div>
			<?php print $graphiqueB ?>
		</div>
	</div>
