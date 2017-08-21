<?php
namespace Home\Model;
use Think\Model;
class OrganModel extends Model
{
	/**
	*获取企业信息
	* $where 条件 array
	* $field 字段 string
	*/
	public function organList($where,$field)
	{
		$sql = "select {$field} from tp_organ as organ
		 where {$where} order by organ.organ_id desc";
		 $row = $this->query($sql);
		 return $row;
	}
	/**
	*获取机构信息
	* $where 条件 array
	*/
	public function sonList($where)
	{
		$sql = "select organ.*,province.province,city.city,level.level_name,type.type_name,parent.organ_name as organ_parent from tp_organ as organ
		 left join tp_organ_level as level on organ.organ_level = level.level_id
		 left join tp_organ_type as type on organ.organ_type = type.type_id
		 left join tp_organ as parent on organ.parent_organ_id = parent.organ_id
		 left join tp_provinces as province on organ.organ_province = province.p_id
		 left join tp_cities as city on organ.organ_city = city.c_id
		 where {$where} order by organ.organ_id desc";
		 $row = $this->query($sql);
		 return $row;
	}

}