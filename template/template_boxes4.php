
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
								<h4 class="text-center">
									<?php print $titleItem1 ?> <!-- Titre de la boxe -->
								</h4>
									<h1 class="text-center">
										<span class="success">
											<?= print $dataItem1?> €
										</span>
									</h1>
									<hr>
									<div class="element">
										<p class="text-right"></p> <!-- Donnée par rapport à l'année ou au mois dernier -->
										<?php print $info1 ?> :
										<?php print $dataInfo1?> € </p>
										<hr class="vertical">
										<p class="text-right"></p> <!-- Donnée par rapport à l'année ou au mois dernier -->
										<?php print $info2 ?> :
										<p class="text-right" id="progress"><?php print $dataInfo2?> % </p>
									</div>
								</div>
							</div>
							<a href="#" class="btn btn-primary">GRAPHIQUE</a>
						</div>
					</div>
					<!-- end bloc 1 -->


					<!-- bloc 2 -->
					<div class="grid-2">
					<div class="card bg-c-blue order-card">
						<!-- Corps de la carte -->
						<div class="card-body">
							<div class="card-block">
								<h4 class="text-center">
									<?php print $titleItem2 ?>
								</h4>
								<h1 class="text-center">
										<span class="success">
											<?= print $dataItem2?> €
										</span>
									</h1>
									<hr>
									<div class="element">
										<p class="text-right"></p> <!-- Donnée par rapport à l'année ou au mois dernier -->
										<?php print $info3 ?> :
										<?php print $dataInfo3?> € </p>
										<hr class="vertical">
										<p class="text-right"></p> <!-- Donnée par rapport à l'année ou au mois dernier -->
										<?php print $info4 ?> :
										<?php print $dataInfo4?> € </p>
									</div>
								</div>
							</div>
							<a href="#" class="btn btn-primary">GRAPHIQUE</a>
						</div>
					</div>
					<!-- end bloc 2 -->

						<!-- bloc 3 -->
						<div class="grid-3">
						<div class="card bg-c-blue order-card">
							<!-- Corps de la carte -->
							<div class="card-body">
								<div class="card-block">
									<h4 class="text-center">

										<?php print $titleItem3 ?>
									</h4>
									<h1 class="text-center">
									<span class="success">
											<?= print $dataItem3?> €
									</span>
									</h1>
									<hr>
									<div class="element">
										<p class="text-right"></p> <!-- Donnée par rapport à l'année ou au mois dernier -->
											<?php print $info5 ?> :
											<?php print $dataInfo5?> € </p>
										<hr class="vertical">
										<p class="text-right"></p> <!-- Donnée par rapport à l'année ou au mois dernier -->
											<?php print $info6 ?> :
											<?php print $dataInfo6?> € </p>
									</div>
								</div>
							</div>
							<a href="#" class="btn btn-primary">GRAPHIQUE</a>
						</div>
					</div>
						<!-- end bloc 3 -->

						<div class="grid-3">
						<div class="card bg-c-blue order-card">
							<!-- Corps de la carte -->
							<div class="card-body">
								<div class="card-block">
									<h4 class="text-center">
										<?php print $titleItem4 ?>
									</h4>
									<h1 class="text-center">
										<span class="success">
											<?= print $dataItem4?> €
										</span>
									</h1>
									<hr>
									<div class="element">
										<p class="text-right"></p> <!-- Donnée par rapport à l'année ou au mois dernier -->
											<?php print $info7 ?> :
											<?php print $dataInfo?> € </p>
										<hr class="vertical">
										<p class="text-right"></p> <!-- Donnée par rapport à l'année ou au mois dernier -->
											<?php print $info8 ?> :
											<?php print $dataInfo8?> € </p>
									</div>
								</div>
							</div>
							<a href="#" class="btn btn-primary">GRAPHIQUE</a>
						</div>
					</div>
						<!-- end bloc 3 -->

