function SetPPVisible() 
{
	if(document.news_editor.programme.checked)
	{
		document.all('programingtable').style.display = "block";
		document.all('programingtable').style.visibility = "visible";
		document.all('leftcoll').innerHTML = "&nbsp;";
	}
	else
	{
		document.all('programingtable').style.display = "none";
		document.all('programingtable').style.visibility = "hidden";
		document.all('leftcoll').innerHTML = "";
	}
}