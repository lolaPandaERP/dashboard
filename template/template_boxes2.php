
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
			<div class="grid-container-2">

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
									<p class="text-left">Total  : <span class="f-center"><?php print $currentData1 ?></span></p> <!-- Total de la donnée courante -->
									<p class="text-left"><?php print $info1 ?> : <?php print $dataInfo1?></p> <!-- Donnée par rapport à l'année ou au mois dernier -->
									<p class="text-left"><?php print $info2 ?><?php print $dataInfo2?></p>
									<div class="element-flexible">
										<canvas id="myChart" width="50" height="20"></canvas>
										<script>
											var ctx = document.getElementById("myChart").getContext("2d");
											var myChart = new Chart(ctx, {
											type: "line",
											data: {
												labels: [
												"Monday",
												"Tuesday",
												"Wednesday",
												"Thursday",
												"Friday",
												"Saturday",
												"Sunday",
												],
												datasets: [
												{
													label: "work load",
													data: [2, 9, 3, 17, 6, 3, 7],
													backgroundColor: "rgba(153,205,1,0.6)",
												},
												{
													label: "free hours",
													data: [2, 2, 5, 5, 2, 1, 10],
													backgroundColor: "rgba(155,153,10,0.6)",
												},
												],
											},
											});
										</script>
									</div>
								 </div>
							</div>
						</div>
					</div>
					<!-- end bloc 1 -->

						<!-- bloc 1 -->
				<div class="grid-2">
					<div class="card bg-c-blue order-card">
						<!-- Corps de la carte -->
						<div class="card-body">
							<div class="card-block">
								<h4 class="text-left">
									<?php print $titleItem2 ?> <!-- Titre de la boxe -->
								</h4>
								<h1 class="text-left"><span id="info">
								<i class="bi bi-graph-up"></i>
									<?php
										print $dataItem2;  // Donnée chiffré à afficher
									?>
									</span></h1>
									<p class="text-left">Total  : <span class="f-center"><?php print $currentData2 ?></span></p> <!-- Total de la donnée courante -->
									<p class="text-left"><?php print $info3 ?> : <?php print $dataInfo3?></p> <!-- Donnée par rapport à l'année ou au mois dernier -->
									<p class="text-left"><?php print $info4 ?><?php print $dataInfo4?></p> <!-- Progression -->
									<div class="element-flexible">
										<canvas id="myChart2" moz-opaque width="50" height="20"></canvas>
										<script>

											var ctx = document.getElementById("myChart2").getContext("2d");
											var myChart = new Chart(ctx, {
											type: "line",
											data: {
												labels: [
												"Monday",
												"Tuesday",
												"Wednesday",
												"Thursday",
												],
												datasets: [
												{
													label: "work load",
													data: [2, 9, 3, 17],
													backgroundColor: "rgba(153,205,1,0.6)",
												},
												{
													label: "free hours",
													data: [2, 2, 5, 5],
													backgroundColor: "rgba(155,153,10,0.6)",
												},
												],
											},
											});
										</script>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- end bloc 1 -->


