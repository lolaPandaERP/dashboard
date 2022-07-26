<?php
include('../template/template_admin.php');
?>

<!-- GRILLE STRUCTURE HTML -->
	<section class="page-content">

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
						<h4 class="text-center">
							<?php print $titleItem1 ?>
						</h4>
						<h1 class="text-center">
							<?php print $dataItem1 ?>
						</h1>
						<hr>
						<div class="col-lg-14">
  							<div class="center-block">
   		 						<div class="pull-left"><?php print $info1 ?> : <h4 class="center"><?php print $dataInfo1 ?></h4></div>
								<div class="pull-right"><?php print $info2 ?> : <h4 class="center"><?php print $dataInfo2 ?></h4></div>
							</div>
							</div>
						</div>
							<?php print $graphiqueA ?>
					</div>
				</div>

			<div class="grid-1">
				<div class="card bg-c-blue order-card">
					<!-- Corps de la carte -->
					<div class="card-body">
					<div class="pull-left">
					<div class="popup" onclick="showPop2()">
						<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title=""><span class="fas fa-info-circle  em088 opacityhigh"></span>
							<span class="popuptext" id="secondPop">
								<h4> Détails des informations / calculs </h4>
							<ul>
								<li><strong><?php print $secondPop_info1 ?></strong><br><?php print $secondPop_data1 ?></li><hr>
								<li><strong><?php print $secondPop_info2 ?></strong><br><?php print $secondPop_data2 ?> </li><hr>
								<li><strong><?php print $secondPop_info3 ?></strong><br><?php print $secondPop_data3 ?> </li>
							</ul>
							</span>
						</div>
						</div>
						<script>
							// When the user clicks on div, open the popup
							function showPop2() {
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
   		 						<div class="pull-left"><?php print $info3 ?> : <h4 class="center"><?php print $dataInfo3 ?></h4></div></a>
								<div class="pull-right"><?php print $info4 ?> : <h4 class="center"><?php print $dataInfo4 ?></h4></div></a>
							</div>
							</div>
						</div>
						<div class="customerGraph" id="d1">
							<?php
								print $graphiqueB;
								print $graphiqueB1;
							 ?>
							  Année :
							<?php
								if($_GET['mode'] == 'customer'){

									if($_GET['filter'] == '2021') {
										?><a id='anneeN-1' href="./overview.php?mode=customer&filter=2021" style="border-bottom :2px solid #29a3a3; background-color: #29a3a3; color: white;">
										<?php print $lastyear ?></a>
										 <a id='anneeN' href="./overview.php?mode=customer&filter=2022">
											<?php print $year ?></a>
										<?php
									} elseif($_GET['filter'] == '2022') {
										?> <a id='anneeN-1' href="./overview.php?mode=customer&filter=2021"><?php print $lastyear ?></a>
										<a id='anneeN' href="./overview.php?mode=customer&filter=2022" style="border-bottom :2px solid #29a3a3; background-color: #29a3a3; color: white;"><?php print $year ?></a>
										<?php
									}
								}
								?>
							</h7>
						</div>
						<div class="graphGraph" id="d2">
							 <?php print $graphiqueB2 ?>
							 <?php print $graphiqueB3 ?>
							 <?php
							 if($_GET['mode'] == 'supplier'){

									if($_GET['filter'] == '2021') {
										?> <a id='anneeN-1' href="./overview.php?mode=supplier&filter=2021" style="border-bottom :2px solid #29a3a3; border-top :2px solid #29a3a3;background-color: #29a3a3; color: white;"><?php print $lastyear ?></a></hr>
										<a id='anneeN' href="./overview.php?mode=supplier&filter=2022"><?php print $year ?></a>
										<?php
									} elseif($_GET['filter'] == '2022') {
										?> <a id='anneeN-1' href="./overview.php?mode=supplier&filter=2021"><?php print $lastyear ?></a></hr>
										<a id='anneeN' href="./overview.php?mode=supplier&filter=2022" style="border-bottom :2px solid #29a3a3; background-color: #29a3a3; color: white;"><?php print $year ?></a>
										<?php
									}
								}
								?>
								</h7>
							</div>
						</div>
					</div>


			<!-- end bloc 1 -->

			<!-- bloc 1 -->
			<div class="grid-1">
				<div class="card bg-c-blue order-card">
					<!-- Corps de la carte -->
					<div class="card-body">
					<div class="pull-left">
					<div class="popup" onclick="showGraph3()">
						<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title=""><span class="fas fa-info-circle  em088 opacityhigh"></span>
							<span class="popuptext" id="myPopup">
								<h4> Détails des informations / calculs </h4>
							<ul>
								<li><strong><?php print $thirdPop_info1 ?></strong><br><?php print $thirdPop_data1 ?></li><hr>
								<li><strong><?php print $thirdPop_info2 ?></strong><br><?php print $thirdPop_data2 ?> </li><hr>
								<li><strong><?php print $thirdPop_info3 ?></strong><br><?php print $thirdPop_data3 ?> </li>
							</ul>
							</span>
						</div>
						</div>
						<script>
							// When the user clicks on div, open the popup
							function showGraph3() {
								var popup = document.getElementById("myPopup");
								popup.classList.toggle("show");
							}
						</script>
						<h4 class="text-center">
							<?php print $titleItem3 ?>
						</h4>
						<h1 class="text-center">
							<?php print $dataItem3 ?>
						</h1>
						<hr>
						<div class="col-lg-14">
  							<div class="center-block">
   		 						<div class="pull-left"><?php print $info5 ?> : <h4 class="center"><?php print $dataInfo5 ?></h4></div>
								<div class="pull-right"><?php print $info6 ?> : <h4 class="center"><?php print $dataInfo6 ?></h4></div>
							</div>
							</div>
						</div>
						<div>
							<?php print $graphiqueC ?>
						</div>
				</div>
			</div>

				<!-- bloc 1 -->
				<div class="grid-1">
					<div class="card bg-c-blue order-card">
						<!-- Corps de la carte -->
						<div class="card-body">
						<div class="pull-left">
						<div class="popup" onclick="showGraph4()">
							<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title=""><span class="fas fa-info-circle  em088 opacityhigh"></span>
								<span class="popuptext" id="fourPop">
									<h4> Détails des informations / calculs </h4>
								<ul>
									<li><strong><?php print $fourPop_info1 ?></strong><br><?php print $fourPop_data1 ?></li><hr>
									<li><strong><?php print $fourPop_info2 ?></strong><br><?php print $fourPop_data2 ?> </li><hr>
									<li><strong><?php print $fourPop_info3 ?></strong><br><?php print $fourPop_data3 ?> </li>
								</ul>
								</span>
							</div>
							</div>
							<script>
								// When the user clicks on div, open the popup
								function showGraph4() {
									var fourPopup = document.getElementById("fourPop");
									fourPopup.classList.toggle("show");
								}
							</script>
							<h4 class="text-center">
								<?php print $titleItem4 ?>
							</h4>
							<h1 class="text-center">
								<?php print $dataItem4 ?>
							</h1>
							<hr>
							<div class="col-lg-14">
								<div class="center-block">
									<div class="pull-left"><?php print $info7 ?> : <h4 class="center"><?php print $dataInfo7 ?></h4></div>
									<div class="pull-right"><?php print $info8 ?> : <h4 class="center"><?php print $dataInfo8 ?></h4></div>
								</div>
								</div>
							</div>
							<div>
								<?php print $graphiqueD ?>
							</div>
						</div>
					</div>

			<!-- end bloc 1 -->








