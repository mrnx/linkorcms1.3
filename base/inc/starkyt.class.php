<?php

# LinkorCMS
# � 2006-2010 �������� ��������� ���������� (galitsky@pochta.ru)
# ����: starkyt.class.php
# ����������: ����� ��� ���������� �������� LinkorCMS

if(!defined('VALID_RUN')){
	header("HTTP/1.1 404 Not Found");
	exit;
}

include ($config['inc_dir'].'html.class.php'); //class HTML

class Starkyt extends HTML
{
	public $Root = ''; // ��� ����� � ������������� ���������, � ��������� ������
	public $DefaultRoot = ''; // ��� ����� � ��������� �� ���������
	public $TemplateFile = ''; // ��� ����� � ����������� � ����� �������, � �������� ���������� ����������
	public $TableOpen = '';
	public $TableClose = '';
	public $TableCellOpen = '';
	public $TableCellClose = '';
	public $BOpenKey = '{';
	public $BCloseKey = '}';
	public $Blocks = array();
	public $SBlocks = array();
	public $EnvBlocks = array();
	public $EnvAliases = array();
	public $EnvChilds = array();
	public $EnvVarBlocks = array();
	public $Cache = array();
	public $compileFile;
	public $lastCompileFile;
	public $compileLine;

	// �����������
	public function InitStarkyt($Root, $TemplateFile)
	{
		$this->Source = '';
		$this->SetRoot($Root);
		$this->TemplateFile = $TemplateFile;
		$tfile = $Root.$TemplateFile;
		if(!is_file($tfile)) {
			echo 'Starkyt: �� ������� ����� ������������ ���� �������: '.$tfile.'<br />';
			exit();
		} else {
			$this->SBlocks = $this->GetSBlocks($TemplateFile);
		}
		// ��������� ���� ������������ {comment}�����������{/comment}
		$this->AddBlock('comment', false);
	}

	public function SetRoot($Root)
	{
		//if(is_dir($Root)){
		$this->Root = $Root;
		//} else {
		//	echo 'Starkyt: �� ������� ����� ������� �������� "'.$root.'".<br />';
		//	exit;
		//}
	}

	/**
	 * ���������, ���������� �� ������
	 */
	public function TemplateExists( $TemplateFile )
	{
		if($TemplateFile == ''){
			return false;
		}
		if(is_file($this->Root.$TemplateFile)){
			return $this->Root.$TemplateFile;
		}elseif(is_file($this->DefaultRoot.$TemplateFile)){
			return $this->DefaultRoot.$TemplateFile;
		}else{
			return false;
		}

	}

	public function SetTableTemplate($table_open, $table_close, $table_cell_open, $table_cell_close)
	{
		$this->TableOpen = file_get_contents($this->TemplateExists($table_open));
		$this->TableClose = file_get_contents($this->TemplateExists($table_close));
		$this->TableCellOpen = file_get_contents($this->TemplateExists($table_cell_open));
		$this->TableCellClose = file_get_contents($this->TemplateExists($table_cell_close));
	}

	// ��������� ������ � ��������� �� �����
	public function GetSBlocks($templateFile)
	{
		$blocks2 = array();
		if($templateFile == ''){
			return $blocks2;
		}
		if(isset($this->Cache[$templateFile])) {
			return $this->Cache[$templateFile];
		}

		$filename = $this->TemplateExists($templateFile);
		if($filename === false){
			echo '�� ������� ����� ������: '.$templateFile;
			return $blocks2;
		}

		global $config, $site, $user, $db;
		ob_start();
		include($filename);
		$source = ob_get_clean();

		//$source = file_get_contents($filename);

		$blocks = explode($this->BOpenKey, $source);
		$blocks2 = array($blocks[0]);
		for($i = 1, $cnt = count($blocks); $i < $cnt; $i++) {
			$tag = explode($this->BCloseKey, $blocks[$i]);
			$blocks2[] = $tag[0];
			if(isset($tag[1])){
				$blocks2[] = $tag[1];
			} else {
				$blocks2[] = '';
			}
		}
		$this->Cache[$templateFile] = $blocks2;

		return $blocks2;
	}

	public function CreateBlock($if = true, $poly = false, $alias = '', $templatefile = '', $plaintext = '', $child = array())
	{
		if($poly) {
			$type = 'poly';
		} else {
			$type = 'block';
		}
		$new = array(
			'type' => $type,
			'alias' => $alias,
			'sub' => array(),
			'if' => $if,
			'template' => $templatefile,
			'plaintext' => $plaintext
		);
		if(!$poly) { // ������� ��������� ��� �����
			$new['vars'] = array(); // ����������, ������ ������� ����� �������� ��������������� �����.
			$new['tempvars'] = array(); // ����������, ������ ������� ����� ��������� ��������������� �������
			$new['template'] = $templatefile; // ��� ����� ������� �����
			$new['plaintext'] = $plaintext; // ����� �����
			$new['child'] = $child; // ��������, �������� � ��� �����, � ������� ���������, ������� ���������.
		}
		// ��������� �������
		$this->GetSBlocks($templatefile);
		return $new;
	}

	public function CreateSubBlock($if = true, $vars = array(), $tempvars = array(), $template = '', $plaintext = '', $child = array())
	{
		$block = array(
			'vars' => $vars,
			'if' => $if,
			'tempvars' => $tempvars,
			'template' => $template,
			'plaintext' => $plaintext,
			'child' => $child
		);
		$this->GetSBlocks($template);
		foreach($tempvars as $temp){
			$this->GetSBlocks($temp);
		}
		return $block;
	}

	public function CreateTable($if = true, $alias = '', $cols = 5, $template = '', $plaintext = '')
	{
		$table = array(
			'if' => $if,
			'alias' => $alias,
			'type' => 'table',
			'cols' => $cols,
			'plaintext' => $plaintext,
			'template' => $template
		);
		$this->GetSBlocks($template);
		return $table;
	}

	public function CreateTableCell($if = true, $vars = array(), $tempvars = array(), $template = '', $plaintext = '', $colspan = 1, $rowspan = 1, $child = array())
	{
		$table = array(
			'if' => $if,
			'vars' => $vars,
			'tempvars' => $tempvars,
			'template' => $template,
			'plaintext' => $plaintext,
			'colspan' => $colspan,
			'rowspan' => $rowspan,
			'child' => $child
		);
		$this->GetSBlocks($template);
		foreach($tempvars as $temp){
			$this->GetSBlocks($temp);
		}
		return $table;
	}

	// ������� ��� �������� ������
	public function AddBlock($name, $if = true, $poly = false, $alias = '', $templatefile = '', $plaintext = '', $child = array())
	{
		$this->Blocks[$name] = $this->CreateBlock($if, $poly, $alias, $templatefile, $plaintext, $child);
		$this->GetSBlocks($templatefile);
	}

	public function AddSubBlock($name, $if = true, $vars = array(), $tempvars = array(), $template = '', $plaintext = '', $child = array())
	{
		$this->Blocks[$name]['sub'][] = $this->CreateSubBlock($if, $vars, $tempvars, $template, $plaintext, $child);
		$this->GetSBlocks($template);
		foreach($tempvars as $temp){
			$this->GetSBlocks($temp);
		}
		return (count($this->Blocks[$name]['sub']) - 1);
	}

	// ������� ��� �������� ������
	public function AddTable($name, $if = true, $alias = '', $cols = 5, $template = '', $plaintext = '')
	{
		$this->Blocks[$name] = $this->CreateTable($if, $alias, $cols, $template, $plaintext);
		$this->GetSBlocks($template);
	}

	public function AddTableCell($name, $if = true, $vars = array(), $tempvars = array(), $template = '', $plaintext = '', $colspan = 1, $rowspan = 1, $child = array())
	{
		$this->Blocks[$name]['sub'][] = $this->CreateTableCell($if, $vars, $tempvars, $template, $plaintext, $colspan, $rowspan, $child);
		$this->GetSBlocks($template);
		foreach($tempvars as $temp){
			$this->GetSBlocks($temp);
		}
		return (count($this->Blocks[$name]['sub']) - 1);
	}

	public function SetVar($block, $varname, $value, $sub_id = 0)
	{
		if(isset($this->Blocks[$block])) {
			if($this->Blocks[$block]['type'] == 'poly' || $this->Blocks[$block]['type'] == 'table') {
				$this->Blocks[$block]['sub'][$sub_id]['vars'][$varname] = $value;
			} else {
				$this->Blocks[$block]['vars'][$varname] = $value;
			}
		}
	}

	public function SetTempVar($block, $varname, $template_file, $sub_id = 0)
	{
		if(isset($this->Blocks[$block])){
			if($this->Blocks[$block]['type'] == 'poly' || $this->Blocks[$block]['type'] == 'table') {
				$this->Blocks[$block]['sub'][$sub_id]['tempvars'][$varname] = $template_file;
			} else {
				$this->Blocks[$block]['tempvars'][$varname] = $template_file;
			}
			$this->GetSBlocks($template_file);
		}
	}

	// ����������� ���� ������
	public function Compile()
	{
		$this->level = 0;
		return Trim($this->Compiler($this->SBlocks));
	}

	public function GetVar($blockname, $varname, $parentBlock)
	{
		$VAR = '';
		if(isset($this->EnvBlocks[$blockname])) {
			$bname = $blockname;
		} elseif(isset($this->EnvBlocks[$this->EnvAliases[$blockname]])) {
			$bname = $this->EnvAliases[$blockname];
		} else {
			return '';
		}
		if(isset($this->EnvBlocks[$bname]['vars'][$varname])) {
			$VAR = $this->EnvBlocks[$bname]['vars'][$varname];
		} elseif(isset($this->EnvBlocks[$bname]['tempvars'][$varname])) {
			$VAR = $this->Compiler($this->GetSBlocks($this->EnvBlocks[$bname]['tempvars'][$varname]), $parentBlock);
		}
		return $VAR;
	}

	public function Analyze($cmd, $findclose)
	{
		$r = array('find' => false, 'type' => 'untyped', 'en' => true, 'op' => 'none');
		if($cmd[0] == '/'){
			$op = 'close';
			$cmd = substr($cmd, 1);
			if(isset($this->EnvVarBlocks[$cmd])) {
				$r = array('find' => true, 'type' => 'block', 'en' => $this->EnvVarBlocks[$cmd], 'op' => $op, 'lname' => $cmd);
				unset($this->EnvVarBlocks[$cmd]);
			} else {
				$r = array('find' => true, 'type' => 'block', 'en' => true, 'op' => $op, 'lname' => $cmd);
			}
			return $r;
		} else {
			$op = 'open';
		}
		if($findclose){
			return $r;
		}
		if(isset($this->Blocks[$cmd]) && // ����
			!isset($this->EnvBlocks[$cmd])) // ������ ��������� ���� 2 ����
		{ // ���� ����
			$r = array('find' => true, 'type' => 'block', 'child' => false, 'en' => $this->Blocks[$cmd]['if'], 'op' => $op, 'lname' => $cmd);
			$this->EnvBlocks[$cmd] = &$this->Blocks[$cmd];
			if($this->Blocks[$cmd]['alias'] != ''){
				$this->EnvAliases[$this->Blocks[$cmd]['alias']] = $cmd; // ����� �����
			}
			// ���������� �������� �����
			if(isset($this->Blocks[$cmd]['child'])){
				$childs = array_keys($this->Blocks[$cmd]['child']);
				for($i = 0, $c = count($childs); $i < $c; $i++) {
					$this->EnvChilds[$childs[$i]] = $this->Blocks[$cmd]['child'][$childs[$i]];
				}
			}
		}elseif(isset($this->EnvBlocks[$cmd])){
			$r = array('find' => false, 'type' => 'untyped', 'child' => false, 'en' => true, 'op' => $op, 'lname' => $cmd, 'env' => true);
			// �������� �������� ����� ����� ��������������
		}elseif(isset($this->EnvChilds[$cmd])){ // ���� �������� ����
			$this->EnvBlocks[$cmd] = &$this->EnvChilds[$cmd];
			if($this->EnvBlocks[$cmd]['alias'] != ''){
				$this->EnvAliases[$this->EnvBlocks[$cmd]['alias']] = $cmd; // ����� �����
			}
			$r = array('find' => true, 'type' => 'block', 'child' => true, 'en' => true, 'op' => $op, 'lname' => $cmd);
			// ���������� �������� �����
			if(isset($this->EnvBlocks[$cmd]['child'])){
				$childs = array_keys($this->EnvBlocks[$cmd]['child']);
				for($i = 0, $c = count($childs); $i < $c; $i++){
					$this->EnvChilds[$childs[$i]] = &$this->EnvBlocks[$cmd]['child'][$childs[$i]];
				}
			}
		}else{ // ���� ������� ���������
			$tag = explode('.', $cmd);
			if(count($tag) == 2){
				$type = 'var';
			}else{
				$tag = explode(':', $cmd);
				if(count($tag) == 2){
					$type = 'block';
				}else{
					$r['env2'] = true;
					return $r;
				}
			}
			if(isset($this->EnvBlocks[$tag[0]])){
				$lname = $tag[0];
			}elseif(isset($this->EnvAliases[$tag[0]])){
				$lname = $this->EnvAliases[$tag[0]];
			}else{
				$r['env2'] = true;
				return $r;
			}
			if(isset($this->EnvBlocks[$lname]['vars'][$tag[1]]) || isset($this->EnvBlocks[$lname]['tempvars'][$tag[1]])){
				$rname = $tag[1];
			}else{
				$r['env2'] = true;
				return $r;
			}
			if($type == 'var'){ // ����������
				$r = array('find' => true, 'type' => 'var', 'lname' => $lname, 'rname' => $rname);
			}else{ // ���� ������������� ��� �������� ����������
				$this->EnvVarBlocks[$cmd] = GetBoolValue($this->EnvBlocks[$lname]['vars'][$rname]);
				$r = array('find' => true, 'type' => 'block', 'en' => true, 'op' => $op, 'lname' => $cmd);
			}
		}
		return $r;
	}

	public function Compiler($TBlocks, $parentBlock = '')
	{
		$t2b = array();

		for($e = 0, $y = count($TBlocks); $e < $y; $e++) {
			$val = $TBlocks[$e];
			$val = str_replace("\r", '&#13', $val);
			$val = str_replace("\n", '&#10', $val);
			$t2b[] = $val;
		}

		$command = true;
		$findStart = false; // ������ ����� ����� � ����� ����� ��� �����
		$findEndName = ''; // ��� ���������� �����
		$subBlocks = array(); // �������� ������� ���� � ��������� �����
		$resultText = ''; // ��������� ������� ����������

		for($i = 0, $cnt = count($TBlocks); $i < $cnt; $i++) {
			$command = !$command;
			if($findStart){
				$subBlocks[] = &$TBlocks[$i];
			}
			if($command){ // ��������� �������
				$cmd = $this->Analyze($TBlocks[$i], $findStart); // ������ �������
				// ����� ������� ������ ���������� ������ ������ �� ����� EnvBlocks
				// ��� ������� ��� �������� ����� ������������� ������������ � EnvChilds
				if(!$findStart && $cmd['find'] && $cmd['type'] == 'block' && $cmd['op'] == 'open'){
					// ������� ������ ������ �����
					$findStart = true;
					$findEndName = $cmd['lname']; // ��� ���������� �����, �� ��� ��������� � $EnvBlocks
					continue; // ����� ���� ���� �� ������ ����� ����� �����
				} elseif($findStart && $cmd['find'] && $cmd['type'] == 'block' && $cmd['op'] == 'close' && $cmd['lname'] == $findEndName) {
					// ������ ����� �����
					// ����� �������� � ������
					$findStart = false;
					unset($subBlocks[count($subBlocks) - 1]); // ��� �������� ����������� ��� �����
					// ���������� ��� �����
					if(isset($this->EnvBlocks[$findEndName])){
						// ���� ��� ���� ������������� �������������
						$blockType = $this->EnvBlocks[$findEndName]['type'];
						$isTable = ($blockType == 'table');
						if($isTable) {
							$TableCols = $this->EnvBlocks[$findEndName]['cols'];
							$coll = 0;
							$rowspan = array();
						}
					}else{ // ���� ��� �������� ���� � ��������� ���������� � �������
						$blockType = 'block';
						$isTable = false;
					}
					if($blockType == 'poly' || $isTable) {
						// ���� ��� ������������� ���� ��� �������
						if(!isset($this->EnvBlocks[$findEndName]['sub'])) {
							$this->EnvBlocks[$findEndName]['sub'] = array();
						}
						if($isTable) { // ��������� ����� �������
							$resultText .= $this->TableOpen."\n";
						}
						$BlockBuffer = $this->EnvBlocks[$findEndName];
						for($j = 0, $c = count($this->EnvBlocks[$findEndName]['sub']); $j < $c; $j++) {
							// ������������ ��������
							if($this->EnvBlocks[$findEndName]['sub'][$j]['if']) {
								// ���� ���� ������� �������
								// ���������� ��� ����� �������� � ��� ��� � ������� ������
								$this->EnvBlocks[$findEndName]['vars'] = &$this->EnvBlocks[$findEndName]['sub'][$j]['vars'];
								$this->EnvBlocks[$findEndName]['tempvars'] = &$this->EnvBlocks[$findEndName]['sub'][$j]['tempvars'];
								$this->EnvBlocks[$findEndName]['template'] = &$this->EnvBlocks[$findEndName]['sub'][$j]['template'];
								$this->EnvBlocks[$findEndName]['plaintext'] = &$this->EnvBlocks[$findEndName]['sub'][$j]['plaintext'];
								$this->EnvBlocks[$findEndName]['child'] = &$this->EnvBlocks[$findEndName]['sub'][$j]['child'];
								if($isTable) { // ��������� ����� ������ �������
									if($coll == 0) {
										$resultText .= "<tr>\n";
									}
									$colspan = $this->EnvBlocks[$findEndName]['sub'][$j]['colspan'];
									$rs = $this->EnvBlocks[$findEndName]['sub'][$j]['colspan'];
									$tcopen = $this->TableCellOpen."\n";
									$tcopen = str_replace('{colspan}', ($colspan > 1 ? ' colspan="'.$colspan.'"' : ''), $tcopen);
									$tcopen = str_replace('{rowspan}', ($rs > 1 ? ' rowspan="'.$rs.'"' : ''), $tcopen);
									$resultText .= $tcopen;
								}
								// ��������� ����� ������ ������������ �� �����������
								if($this->EnvBlocks[$findEndName]['sub'][$j]['plaintext'] != '') {
									// ����� ��������
									$resultText .= $this->EnvBlocks[$findEndName]['sub'][$j]['plaintext'];
									$compile = false;
								} elseif($BlockBuffer['plaintext'] != '') { // ����� �����
									$resultText .= $BlockBuffer['plaintext'];
									$compile = false;
								} elseif($this->EnvBlocks[$findEndName]['sub'][$j]['template'] != '') { // ������ ��������
									// ���������� �������� ����� ��������
									$tempBlocks = $this->GetSBlocks($this->EnvBlocks[$findEndName]['sub'][$j]['template']);
									$compile = true;
								} elseif($BlockBuffer['template'] != '') {
									// ������ �����
									$tempBlocks = $this->GetSBlocks($BlockBuffer['template']);
									$compile = true;
								} else {
									// ����������� ����� ����� � �������
									$tempBlocks = &$subBlocks;
									$compile = true;
								}
								if($compile) {
									// ���������� �������� ����� ��������
									$childs = array();
									$childs = array_keys($this->EnvBlocks[$findEndName]['sub'][$j]['child']);
									for($k = 0, $c2 = count($childs); $k < $c2; $k++) {
										$this->EnvChilds[$childs[$k]] = &$this->EnvBlocks[$findEndName]['sub'][$j]['child'][$childs[$k]];
									}
									$resultText .= $this->Compiler($tempBlocks, $findEndName);
									// ������� �������� ����� ��������
									for($k = 0; $k < $c2; $k++) {
										unset($this->EnvChilds[$childs[$k]]);
									}
									unset($tempBlocks);
								}
								///// ��������������� ����� /////
								if($parentBlock != '' && $this->EnvBlocks[$parentBlock]['alias'] != '') {
									$this->EnvAliases[$this->EnvBlocks[$parentBlock]['alias']] = $parentBlock;
								}
								/////
								if($isTable) {
									$Cm = count($rowspan);
									$coll = $coll + $colspan + $Cm;
									for($Am = 0; $Am < $Cm; $Am++) {
										if($rowspan[$Am] == 1) {
											unset($rowspan[$Am]);
										} else {
											$rowspan[$Am]--;
										}
									}
									if($coll == $TableCols) {
										$resultText .= "</tr>\n";
										$coll = 0;
									}
									if($rs > 1) {
										$rowspan[] = $rs;
									}
									$resultText .= $this->TableCellClose."\n";
								}
							}
						}
						if($isTable) {
							if($coll != 0) {
								$resultText .= "</tr>\n";
							}
							$resultText .= $this->TableClose;
						}
						$subBlocks = array();
					} else {
						if(isset($this->EnvBlocks[$findEndName])) {
							if($this->EnvBlocks[$findEndName]['if']) {
								if($this->EnvBlocks[$findEndName]['plaintext'] != '') {
									$resultText .= $this->EnvBlocks[$findEndName]['plaintext'];
								} else {
									if($this->EnvBlocks[$findEndName]['template'] != '') {
										$subBlocks = $this->GetSBlocks($this->EnvBlocks[$findEndName]['template']);
									}
									$resultText .= $this->Compiler($subBlocks, $findEndName);
								}
								///// ��������������� ����� /////
								if($parentBlock != '' && $this->EnvBlocks[$parentBlock]['alias'] != '') {
									$this->EnvAliases[$this->EnvBlocks[$parentBlock]['alias']] = $parentBlock;
								}
								/////
							}
						} else {
							if($cmd['en'] === true) {
								$resultText .= $this->Compiler($subBlocks);
							}
						}
						$subBlocks = array();
					}
					//////////////////
					if(isset($this->EnvBlocks[$findEndName])){
						//������� ��� �������� �����
						if($this->EnvBlocks[$findEndName]['type'] == 'block') {
							$childs = array_keys($this->EnvBlocks[$findEndName]['child']);
							for($k = 0, $c = count($childs); $k < $c; $k++) {
								unset($this->EnvChilds[$childs[$k]]);
							}
						}
						//������� �����
						unset($this->EnvAliases[$this->EnvBlocks[$findEndName]['alias']]);
						// ������� ��� ����
						unset($this->EnvBlocks[$findEndName]);
					}
					continue;
				}elseif(!$findStart && $cmd['type'] == 'var'){ // ���� ����������
					$resultText .= $this->GetVar($cmd['lname'], $cmd['rname'], $parentBlock);
				}elseif(!$cmd['find'] && !isset($cmd['env']) && isset($cmd['env2'])){
					$resultText .= $this->BOpenKey.$TBlocks[$i].$this->BCloseKey;
				}
			}elseif(!$findStart) { // ���� �� ��������
				$resultText .= $TBlocks[$i];
			}
		}
		return $resultText;
	}
}

?>