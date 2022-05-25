<head>
	<meta charset="UTF-4">
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=no">
	 <link rel="stylesheet" type="text/css" href="tab.css">
	<link rel="stylesheet" type="text/css"  href="https://pixinvent.com/stack-responsive-bootstrap-4-admin-template/app-assets/css/bootstrap-extended.min.css">
	<link rel="stylesheet" type="text/css"  href="https://pixinvent.com/stack-responsive-bootstrap-4-admin-template/app-assets/fonts/simple-line-icons/style.min.css">
	<link rel="stylesheet" type="text/css"  href="https://pixinvent.com/stack-responsive-bootstrap-4-admin-template/app-assets/css/bootstrap.min.css">
	<link href="https://fonts.googleapis.com/css?family=Montserrat&display=swap" rel="stylesheet">
</head>

<body>


	<!-- page content -->
	<section class="page-content">
		<div class="grid-container-4">
			<div class="grid-1">
				<div class="card bg-c-blue order-card">
					<div class="card-body">
					<div class="pull-right">
						<div class="popup" onclick="myFunction()">
						<span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title=""><span class="fas fa-info-circle  em088 opacityhigh" style=" vertical-align: middle; cursor: help"></span>
							<span class="popuptext" id="myPopup">
								<h4> Détails des informations / calculs </h4>
							<ul>
								<li>Le calcul du CA : <strong>Somme du "total_ht" de toutes les factures clients sur l'année courante (hors brouillon) </strong> </li><hr>
								<li>Le calcul du CA N-1 : <strong>Somme du "total_ht" de toutes les factures clients sur l'année précédente (hors brouillon)</strong></li><hr>
								<li>La progression : <strong> ( (VA - VD) / VD ) x 100 </strong></li>
							</ul>
							</span>
						</div>
						</div>
						<script>
							// When the user clicks on div, open the popup
							function myFunction() {
								var popup = document.getElementById("myPopup");
								popup.classList.toggle("show");
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
			<!-- end bloc 1 -->

			<!-- bloc 1 -->
			<div class="grid-1">
				<div class="card bg-c-blue order-card">
					<!-- Corps de la carte -->
					<div class="card-body">
					<div class="pull-right"><span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title=""><span class="fas fa-info-circle  em088 opacityhigh" style=" vertical-align: middle; cursor: help"></span></div>
						<!-- <div class="card-block"> -->
						<h4 class="text-center">
							<?php print $titleItem2 ?>
						</h4>
						<h1 class="text-center">
							<?php print $dataItem2 ?>

						</h1>
						<hr>
						<div class="col-lg-14">
  							<div class="center-block">
   		 						<div class="pull-left"><?php print $info3 ?> : <h4 class="center"><?php print $dataInfo3 ?></h4></div>
								<div class="pull-right"><?php print $info4 ?> : <h4 class="center"><?php print $dataInfo4 ?></h4></div>
							</div>
							</div>
						</div>
   		 					<div><?php print $graphiqueB . "\n"?></div>
							<div><?php print $graphiqueB1 ?></div>
							<!-- <div><?php print $graphiqueB2 . "\n"?></div>
							<div><?php print $graphiqueB3 ?></div> -->
						</div>
					</div>
			<!-- end bloc 1 -->

			<!-- bloc 1 -->
			<div class="grid-1">
				<div class="card bg-c-blue order-card">
					<!-- Corps de la carte -->
					<div class="card-body">
					<div class="pull-right"><span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title=""><span class="fas fa-info-circle  em088 opacityhigh" style=" vertical-align: middle; cursor: help"></span></div>
						<!-- <div class="card-block"> -->
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
			<!-- end bloc 1 -->



			<!-- end bloc 1 -->


				<!-- bloc 1 -->
				<div class="grid-1">
					<div class="card bg-c-blue order-card">
						<!-- Corps de la carte -->
						<div class="card-body">
						<div class="pull-right"><span class="classfortooltip" style="padding: 0px; padding: 0px; padding-right: 3px !important;" title=""><span class="fas fa-info-circle  em088 opacityhigh" style=" vertical-align: middle; cursor: help"></span></div>
							<!-- <div class="card-block"> -->
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
				</div>
			<!-- end bloc 1 -->








