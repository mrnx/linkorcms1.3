<?php

# LinkorCMS
# © 2006-2010 Галицкий Александр Николаевич (galitsky@pochta.ru)
# Файл:			inc/html.class.php
# Назначение:	Класс вывода элементов управления html

class HTML
{

	public $echoed;
	public $start_str;
	public $end_str;
	public $text;

	public function Html()
	{
		$this->echoed = false;
		$this->start_str = '';
		$this->end_str = '';
		$this->text = '';
	}

	public function EchoText()
	{
		echo $this->text;
	}

	public function ResetText()
	{
		$this->text = '';
	}

	public function FEcho( $text, $echo_terminal_str = true )
	{
		if($echo_terminal_str){
			$text = $this->start_str.$text.$this->end_str;
		}
		$this->text .= $text;
		if($this->echoed){
			echo $text;
		}
	}

	public function FormOpen( $name = '', $action = '', $method = 'post', $enctype = '', $other = '' )
	{
		$text = "\n<form".($name != '' ? " name=\"$name\"" : '').($action != '' ? " action=\"$action\"" : '').($method != '' ? " method=\"$method\"" : '').($enctype != '' ? " enctype=\"$enctype\"" : '').($other != '' ? ' '.$other : '').">\n";
		$this->FEcho($text, false);
		return $text;
	}

	public function Submit( $caption = 'Submit', $other = '' )
	{
		$text = "<input type=\"submit\" value=\"$caption\" align=\"middle\"".($other != '' ? ' '.$other : '').">\n";
		$this->FEcho($text);
		return $this->start_str.$text.$this->end_str;
	}

	public function Button( $caption = 'Button', $other = '' )
	{
		$text = "<input type=\"button\" value=\"$caption\" align=\"middle\"".($other != '' ? ' '.$other : '').">\n";
		$this->FEcho($text);
		return $this->start_str.$text.$this->end_str;
	}

	public function Edit( $name, $text = '', $password = false, $other = '' )
	{
		$text = "<input type=\"".($password ? 'password' : 'text')."\" name=\"$name\"".($text != '' ? ' value="'.$text.'"' : '').($other != '' ? ' '.$other : '').">\n";
		$this->FEcho($text);
		return $this->start_str.$text.$this->end_str;
	}

	public function TextArea( $name, $text = '', $other = '' )
	{
		$text = "<textarea name=\"$name\"".($other != '' ? ' '.$other : '').">$text</textarea>\n";
		$this->FEcho($text);
		return $this->start_str.$text.$this->end_str;
	}

	public function Select_open( $name, $multiple = false, $other = '' )
	{
		$text = "<select name=\"$name\"".($multiple ? ' multiple="multiple"' : '').($other != '' ? ' '.$other : '').">\n";
		$this->FEcho($text, false);
		return $text;
	}

	public function Option( $name, $caption, $selected = false, $other = '' )
	{
		$text = "<option value=\"$name\"".($selected ? ' selected="selected"' : '').($other != '' ? ' '.$field['other'] : '').'>'.$caption."</option>\n";
		$this->FEcho($text, false);
		return $text;
	}

	public function Select_close()
	{
		$text = "</select>\n";
		$this->FEcho($text, false);
		return $text;
	}

	public function Select( $name, $data, $multiple = false, $other = '' )
	{
		//$data = array(['name']['caption']['selected']['other'])
		if(!isset($data['selected'])){
			$data['selected'] = '';
		}
		$text = "<select name=\"$name\"".($multiple ? ' multiple="multiple"' : '').($other != '' ? ' '.$other : '').">\n";
		foreach($data as $field){
			if(is_array($field)){
				$text .= '<option value="'.$field['name'].'"'.($field['selected'] || $data['selected'] == $field['name'] ? ' selected="selected"' : '').($field['other'] ? ' '.$field['other'] : '').'>'.$field['caption']."</option>\n";
			}
		}
		$text .= "</select>\n";
		$this->FEcho($text);
		return $this->start_str.$text.$this->end_str;
	}

	public function DataAdd( &$data, $name, $caption, $selected = false, $other = '' )
	{
		$data[] = array('name'=>$name, 'caption'=>$caption, 'selected'=>$selected, 'other'=>$other);
	}

	public function Hidden( $name, $value = '', $other = '' )
	{
		$text = "<input type=\"hidden\" name=\"$name\" value=\"$value\"".($other != '' ? ' '.$other : '').">\n";
		$this->FEcho($text, false);
		return $text;
	}

	public function Check( $name, $value, $checked = false, $other = '' )
	{
		$text = "<input type=\"checkbox\" name=\"$name\" value=\"$value\"".($checked ? ' checked="checked"' : '').($other != '' ? ' '.$other : '').">\n";
		$this->FEcho($text);
		return $this->start_str.$text.$this->end_str;
	}

	public function Radio( $name, $value, $checked = false, $other = '' )
	{
		$text = "<input type=\"radio\" name=\"$name\" value=\"$value\"".($checked ? ' checked="checked"' : '').($other != '' ? ' '.$other : '').">\n";
		$this->FEcho($text);
		return $this->start_str.$text.$this->end_str;
	}

	public function FFile( $name, $other = '' )
	{
		$text = "<input type=\"file\" name=\"$name\"".($other ? ' '.$other : '').">\n";
		$this->FEcho($text);
		return $this->start_str.$text.$this->end_str;
	}

	public function FormClose()
	{
		$text = "</form>\n";
		$this->FEcho($text, false);
		return $text;
	}
}

?>