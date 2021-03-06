<?php

if(!defined('VALID_RUN')){
	Header("Location: http://".getenv("HTTP_HOST")."/index.php");
	exit();
}

include ($config['inc_dir'].'tree.class.php'); 

class ForumTree extends Tree
{
	public $moduleName = 'forum';
	public $id_par_name = 'op=showforum&forum';
	public $TopCatName = '�����';
	public $Slach = '/';

	public function ShowPath( $id, $UFU = false, $view_end_url = false )
	{
		global $site;
		$parents = array();
		$parents = $this->GetAllParent($id);
		$parent = $this->GetParentId($id);
		if($parent == 0){
			$burl = 'index.php?name='.$this->moduleName;
		}elseif($parent != 0){
			$burl = 'index.php?name='.$this->moduleName.'&'.$this->id_par_name.'='.$parent;
		}
		$path = '';
		$path .= '<b><a href="index.php?name='.$this->moduleName.'">'.$this->TopCatName.'</a></b>';
		$c = count($parents) - 1;
		for($i = 0; $i < $c; $i++){
			if( !$UFU){
			$path .=  $this->Slach.' <a href="index.php?name='.$this->moduleName.'&'.$this->id_par_name.'='.$parents[$i]['id'].'">'.$parents[$i]['title'].'</a>';
			} else {
			$path .= $this->Slach.' <a href="'.$this->moduleName.'/'.$parents[$i]['id'].'">'.$parents[$i]['title'].'</a>';
			}
		}
			if(!$view_end_url){
				$path .=$this->Slach.$parents[$c]['title'];
			} else {
				$path .= $this->Slach.' <a href="'.$this->moduleName.'/'.$parents[$i]['id'].'">'.$parents[$c]['title'].'</a>';
			}

		$site->AddTextBox('', $path);
	}

  public function ForumCatsData( $tree, $level )
	{
		global $site, $FCatsData;
		if(in_array($tree[$this->IdKey], $this->childs) === false){
			$levs = str_repeat('&nbsp;-&nbsp;', $level);
			$site->DataAdd($FCatsData, $tree[$this->IdKey], $levs.$tree[$this->TitleKey].($this->viewitems && isset($tree[$this->FileCounterKey]) ?($tree[$this->FileCounterKey]>0? ' ('.$tree[$this->FileCounterKey].')' :''): ''), ($tree[$this->IdKey] == $this->sel_id));
		}
	}


	public function GetCatsDataF( $sel_id, $viewitems = false, $root = false, $id = 0, $xor = false )
	{
		global $site, $FCatsData;
		$this->childs = array();
		$this->sel_id = $sel_id;
		$this->viewitems = $viewitems;
		if($xor){
			$this->childs = $this->GetAllChildId($id);
		}
		if($root){
			$site->DataAdd($FCatsData, '0', $this->TopCatName, $sel_id == 0);
		}
		$this->ListingTree(0, array($this, 'ForumCatsData'));
		return $FCatsData;
	}

}

?>