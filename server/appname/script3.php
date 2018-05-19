<?php
	require("parsedown.php");
	function connect()
	{
		$mysqli=new mysqli();
		$mysqli->connect(constant("dbhost"),constant("dbuser"),constant("dbpsw"),constant("dbname"));
		$mysqli->query("set names utf8;");
		return $mysqli;
	}
	function islogin()
	{
		if (!$_SESSION["account"])
			return false;
		return true;
	}
	function checklogin()
	{
		if (!$_SESSION["account"])
			header("location:/");
	}
	function isadmin($user)
	{
		$mysqli=connect();
		$rs=$mysqli->query("select * from admin where account=\"$user\";");
		$num=mysqli_num_rows($rs);
		$mysqli->close();
		return $num>0;
	}
	function getaid($user)
	{
		$mysqli=connect();
		$rs=$mysqli->query("select aid from admin where account=\"$user\";");
		$num=mysqli_num_rows($rs);
		if (!$num)
			return 0;
		$mysqli->close();
		$row=$rs->fetch_array();
		return $row["aid"];
	}
	function checkadmin()
	{
		if (!isadmin($_SESSION["account"]))
			header("location:/");
	}
	function login($user,$psw)
	{
		$user=addslashes($user);
		$mysqli=connect();
		$rs=$mysqli->query("select * from user where account=\"$user\";");
		$num=mysqli_num_rows($rs);
		if (!$num)
			return "用户不存在。";
		$row=$rs->fetch_array();
		if ($row["password"]==md5($psw))
		{
			$_SESSION["account"]=$row["account"];
			return;
		}
		return "用户名与密码不匹配。";
		$mysqli->close();
	}
	function logout()
	{
		$_SESSION["account"]="";
		header("location:/");
	}
	function signup($user,$psw,$repeatpsw)
	{
		$user=addslashes($user);
		if (strpos($user,"<")!=false)
			return "用户名不合法。";
		if (strpos($user,">")!=false)
			return "用户名不合法。";
		if (strpos($user,"&")!=false)
			return "用户名不合法。";
		if (strpos($user,"+")!=false)
			return "用户名不合法。";
		if (!str_replace(" ","",$user))
			return "用户名不合法。";
		if ($psw=="")
			return "密码不合法。";
		if ($psw!=$repeatpsw)
			return "确认密码不正确。";
		$user=htmlspecialchars($user);
		$mysqli=connect();
		$rs=$mysqli->query("select * from user where account=\"$user\";");
		$num=mysqli_num_rows($rs);
		if ($num)
			return "用户名已注册。";
		$mysqli->query("insert into user (account,password) values (\"$user\",\"".md5($psw)."\");");
		login($user,$psw);
		$mysqli->close();
	}
	function throwerror($message)
	{
		echo "<div class=\"ui negative icon message\">\n";
		echo "<i class=\"remove icon\"></i>\n";
		echo "<div class=\"ui small header\">$message</div>\n";
		echo "<a href=\"javascript:history.go(-1);\">返回上一页</a>\n";
		echo "</div>\n";
	}
	function getpagesnum()
	{
		$mysqli=connect();
		$rs=$mysqli->query("select * from problems order by pid;");
		$num=mysqli_num_rows($rs);
		if ($num==0)
			return 0;
		$row=$rs->fetch_array();
		$tot=0;
		$admin=isadmin($_SESSION["account"]);
		$super=getuid($_SESSION["account"])==1;
		while ($row)
		{
			if (!$admin)
				if (substr(urldecode($row["title"]),0,7)=="hidden:")
				{
					$row=$rs->fetch_array();
					continue;
				}
			if (!$super)
				if (substr(urldecode($row["title"]),0,8)=="limited:")
				{
					$row=$rs->fetch_array();
					continue;
				}
			$tot=$tot+1;
			$row=$rs->fetch_array();
		}
		$mysqli->close();
		return $tot;
	}
	function getprobstatus($user,$pid)
	{
		$mysqli=connect();
		$rs=$mysqli->query("select * from submission where pid=\"$pid\" and account=\"$user\" and result=\"Accepted\";");
		$num=mysqli_num_rows($rs);
		if ($num>0)
			return "green ";
		$rs=$mysqli->query("select * from submission where pid=\"$pid\" and account=\"$user\";");
		$num=mysqli_num_rows($rs);
		$mysqli->close();
		if ($num>0)
			return "red ";
		return "";
	}
	function showproblems($page)
	{
		$mysqli=connect();
		$rs=$mysqli->query("select * from problems order by pid;");
		$num=mysqli_num_rows($rs);
		$pnum=getpagesnum();
		if ($page<=0||$page==null)
			$page=1;
		$page=floor($page);
		if (floor(($pnum+29)/30)<$page)
			$page=floor(($pnum+29)/30);
		$l=30*($page-1)+1;
		$r=30*$page;
		if ($pnum<$l)
			$l=$pnum;
		if ($pnum<$r)
			$r=$pnum;
		if (!$pnum)
		{
			echo "<div class=\"ui segment\">\n糟糕……好像一点东西都没有呢……\n</div>\n";
			$mysqli->close();
			return;
		}
		$row=$rs->fetch_array();
		$tot=0;
		$admin=isadmin($_SESSION["account"]);
		$super=getuid($_SESSION["account"])==1;
		while ($row)
		{
			if (!$super)
			{
				if (substr(urldecode($row["title"]),0,8)=="limited:")
				{
					$row=$rs->fetch_array();
					continue;
				}
			}
			else
			{
				if (substr(urldecode($row["title"]),0,8)=="limited:")
				{
					$row["title"]=substr($row["title"],8,strlen($row["title"]))."<div class=\"ui right floated orange label\" style=\"float: right;\">权限</div>";
					continue;
				}
			}
			if (!$admin)
			{
				if (substr(urldecode($row["title"]),0,7)=="hidden:")
				{
					$row=$rs->fetch_array();
					continue;
				}
			}
			else
			{
				if (substr(urldecode($row["title"]),0,7)=="hidden:")
				{
					$row["title"]=substr($row["title"],7,strlen($row["title"]))."<div class=\"ui right floated red label\" style=\"float: right;\">未公开</div>";
					continue;
				}
				
			}
			$tot=$tot+1;
			if ($tot<$l||$tot>$r)
			{
				$row=$rs->fetch_array();
				continue;
			}
			$i=$row["pid"];
			echo "<div class=\"ui segment\">\n";
			echo "<a href=\"/problem/$i\"><div class=\"ui left ".getprobstatus($_SESSION["account"],$row["pid"])."ribbon label\">$i</div>".$row["title"]."</a>\n";
			echo "</div>\n";
			$row=$rs->fetch_array();
		}
		$mysqli->close();
	}
	function getuid($user)
	{
		$mysqli=connect();
		$rs=$mysqli->query("select uid from user where account=\"$user\";");
		$num=mysqli_num_rows($rs);
		if (!$num)
			return 0;
		$row=$rs->fetch_array();
		$ret=$row["uid"];
		$mysqli->close();
		return $ret;
	}
	function getaccount($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select account from user where uid=\"$id\";");
		$num=mysqli_num_rows($rs);
		if (!$num)
			return;
		$row=$rs->fetch_array();
		$ret=$row["account"];
		$mysqli->close();
		return $ret;
	}
	function getuseraccepted($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$account=getaccount($id);
		$rs=$mysqli->query("select pid from submission where account=\"$account\" and result=\"Accepted\" order by pid;");
		$num=mysqli_num_rows($rs);
		if (!$num)
			return 0;
		$row=$rs->fetch_array();
		$tot=0;
		$last=0;
		while ($row)
		{
			if ($row["pid"]!=$last)
			{
				$last=$row["pid"];
				$tot++;
			}
			$row=$rs->fetch_array();
		}
		$mysqli->close();
		return $tot;
	}
	function probnotexist($id)
	{
		if (!is_numeric($id))
			return true;
		$mysqli=connect();
		$rs=$mysqli->query("select * from problems where pid=\"$id\";");
		$num=mysqli_num_rows($rs);
		$mysqli->close();
		return $num==0;
	}
	function announcementnotexist($id)
	{
		if (!is_numeric($id))
			return true;
		$mysqli=connect();
		$rs=$mysqli->query("select * from announcement where sid=\"$id\";");
		$num=mysqli_num_rows($rs);
		$mysqli->close();
		return $num==0;
	}
	function submissionnotexist($id)
	{
		if (!is_numeric($id))
			return true;
		$mysqli=connect();
		$rs=$mysqli->query("select * from submission where sid=\"$id\";");
		$num=mysqli_num_rows($rs);
		$mysqli->close();
		return $num==0;
	}
	function getprobtitlecode($id)
	{
		if (!is_numeric($id))
			return true;
		$mysqli=connect();
		$rs=$mysqli->query("select title from problems where pid=\"$id\";");
		$row=$rs->fetch_array();
		$mysqli->close();
		return $row["title"];
	}
	function getuseracdetail($id)
	{

		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$account=getaccount($id);
		$rs=$mysqli->query("select pid from submission where account=\"$account\" and result=\"Accepted\" order by pid;");
		$num=mysqli_num_rows($rs);
		if (!$num)
			return;
		$row=$rs->fetch_array();
		$tot=0;
		$last=0;
		while ($row)
		{
			if ($row["pid"]!=$last)
			{
				$last=$row["pid"];
				echo "<a class=\"ui blue label\" href=\"/problem/".$row["pid"]."\">".$row["pid"]."</a>";
			}
			$row=$rs->fetch_array();
		}
		$mysqli->close();
	}
	function getstatusnum()
	{
		$mysqli=connect();
		$rs=$mysqli->query("select * from submission order by sid desc;");
		$num=mysqli_num_rows($rs);
		if ($num==0)
			return 0;
		$row=$rs->fetch_array();
		$tot=0;
		$admin=isadmin($_SESSION["account"]);
		$super=getuid($_SESSION["account"])==1;
		while ($row)
		{
			if (!$admin)
				if (substr(getprobtitlecode($row["pid"]),0,7)=="hidden:")
				{
					$row=$rs->fetch_array();
					continue;
				}
			if (!$super)
				if (substr(getprobtitlecode($row["pid"]),0,8)=="limited:")
				{
					$row=$rs->fetch_array();
					continue;
				}
			$tot=$tot+1;
			$row=$rs->fetch_array();
		}
		$mysqli->close();
		return $tot;
	}
	function getsubmissioninfo($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$account=getaccount($id);
		$rs=$mysqli->query("select * from submission where account=\"$account\" and result=\"Accepted\";");
		$num=mysqli_num_rows($rs);
		echo $num.',';
		$rs=$mysqli->query("select * from submission where account=\"$account\" and result=\"Wrong Answer\";");
		$num=mysqli_num_rows($rs);
		echo $num.',';
		$rs=$mysqli->query("select * from submission where account=\"$account\" and result=\"Time Limit Exceeded\";");
		$num=mysqli_num_rows($rs);
		echo $num.',';
		$rs=$mysqli->query("select * from submission where account=\"$account\" and result=\"Memory Limit Exceeded\";");
		$num=mysqli_num_rows($rs);
		echo $num.',';
		$rs=$mysqli->query("select * from submission where account=\"$account\" and result=\"Runtime Error\";");
		$num=mysqli_num_rows($rs);
		echo $num.',';
		$rs=$mysqli->query("select * from submission where account=\"$account\" and result=\"Compile Error\";");
		$num=mysqli_num_rows($rs);
		echo $num.',';
		$rs=$mysqli->query("select * from submission where account=\"$account\" and result=\"No Test Data\";");
		$num=mysqli_num_rows($rs);
		echo $num;
		$mysqli->close();
	}
	function colored($str)
	{
		if ($str=="Accepted")
			return "<div class=\"ui green label\">Accepted</div>";
		if ($str=="Wrong Answer")
			return "<div class=\"ui red label\">Wrong Answer</div>";
		if ($str=="Time Limit Exceeded")
			return "<div class=\"ui orange label\">Time Limit Exceeded</div>";
		if ($str=="Memory Limit Exceeded")
			return "<div class=\"ui orange label\">Memory Limit Exceeded</div>";
		if ($str=="Runtime Error")
			return "<div class=\"ui yellow label\">Runtime Error</div>";
		if ($str=="Compile Error")
			return "<div class=\"ui cyan label\">Compile Error</div>";
		if ($str=="No Test Data")
			return "<div class=\"ui brown label\">No Test Data</div>";
		if ($str=="Compiling")
			return "<div class=\"ui blue label\">Compiling</div>";
		if ($str=="Running")
			return "<div class=\"ui blue label\">Running</div>";
		if ($str=="Pending")
			return "<div class=\"ui gray label\">Pending</div>";
	}
	function showstatus($page)
	{
		$mysqli=connect();
		$rs=$mysqli->query("select * from submission order by sid desc;");
		$num=mysqli_num_rows($rs);
		$pnum=getstatusnum();
		if ($page<=0||$page==null)
			$page=1;
		$page=floor($page);
		if (floor(($pnum+29)/30)<$page)
			$page=floor(($pnum+29)/30);
		$l=30*($page-1)+1;
		$r=30*$page;
		if ($pnum<$l)
			$l=$pnum;
		if ($pnum<$r)
			$r=$pnum;
		if (!$pnum)
		{
			echo "<tr>\n<td colspan='6'>糟糕……好像一点东西都没有呢……</td>\n</tr>\n";
			$mysqli->close();
			return;
		}
		$row=$rs->fetch_array();
		$tot=0;
		$admin=isadmin($_SESSION["account"]);
		$super=getuid($_SESSION["account"])==1;
		while ($row)
		{
			if (!$admin)
				if (substr(getprobtitlecode($row["pid"]),0,7)=="hidden:")
				{
					$row=$rs->fetch_array();
					continue;
				}
			if (!$super)
				if (substr(getprobtitlecode($row["pid"]),0,8)=="limited:")
				{
					$row=$rs->fetch_array();
					continue;
				}
			$tot=$tot+1;
			if ($tot<$l||$tot>$r)
			{
				$row=$rs->fetch_array();
				continue;
			}
			echo "<tr><td>#".$row["sid"]."</td><td><a href=\"/problem/".$row["pid"]."/\">".getprobtitlecode($row["pid"])."</a></td><td>".$row["account"]."</td><td>".colored($row["result"])."</td><td>".$row["time"]."</td><td><a href=\"/source/".$row["sid"]."/\">".$row["lang"]."</a></td></tr>\n";
			$row=$rs->fetch_array();
		}
		$mysqli->close();
	}
	function showposts()
	{
		$mysqli=connect();
		$rs=$mysqli->query("select * from announcement order by sid desc;");
		$num=mysqli_num_rows($rs);
		$row=$rs->fetch_array();
		while ($row)
		{
			if (substr(urldecode($row["title"]),0,7)=="hidden:")
			{
				$row=$rs->fetch_array();
				continue;
			}
			if (substr(urldecode($row["title"]),0,9)=="password[")
			{
				$row=$rs->fetch_array();
				continue;
			}
			$i=$row["sid"];
			echo "<div class=\"ui segment\">\n";
			echo "<a href=\"/detail/$i\">".$row["title"]."</a>\n";
			echo "</div>\n";
			$row=$rs->fetch_array();
		}
		$mysqli->close();
	}
	function showcomment($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select * from comment where postid=$id;");
		$num=mysqli_num_rows($rs);
		if (!$num)
		{
			echo "<div class=\"ui attached blue segment\">\n这里还没有留言，快来留一条吧～\n</div>\n";
			$mysqli->close();
			return;
		}
		$row=$rs->fetch_array();
		while ($row)
		{
			$Parsedown=new parsedown();
			$i=$row["cid"];
			echo "<div class=\"ui attached blue segment\">\n";
			echo "<div class=\"ui small header\">\n".$row["account"]."\n";
			if (isadmin($_SESSION["account"]))
				echo "<a href=\"javascript:deletecomment($i);\">\n<div class=\"ui red circular label\">\n<i class=\"remove icon\" style=\"margin-right: 0;\"></i>\n</div>\n</a>\n";
			echo "</div>\n";
			echo "<div class=\"ui sub header\" style=\"margin-top: -1em;\">\n".$row["time"]."\n</div>\n</div>\n";
			echo "<div class=\"ui attached segment\" id=\"show$i\">\n".$Parsedown->text($row["body"])."\n</div>\n";
			$row=$rs->fetch_array();
		}
		$mysqli->close();
	}
	function getusersexual($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select sex from user where uid=$id;");
		$row=$rs->fetch_array();
		if ($row["sex"]!=1)
			echo "<i class=\"man icon\"></i>";
		else
			echo "<i class=\"woman icon\"></i>";
		$mysqli->close();
	}
	function getmotto($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select motto from user where uid=$id;");
		$row=$rs->fetch_array();
		$mysqli->close();
		return $row["motto"];
	}
	function viewmotto($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select motto from user where uid=$id;");
		$row=$rs->fetch_array();
		$mysqli->close();
		$Parsedown=new parsedown();
		return $Parsedown->text($row["motto"]);
	}
	function setmotto($id,$motto)
	{
		if (!is_numeric($id))
			return;
		if (getuid($_SESSION["account"])!=$id)
			return;
		$motto=htmlspecialchars($motto);
		$mysqli=connect();
		$mysqli->query("update user set motto=\"".$motto."\" where uid=$id;");
		$mysqli->close();
	}
	function changesex($id)
	{
		if (!is_numeric($id))
			return;
		if (getuid($_SESSION["account"])!=$id)
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select sex from user where uid=$id;");
		$row=$rs->fetch_array();
		if ($row["sex"]!=1)
			$set=1;
		else
			$set=0;
		$mysqli->query("update user set sex=\"".$set."\" where uid=$id;");
		$mysqli->close();
		header("location:/user/".$id."/".$set);
	}
	function showuser()
	{
		$mysqli=connect();
		$rs=$mysqli->query("select * from user;");
		$row=$rs->fetch_array();
		while ($row)
		{
			$trs=$mysqli->query("select * from admin where account=\"".$row["account"]."\";");
			$num=mysqli_num_rows($trs);
			if (!$num)
				echo "<a href=\"/add/".$row["uid"]."\">\n<div class=\"ui labeled button\"><div class=\"ui blue icon button\"><i class=\"plus icon\"></i></div><div class=\"ui blue basic label\">".$row["account"]."</div></div>\n</a>\n";
			$row=$rs->fetch_array();
		}
		$mysqli->close();
	}
	function getprobtimelimit($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select timelimit from problems where pid=\"$id\";");
		$row=$rs->fetch_array();
		$mysqli->close();
		return $row["timelimit"];
	}
	function getprobmemorylimit($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select memorylimit from problems where pid=\"$id\";");
		$row=$rs->fetch_array();
		$mysqli->close();
		return $row["memorylimit"];
	}
	function getsolver($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select account from submission where sid=\"$id\";");
		$row=$rs->fetch_array();
		$mysqli->close();
		return $row["account"];
	}
	function viewsource($id)
	{
		if (!is_numeric($id))
			return;
		if ($_SESSION["account"]!=getsolver($id)&&!isadmin($_SESSION["account"]))
			return "= =||";
		$mysqli=connect();
		$rs=$mysqli->query("select program from submission where sid=\"$id\";");
		$row=$rs->fetch_array();
		$mysqli->close();
		return $row["program"];
	}
	function judgewaiting()
	{
		if (!isadmin($_SESSION["account"]))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select * from submission where result=\"Pending\";");
		$row=$rs->fetch_array();
		if ($row)
			echo "<div id=\"sid\">".$row["sid"]."</div>\n<div id=\"pid\">".$row["pid"]."</div>\n<div id=\"timelimit\">".getprobtimelimit($row["pid"])."</div>\n<div id=\"memorylimit\">".getprobmemorylimit($row["pid"])."</div>\n<div id=\"program\">".base64_encode($row["program"])."</div><div id=\"lang\">".$row["lang"]."</div>";
		$mysqli->close();
	}
	function judgepull($sid,$status)
	{
		if (!isadmin($_SESSION["account"]))
			return;
		$mysqli=connect();
		if ($status==1)
			$status="Accepted";
		if ($status==2)
			$status="Wrong Answer";
		if ($status==3)
			$status="Time Limit Exceeded";
		if ($status==4)
			$status="Memory Limit Exceeded";
		if ($status==5)
			$status="No Test Data";
		if ($status==6)
			$status="Compile Error";
		if ($status==7)
			$status="Compiling";
		if ($status==8)
			$status="Running";
		if ($status==9)
			$status="Pending";
		if ($status==10)
			$status="Runtime Error";
		$mysqli->query("update submission set result=\"$status\" where sid=$sid;");
		$mysqli->close();
	}
	function showtitle()
	{
		$mysqli=connect();
		$rs=$mysqli->query("select * from announcement order by sid desc;");
		$num=mysqli_num_rows($rs);
		if (!$num)
			return;
		$row=$rs->fetch_array();
		while ($row)
		{
			$i=$row["sid"];
			echo "<a href=\"/detail/$i\">\n<div class=\"ui attached segment\">\n<div class=\"ui small header\">".$row["title"]."</div><div class=\"ui tiny grey header\">".$row["time"]." | ".$row["account"]."\n</div></div>\n</a>\n";
			$row=$rs->fetch_array();
		}
		$mysqli->close();
	}
	function addadmin($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		checklogin();
		checkadmin();
		if (getuid($_SESSION["account"])!=1)
			header("location:/");
		$rs=$mysqli->query("select account from user where uid=$id;");
		$row=$rs->fetch_array();
		$mysqli->query("insert into admin (account) values (\"".$row["account"]."\");");
		header("location:/admin/");
		$mysqli->close();
	}
	function moveadmin($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		checklogin();
		checkadmin();
		if (getuid($_SESSION["account"])!=1)
			header("location:/");
		if ($id!=1)
			$mysqli->query("delete from admin where aid=$id;");
		header("location:/admin/");
		$mysqli->close();
	}
	function showadmin()
	{
		$mysqli=connect();
		$rs=$mysqli->query("select * from admin;");
		$row=$rs->fetch_array();
		while ($row)
		{
			if ($row["aid"]==1)
				echo "<a href=\"/user/".getuid($row["account"])."\"><div class=\"ui labeled button\"><div class=\"ui red icon button\"><i class=\"user icon\"></i></div><div class=\"ui red basic label\">".$row["account"]."</div></div></a>\n";
			else
				echo "<a href=\"/user/".getuid($row["account"])."\"><div class=\"ui labeled button\"><div class=\"ui orange icon button\"><i class=\"user icon\"></i></div><div class=\"ui orange basic label\">".$row["account"]."</div></div></a>\n";
			$row=$rs->fetch_array();
		}
		$mysqli->close();
	}
	function post($title,$text)
	{
		$mysqli=connect();
		if (!islogin())
		{
			echo "身份校验失效啦，重新登录试试吧。";
			return;
		}
		checkadmin();
		if (!$title)
		{
			echo "标题不能为空。";
			return;
		}
		if (!$text)
		{
			echo "内容不能为空。";
			return;
		}
		$text=str_replace("\\","\\\\",$text);
		$text=str_replace("\"","\\\"",$text);
		$mysqli->query("insert into announcement (time,account,title,body) values (\"".date("y/m/d H:i:s")."\",\"".$_SESSION["account"]."\",\"$title\",\"$text\");");
		$mysqli->close();
	}
	function submit($pid,$prog,$lang)
	{
		$mysqli=connect();
		if (!islogin())
		{
			echo "身份校验失效啦，重新登陆试试吧。";
			return;
		}
		$prog=str_replace("\\","\\\\",$prog);
		$prog=str_replace("\"","\\\"",$prog);
		$mysqli->query("insert into submission (time,pid,account,program,lang,result) values (\"".date("y/m/d H:i:s")."\",".$pid.",\"".$_SESSION["account"]."\",\"$prog\",\"$lang\",\"Pending\");");
		$mysqli->close();
	}
	function modify($title,$text,$id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		checklogin();
		checkadmin();
		if (!$title)
		{
			echo "标题不能为空。";
			return;
		}
		if (!$text)
		{
			echo "内容不能为空。";
			return;
		}
		$text=str_replace("\\","\\\\",$text);
		$text=str_replace("\"","\\\"",$text);
		$mysqli->query("update announcement set time=\"".date("y/m/d H:i:s")."\",account=\"".$_SESSION["account"]."\",title=\"$title\",body=\"$text\" where sid=$id;");
		$mysqli->close();
	}
	function process($text)
	{
		$text=str_replace("\\","\\\\",$text);
		$text=str_replace("\"","\\\"",$text);
		return $text;
	}
	function edit($id,$pid,$title,$time,$memory,$statement,$input,$output,$sample,$constraints)
	{
		if ($id==""||!is_numeric($id))
			return "题目编号有误。";
		if ($pid==""||$pid<=0||!is_numeric($pid))
			return "题目编号有误。";
		if ($pid!=$id)
			if (getprobtitlecode($pid)!="")
				return "题目编号重复。";
		if ($time==""||!is_numeric($time))
			return "时间限制有误。";
		if ($memory==""||!is_numeric($memory))
			return "空间限制有误。";
		if (!islogin())
		{
			echo "身份校验失效啦，重新登陆试试吧。";
			return;
		}
		$mysqli=connect();
		checklogin();
		checkadmin();
		if (!$title)
		{
			echo "标题不能为空。";
			return;
		}
		$statement=process($statement);
		$input=process($input);
		$output=process($output);
		$sample=process($sample);
		$constraints=process($constraints);
		if ($id!=0)
		{
			$mysqli->query("update problems set pid=$pid, title=\"$title\", timelimit=$time, memorylimit=$memory, statement=\"$statement\", input=\"$input\", output=\"$output\", sample=\"$sample\", constraints=\"$constraints\" where pid=$id;");
			$mysqli->query("update submission set pid=$pid where pid=$id;");
		}
		else
			$mysqli->query("insert into problems (pid, title, timelimit, memorylimit, statement, input, output, sample, constraints) values ($pid, \"$title\", $time, $memory, \"$statement\", \"$input\", \"$output\", \"$sample\", \"$constraints\");");
		$mysqli->close();
	}
	function deletepost($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		checklogin();
		checkadmin();
		$mysqli->query("delete from announcement where sid=$id;");
		$mysqli->close();
	}
	function deletecomment($id)
	{
		echo "???".$id;
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		checklogin();
		checkadmin();
		$mysqli->query("delete from comment where cid=$id;");
		$mysqli->close();
	}
	function comment($user,$text,$id)
	{
		if (!is_numeric($id))
			return;
		$user=$_SESSION["account"];
		$user=str_replace("<","&lt;",$user);
		$text=str_replace("<","&lt;",$text);
		$mysqli=connect();
		if (!$user)
		{
			echo "昵称不能为空。";
			return;
		}
		if (!$text)
		{
			echo "内容不能为空。";
			return;
		}
		$text=str_replace("\\","\\\\",$text);
		$text=str_replace("\"","\\\"",$text);
		$mysqli->query("insert into comment (time,account,body,postid) values (\"".date("y/m/d H:i:s")."\",\"".$user."\",\"$text\",$id);");
		$mysqli->close();
	}
	function title($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select title from announcement where sid=\"$id\";");
		$row=$rs->fetch_array();
		$mysqli->close();
		if (substr($row["title"],0,7)=="hidden:")
			if (!isadmin($_SESSION["account"]))
				header("location:/detail/0");
		return $row["title"];
	}
	function getprobtitle($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select title from problems where pid=\"$id\";");
		$row=$rs->fetch_array();
		$mysqli->close();
		if (substr($row["title"],0,7)=="hidden:")
			if (!isadmin($_SESSION["account"]))
				header("location:/problem/0");
		if (substr($row["title"],0,8)=="limited:")
			if (getuid($_SESSION["account"])!=1)
				header("location:/problem/0");
		return $row["title"];
	}
	function getprobstatement($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select statement from problems where pid=\"$id\";");
		$row=$rs->fetch_array();
		$mysqli->close();
		$Parsedown=new parsedown();
		return $Parsedown->text($row["statement"]);
	}
	function getprobinput($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select input from problems where pid=\"$id\";");
		$row=$rs->fetch_array();
		$mysqli->close();
		$Parsedown=new parsedown();
		return $Parsedown->text($row["input"]);
	}
	function getproboutput($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select output from problems where pid=\"$id\";");
		$row=$rs->fetch_array();
		$mysqli->close();
		$Parsedown=new parsedown();
		return $Parsedown->text($row["output"]);
	}
	function getprobsample($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select sample from problems where pid=\"$id\";");
		$row=$rs->fetch_array();
		$mysqli->close();
		$Parsedown=new parsedown();
		return $Parsedown->text($row["sample"]);
	}
	function getprobconstraints($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select constraints from problems where pid=\"$id\";");
		$row=$rs->fetch_array();
		$mysqli->close();
		$Parsedown=new parsedown();
		return $Parsedown->text($row["constraints"]);
	}
	function getprobstatementcode($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select statement from problems where pid=\"$id\";");
		$row=$rs->fetch_array();
		$mysqli->close();
		return $row["statement"];
	}
	function getprobinputcode($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select input from problems where pid=\"$id\";");
		$row=$rs->fetch_array();
		$mysqli->close();
		return $row["input"];
	}
	function getproboutputcode($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select output from problems where pid=\"$id\";");
		$row=$rs->fetch_array();
		$mysqli->close();
		return $row["output"];
	}
	function getprobsamplecode($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select sample from problems where pid=\"$id\";");
		$row=$rs->fetch_array();
		$mysqli->close();
		return $row["sample"];
	}
	function getprobconstraintscode($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select constraints from problems where pid=\"$id\";");
		$row=$rs->fetch_array();
		$mysqli->close();
		return $row["constraints"];
	}
	function timepost($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select * from announcement where sid=\"$id\";");
		$row=$rs->fetch_array();
		$mysqli->close();
		return $row["time"]." | ".$row["account"];
	}
	function mdcode($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select body from announcement where sid=\"$id\";");
		$row=$rs->fetch_array();
		$mysqli->close();
		return str_replace("<","&lt;",$row["body"]);
	}
	function mdtext($id)
	{
		if (!is_numeric($id))
			return;
		$mysqli=connect();
		$rs=$mysqli->query("select body from announcement where sid=\"$id\";");
		$row=$rs->fetch_array();
		$mysqli->close();
		$Parsedown=new parsedown();
		return $Parsedown->text(str_replace("<:more:>","",$row["body"]));
	}
	function showuploaded()
	{
		if (!isadmin($_SESSION["account"]))
			return;
		$dir=opendir("upload/");
		if (!$dir)
		{
			echo "<div class=\"ui attached segment\">出错啦！</div>";
			return;
		}
		while (($file=readdir($dir))!==false)
			if ($file!="."&&$file!="..")
				echo "<a href=\"/upload/$file\"><div class=\"ui attached segment\">".$file."</div></a>";
		closedir($dir);
	}
	session_start();
?>
