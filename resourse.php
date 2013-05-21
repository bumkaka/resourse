<?php
/**
Author: Bumkaka from modx.im
Code style: Sith
*/
class resourse {
	static $_instance = null;
	public $id;
	public $field;
	public $tv;
	public $log;
	public $new_resourse;
	public $dafeult_field;
	private $table=array('"'=>'_',"'"=>'_',' '=>'_','.'=>'_',','=>'_','а'=>'a','б'=>'b','в'=>'v',
		'г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z','и'=>'i','й'=>'y','к'=>'k',
		'л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u',
		'ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'sch','ь'=>'','ы'=>'y','ъ'=>'',
		'э'=>'e','ю'=>'yu','я'=>'ya','А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D','Е'=>'E',
		'Ё'=>'E','Ж'=>'Zh','З'=>'Z','И'=>'I','Й'=>'Y','К'=>'K','Л'=>'L','М'=>'M','Н'=>'N',
		'О'=>'O','П'=>'P','Р'=>'R','С'=>'S','Т'=>'T','У'=>'U','Ф'=>'F','Х'=>'H','Ц'=>'C',
		'Ч'=>'Ch','Ш'=>'Sh','Щ'=>'Sch','Ь'=>'','Ы'=>'Y','Ъ'=>'','Э'=>'E','Ю'=>'Yu','Я'=>'Ya',
	);

	private $set;	
	
	public function clear_chache(){
	  $this->modx->clearCache();
      include_once (MODX_MANAGER_PATH . '/processors/cache_sync.class.processor.php');
      $sync = new synccache();
      $sync->setCachepath(MODX_BASE_PATH . "assets/cache/");
      $sync->setReport(false);
      $sync->emptyCache();
	}
	
	public function set($key,$value){$this->field[$key] = $value;}
	
	public function get($key){return $this->field[$key];}
	
	public function Uset($key){$this->set[]=' '.$key.'="'.$this->field[$key].'" ';}
	
	public function save(){
		if ($this->field['pagetitle'] == '') {$this->log[] =  'Pagetitle is empty in <pre>'.print_r($this->field,true).'</pre>';return false;}
		if ($this->field['alias'] == '') $this->field['alias'] = $this->translite($this->field['pagetitle']);

		$fld = $this->field;
		foreach($this->default_field as $key=>$value){
			if ($this->new_resourse) {
				if ($this->field[$key] == '') $this->field[$key] = $this->default_field[$key];
				$this->Uset($key);
			} else {if ($this->field[$key]!='') $this->Uset($key);}
			unset($fld[$key]);
		}
		if (!empty($this->set)){
			$SQL = $this->new_resourse?'INSERT into PREFIX_site_content SET '.implode(', ', $this->set):'UPDATE PREFIX_site_content SET '.implode(', ', $this->set).' WHERE id = '.$this->id;
			$this->query($SQL);
		}
		$id = $this->new_resourse? $this->modx->db->getInsertId():$this->id;
		
		foreach($fld as $key=>$value){
			if ($value=='') continue;
 			if ($this->tv[$key]!=''){
				$result = $this->query('UPDATE PREFIX_site_tmplvar_contentvalues SET `value` = "'.$value.'" WHERE `contentid` = '.$id.' AND `tmplvarid` = '.$this->tv[$key].';');
				$rc = mysql_affected_rows();
				if ($rc==0){
					$result = $this->query('INSERT into PREFIX_site_tmplvar_contentvalues SET `contentid` = '.$id.',`tmplvarid` = '.$this->tv[$key].',`value` = "'.$value.'";');
				}
			}
		}
		return true;
	}
	
	public function list_log(){echo '<pre>'.print_r($this->log,true).'</pre>';}
	
	public function get_TV(){
		$result = $this->query("SELECT id,name FROM `PREFIX_site_tmplvars`");
		while($row = $this->modx->db->GetRow($result)) $this->tv[$row['name']] = $row['id'];
	}
	
	public function translite($alias){return strtr($alias, $this->table);}
	
	public function query($SQL){return $this->modx->db->query(str_replace('PREFIX_',$this->modx->db->config['table_prefix'],$SQL));}
	
	private function __construct(){global $modx;$this->modx = $modx;$this->get_TV();}

	private final function __clone(){throw new Exception('Clone is not allowed');}
	
	static function Instance($id=0){
		if (self::$_instance == NULL){self::$_instance = new self();}
		$self = self::$_instance;
		$self->new_resourse = $id == 0;
		$self->id = $id;
		$self->field=array();
		$self->set=array();
		$self->default_field = array('pagetitle'=>'New document','alias'=>'','parent'=>0,'createdon'=>time(),'createdby'=>'0','editedon'=>'0','editedby'=>'0','published'=>'1','deleted'=>'0','hidemenu'=>'1','template'=>'0','content'=>'');
		return $self;
	}
}