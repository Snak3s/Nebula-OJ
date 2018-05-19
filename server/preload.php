<?php
	if ($_GET["nostyle"]==1)
	{
		require("Saabaru/settings.php");
		require(constant("appname")."/"."script3.php");
		require(constant("appname")."/".$_GET["link"]);
		exit;
	}
?>
<!DOCTYPE HTML>
<html lang="zh-CN">
<head>
	<?php
		require("Saabaru/settings.php");
		require(constant("appname")."/"."script3.php");
		require(constant("appname")."/".$_GET["link"].".conf");
	?>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=0.6, maximum-scale=0.6, user-scalable=no"/>
	<title>
		<?php
			echo $title." - ".constant("apptitle");
		?>
	</title>
	<script src='https://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML'></script>
	<script type="text/x-mathjax-config">MathJax.Hub.Config({tex2jax: {inlineMath: [['$','$']]}});</script>
	<script src="/resources/jquery-3.2.1.js"></script>
	<link rel="stylesheet" type="text/css" href="/resources/semantic.min.css">
	<script src="/resources/semantic.min.js"></script>
	<link rel="stylesheet" type="text/css" href="/resources/style3.css">
	<style>
		.ui.menu a.item, .ui.menu a.item:hover, .ui.menu a.item:active, .ui.menu a.item:focus
		{
			color: #FFFFFF;
		}
		#filter
		{
			background-image: url("/resources/title.png");
			background-size: 100% auto;
			color: #FFFFFF;
			border-left: 0;
			border-right: 0;
			filter: opacity(50%);
			-webkit-filter:  opacity(50%);
		}
	</style>
</head>
<body style="margin: 0px;">
	<?php
		require(constant("appname")."/"."header");
	?>
	<div class="ui main container" style="padding-top: 7em;">
		<div class="ui grid">
			<div class="ui twelve wide column">
				<?php
					require(constant("appname")."/".$_GET["link"]);
				?>
			</div>
			<div class="ui four wide column">
				<?php
					require(constant("appname")."/"."foot");
				?>
			</div>
		</div>
	</div>
</body>
</html>
