
    <head>
        <meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<link rel="stylesheet" type="text/css" href="tab.css">
		<link rel="stylesheet" type="text/css" href="https://pixinvent.com/stack-responsive-bootstrap-4-admin-template/app-assets/css/bootstrap-extended.min.css">
		<link rel="stylesheet" type="text/css" href="https://pixinvent.com/stack-responsive-bootstrap-4-admin-template/app-assets/fonts/simple-line-icons/style.min.css">
		<link rel="stylesheet" type="text/css" href="https://pixinvent.com/stack-responsive-bootstrap-4-admin-template/app-assets/css/bootstrap.min.css">
		<link href="https://fonts.googleapis.com/css?family=Montserrat&display=swap" rel="stylesheet">
	</head>

<body>


<!-- page content -->
	<section class="page-content">
		<!-- grid-based layout -->
			<div class="grid-container-4">

			<!-- bloc 1 -->
				<div class="grid-1">
					<div class="card bg-c-blue order-card">
						<!-- Corps de la carte -->
						<div class="card-body">
							<div class="card-block">
								<h4 class="text-left">
									<?php print $titleItem1 ?> <!-- Titre de la boxe -->
								</h4>
								<h1 class="text-left"><span id="info">
								<i class="bi bi-graph-up"></i>
									<?php
										print $dataItem1;  // Donnée chiffré à afficher
									?>
									</span></h1>
									<p class="text-left">Total  : <span class="f-center"><?php print $currentData1 ?> €</span></p> <!-- Total de la donnée courante -->
									<p class="text-left"><?php print $info1 ?> : <?php print $dataInfo1?>€ </p> <!-- Donnée par rapport à l'année ou au mois dernier -->
									<p class="text-left"><?php print $info2 ?><?php print $dataInfo2?> % </p> <!-- Progression -->
								 </div>
							</div>
						</div>
					</div>
					<!-- end bloc 1 -->

					<!-- bloc 2 -->
					<div class="grid-2">
					<div class="card bg-c-blue order-card">
						<!-- Corps de la carte -->
						<div class="card-body">
							<div class="card-block">
								<h4 class="text-left">
									<?php print $titleItem2 ?>
								</h4>
								<h1 class="text-left"><span id="info">
								<i class="bi bi-graph-up"></i>
									<?php
										print $dataItem2;
									?>
									</span></h1>
									<p class="text-left">Total : <span class="f-center"><?php print $currentData2 ?> €</span></p>
									<p class="text-left"><?php print $info3 ?> : <?php print $dataInfo3?>€ </p>
									<p class="text-left"><?php print $info4 ?>  : <?php print $dataInfo4?> € </p>
								 </div>
							</div>
						</div>
					</div>
					<!-- end bloc 2 -->

						<!-- bloc 3 -->
						<div class="grid-3">
						<div class="card bg-c-blue order-card">
							<!-- Corps de la carte -->
							<div class="card-body">
								<div class="card-block">
									<h4 class="text-left">
										<?php print $titleItem3 ?>
									</h4>
									<h1 class="text-left"><span id="warning">
										<?php
											print $dataItem3;
										?>
										</span></h1>
										<p class="text-left">Total : <span class="f-center"><?php print $currentData3 ?> €</span></p>
										<p class="text-left"><?php print $info5 ?> : <?php print $dataInfo5?>€ </p>
										<p class="text-left"><?php print $info6 ?>  : <?php print $dataInfo6?> € </p>
									</div>
								</div>
							</div>
						</div>
						<!-- end bloc 3 -->

						<div class="grid-3">
						<div class="card bg-c-blue order-card">
							<!-- Corps de la carte -->
							<div class="card-body">
								<div class="card-block">
									<h4 class="text-left">
										<?php print $titleItem4 ?>
									</h4>
									<h1 class="text-left"><span id="info">
										<?php
											print $dataItem4;
										?>
										</span></h1>
										<p class="text-left">Total : <span class="f-center"><?php print $currentData4 ?> €</span></p>
										<p class="text-left"><?php print $info7 ?> : <?php print $dataInfo7 ?>€ </p>
										<p class="text-left"><?php print $info8 ?>  : <?php print $dataInfo8?> € </p>
									</div>
								</div>
							</div>
						</div>
						<!-- end bloc 3 -->

