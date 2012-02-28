function MailToMenu()
{
	window.open('index.php?name=plugins&p=mail',
	'Mail',
	'resizable=yes,scrollbars=yes,menubar=no,status=no,location=no,width=700,height=580,screenX=300,screenY=50');
}
function Admin(afile,page){
	location = afile+'?exe='+page;
}
function admtdover(itemobj){
	itemobj.className = 'admenuitemover';
}
function admtdnorm(itemobj){
	itemobj.className = 'admn';
}
function modtdover(itemobj){
	itemobj.className = 'modmenuitemover';
}
function modtdnorm(itemobj){
	itemobj.className = 'modn';
}

