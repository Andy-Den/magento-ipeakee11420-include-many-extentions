function validateForm2()
{ 
	var x=document.forms["subForm2"]["cm-nkrlkr-nkrlkr"].value;
	var atpos=x.indexOf("@");
	var dotpos=x.lastIndexOf(".");
	var y=document.forms["subForm2"]["cm-name"].value;
	if (y==null || y=="")
	{
	  alert("Name must be filled out");
	  return false;
	}
	if(x==null || x=="")
	{
	  alert("e-mail address must be filled out");
	  return false;	}
	else if (atpos<1 || dotpos<atpos+2 || dotpos+2>=x.length)
	{
	  alert("Not a valid e-mail address");
	  return false;
	}
}