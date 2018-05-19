<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"/>
	<?php
		include("script3.php");
	?>
	<title>
		<?php
			echo title($_GET["id"]);
		?>
		= Snakes =
	</title>
	<link rel="stylesheet" type="text/css" href="/style3.css">
	<script src='https://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML'></script>
	<script type="text/x-mathjax-config">MathJax.Hub.Config({tex2jax: {inlineMath: [['$','$']]}});</script>
	<script src="/jquery-3.2.1.js"></script>
	<link rel="stylesheet" type="text/css" href="/semantic/semantic.css">
	<script src="/semantic/semantic.js"></script>
	<script>
		function locked()
		{
			document.getElementById("tip").innerText="正在解锁，请稍后……";
			document.getElementById("tip").style.opacity=1;
			document.getElementById("tip").className="ui left pointing green basic label";
			var xmlhttp;
			if (window.XMLHttpRequest)
				xmlhttp=new XMLHttpRequest();
			else
				xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			xmlhttp.onreadystatechange=function()
			{
				if (xmlhttp.readyState==4&&xmlhttp.status==200)
					if (xmlhttp.responseText)
					{
						document.getElementById("tip").style.opacity=1;
						document.getElementById("tip").className="ui left pointing red basic label";
						document.getElementById("tip").innerText=xmlhttp.responseText;
					}
					else
						window.location.href="/detail/"+<?php echo $_GET["id"];?>;
			}
			xmlhttp.open("POST","tryunlock.php",true);
			xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
			xmlhttp.send("id="+<?php echo $_GET["id"];?>+"&password="+encodeURIComponent(document.getElementById("password").value));
		}
		function keyDown(e)
		{
			var currKey=0,e=e||event;
			currKey=e.keyCode||e.which||e.charCode;
			if (currKey==13)
				locked();
		}
		document.onkeydown = keyDown;
	</script>
</head>
<body style="margin: 0px;">
	<?php
		include("header.php");
	?>
	<div class="ui main container" style="padding-top: 7em;">
		<div class="ui grid">
			<div class="ui twelve wide column">
			<div class="ui segments">
			<div class="ui top attached blue segment">
			<?php
				if (isadmin($_SESSION["account"]))
					echo "<a href=\"/modify.php?id=".$_GET["id"]."\">\n<div class=\"ui red ribbon label\">\n<i class=\"write icon\"></i>\n</div>\n</a>";
			?>
				<div class="ui medium header">
				<?php
					echo title($_GET["id"]);
				?>
				</div>
			<div class="ui blue right ribbon label">
				<?php
					echo timepost($_GET["id"]);
				?>
			</div>
			</div>
			<div class="ui bottom attached segment" id="maintext">
			<div class="ui form">
				<div class="ui medium header">请输入密钥以查看其内容。</div>
				<div class="field">
					<div class="ui small header">密钥</div>
					<div class="ui left icon input">
						<input type="password" id="password" placeholder="密钥"/>
						<i class="lock icon"></i>
					</div>
				</div>
				<div class="ui blue button" onclick="javascript:locked();">解锁</div>
				<div id="tip" class="ui left pointing green basic label" style="opacity: 0;"></div>
			</div>
			</div>
			</div>
			</div>
			<?php
				include("foot2.php");
			?>
		</div>
	</div>
</body>
</html>
