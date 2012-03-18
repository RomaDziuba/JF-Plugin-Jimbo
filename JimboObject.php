<?php 
class JimboObject extends Object
{
    public function getMenu($search, $orderBy = array())
    {
    	$sql = "SELECT 
                    m.* 
                FROM 
                    dbdrive_menu_perms p
                    INNER JOIN dbdrive_menu m ON (p.id_menu = m.id)";
    	
    	return $this->select($sql, $search, $orderBy);
    } // end getMenu
}
?>