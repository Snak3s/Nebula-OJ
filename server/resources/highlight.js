function parse(source)
{
	source=source.replace(/&amp;/g,"&");
	source=source.replace(/&lt;/g,"<");
	source=source.replace(/&gt;/g,">");
	var p=new Array(),t=new Array();
	var flag=0,temp=String(""),tempflag=0,ptop=0;
	var result=String("");
	var strop=0,comop=0,hex=0;
	/*flags:
		0: none
		1: op
		2: key
		3: str
		4: num
		5: name
		6: tag
		7: com
		8: declear
		9: func
	*/
	for (var i=0;i<source.length;i++)
	{
		if (flag==0)
		{
			temp=temp+source[i];
			if (source[i]=='+'||source[i]=='-'||source[i]=='*'||source[i]=='/'||source[i]=='%'||source[i]==','||source[i]=='{'||source[i]=='}'||source[i]==';'||source[i]=='?'||source[i]==':'||source[i]=='.')
				flag=1;
			if (source[i]=='/'&&source[i+1]=='*')
			{
				flag=7;
				if (comop==0)
					comop=1;
			}
			if (source[i]=='/'&&source[i+1]=='/')
			{
				flag=7;
				if (comop==0)
					comop=2;
			}
			if (source[i]=='&'||source[i]=='<'||source[i]=='>'||source[i]=='='||source[i]=='|'||source[i]=='!'||source[i]=='~'||source[i]=='^'||source[i]=='('||source[i]==')'||source[i]=='['||source[i]==']'||source[i]=='{'||source[i]=='}')
				flag=1;
			if (source[i]=="\"")
			{
				flag=3;
				if (strop==0&&comop==0)
					strop=1;
			}
			if (source[i]=="\'")
			{
				flag=3;
				if (strop==0&&comop==0)
					strop=2;
			}
			if (source[i]>='0'&&source[i]<='9')
				flag=4;
			if (source[i]=='\n'||source[i]=='\r'||source[i]==' '||source[i]=='\t')
				flag=6;
			if (flag==0)
				flag=5;
			continue;
		}
		tempflag=0;
		if (source[i]=='+'||source[i]=='-'||source[i]=='*'||source[i]=='/'||source[i]=='%'||source[i]==','||source[i]=='{'||source[i]=='}'||source[i]==';'||source[i]=='?'||source[i]==':'||source[i]=='.')
			tempflag=1;
		if (source[i]=='/'&&source[i+1]=='*')
		{
			tempflag=7;
			if (comop==0)
				comop=1;
		}
		if (source[i]=='/'&&source[i-1]=='*')
			if (comop==1)
			{
				temp=temp+source[i];
				p[ptop]=temp;
				t[ptop]=flag;
				temp=String("");
				flag=0;
				ptop++;
				comop=0;
				continue;
			}
		if (source[i]=='/'&&source[i+1]=='/')
		{
			tempflag=7;
			if (comop==0)
				comop=2;
		}
		if (source[i]=='&'||source[i]=='<'||source[i]=='>'||source[i]=='='||source[i]=='|'||source[i]=='!'||source[i]=='~'||source[i]=='^'||source[i]=='('||source[i]==')'||source[i]=='['||source[i]==']'||source[i]=='{'||source[i]=='}')
			tempflag=1;
		if (source[i]=="\"")
		{
			tempflag=3;
			if (strop==1)
			{
				temp=temp+source[i];
				p[ptop]=temp;
				t[ptop]=flag;
				temp=String("");
				flag=0;
				ptop++;
				strop=0;
				continue;
			}
			if (strop==0&&comop==0)
				strop=1;
		}
		if (source[i]=="\'")
		{
			tempflag=3;
			if (strop==2)
			{
				temp=temp+source[i];
				p[ptop]=temp;
				t[ptop]=flag;
				temp=String("");
				flag=0;
				ptop++;
				strop=0;
				continue;
			}
			if (strop==0&&comop==0)
				strop=2;
		}
		if (source[i]>='0'&&source[i]<='9'&&flag!=5)
			tempflag=4;
		if (flag==4&&source[i]=='x'&&source[i-1]=='0'&&temp.length==1&&flag==4&&(('0'<=source[i+1]&&source[i+1]<='9')||('A'<=source[i+1]&&source[i+1]<='F')))
		{
			tempflag=4;
			hex=1;
		}
		if (source[i]=='$'&&(('0'<=source[i+1]&&source[i+1]<='9')||('A'<=source[i+1]&&source[i+1]<='F')))
		{
			p[ptop]=temp;
			t[ptop]=flag;
			temp=source[i];
			flag=4;
			ptop++;
			hex=1;
			continue;
		}
		if (('0'<=source[i]&&source[i]<='9')||('A'<=source[i]&&source[i]<='F'))
			if (hex==1)
				tempflag=4;
		if ((source[i]=='.'||source[i]=='e')&&flag==4)
			tempflag=4;
		if (source[i]=='-'&&source[i-1]=='e'&&flag==4)
			tempflag=4;
		if (tempflag==0)
			tempflag=5;
		if (source[i]=='\n'||source[i]=='\r'||source[i]==' '||source[i]=='\t')
			tempflag=6;
		if (source[i]=='\n'||source[i]=='\r')
			if (comop==2)
			{
				p[ptop]=temp;
				t[ptop]=flag;
				temp=source[i];
				flag=tempflag;
				ptop++;
				comop=0;
				continue;
			}
		if (temp[0]=='#'&&source[i]!='\n'&&source[i]!='\r')
			comop=strop=hex=0;
		if (tempflag==flag||flag==3||flag==7||(temp[0]=='#'&&source[i]!='\n'&&source[i]!='\r'))
			temp=temp+source[i];
		else
		{
			if (temp[0]=='#')
				flag=8;
			if (temp=="int"||
				temp=="long"||
				temp=="continue"||
				temp=="return"||
				temp=="if"||
				temp=="else"||
				temp=="for"||
				temp=="void"||
				temp=="bool"||
				temp=="using"||
				temp=="namespace"||
				temp=="inline"||
				temp=="char"||
				temp=="const"||
				temp=="var"||
				temp=="begin"||
				temp=="end"||
				temp=="real"||
				temp=="float"||
				temp=="double"||
				temp=="string"||
				temp=="unsigned"||
				temp=="do"||
				temp=="while"||
				temp=="register"||
				temp=="break"||
				temp=="goto"||
				temp=="template"||
				temp=="friend"||
				temp=="typedef"||
				temp=="auto"||
				temp=="int64"||
				temp=="dword"||
				temp=="qword"||
				temp=="integer"||
				temp=="longint"||
				temp=="extended"||
				temp=="struct")
				flag=2;
			if (flag==5&&source[i]=='(')
				flag=9;
			p[ptop]=temp;
			t[ptop]=flag;
			temp=source[i];
			flag=tempflag;
			ptop++;
			hex=0;
		}
	}
	if (temp[0]=='#')
		flag=8;
	if (temp=="int"||
		temp=="long"||
		temp=="continue"||
		temp=="return"||
		temp=="if"||
		temp=="else"||
		temp=="for"||
		temp=="void"||
		temp=="bool"||
		temp=="using"||
		temp=="namespace"||
		temp=="inline"||
		temp=="char"||
		temp=="const"||
		temp=="var"||
		temp=="begin"||
		temp=="end"||
		temp=="real"||
		temp=="float"||
		temp=="double"||
		temp=="string"||
		temp=="unsigned"||
		temp=="do"||
		temp=="while"||
		temp=="register"||
		temp=="break"||
		temp=="goto"||
		temp=="template"||
		temp=="friend"||
		temp=="typedef"||
		temp=="auto"||
		temp=="int64"||
		temp=="dword"||
		temp=="qword"||
		temp=="integer"||
		temp=="longint"||
		temp=="extended"||
		temp=="struct")
		flag=2;
	if (flag==5&&source[i]=='(')
		flag=9;
	p[ptop]=temp;
	t[ptop]=flag;
	ptop++;
	for (var i=0;i<ptop;i++)
	{
		p[i]=p[i].replace(/&/g,"&amp;");
		p[i]=p[i].replace(/</g,"&lt;");
		p[i]=p[i].replace(/>/g,"&gt;");
		if (t[i]==0)
			result=result+p[i];
		if (t[i]==1)
			result=result+"<span style='color: #FF80B0;'>"+p[i]+"</span>";
		if (t[i]==2)
			result=result+"<span style='color: #80B0FF; font-style: Italic;'>"+p[i]+"</span>";
		if (t[i]==3)
			result=result+"<span style='color: #FFB080;'>"+p[i]+"</span>";
		if (t[i]==4)
			result=result+"<span style='color: #B080FF;'>"+p[i]+"</span>";
		if (t[i]==5)
			result=result+p[i];
		if (t[i]==6)
			result=result+p[i];
		if (t[i]==7)
			result=result+"<span style='color: #808080;'>"+p[i]+"</span>";
		if (t[i]==8)
			result=result+"<span style='color: #80FFB0;'>"+p[i]+"</span>";
		if (t[i]==9)
			result=result+"<span style='color: #B0FF80;'>"+p[i]+"</span>";
	}
	return result;
}
window.onload=function highlight()
{
	if (!document.getElementsByTagName)
	{
		alert("Error!");
		return;
	}
	var codes=document.getElementsByTagName("code");
	for (var i=0;i<codes.length;i++)
	{
		var source=codes[i].innerHTML;
		var result=parse(source);
		codes[i].innerHTML=result;
	}
}

