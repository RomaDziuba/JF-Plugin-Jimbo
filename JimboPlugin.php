<?php 
class JimboPlugin extends ObjectJimboPlugin
{
	public function onInit()
	{
		$this->jimboObject = $this->getObject('Jimbo', true);
	} // end onInit
    
    public function main($table = false, $pluginName = false)
    { 
        global $jimbo;
        
        $sessionData = &$jimbo->getSessionData();
        
        $authData = $jimbo->user->get('auth_data');
        
        if ($pluginName) {
            $path = $this->options['plugins_path'].$pluginName.'/tblDefs/';
            $handlersPath = $this->options['plugins_path'].$pluginName.'/tblHandlers/';
            $jimbo->setOption('defs_path', $path);
            $jimbo->setOption('handlers_path', $handlersPath);
        }
        
        $sessionData['id_dealer'] = $authData['id_dealer'];
        
        $content = $jimbo->getView($jimbo->db, $table);
        // defs_path
        
        $template = $this->getTemplateName();
        
        if (!$template) {
            echo $content;
            exit();
        }
        
        $jimbo->display($content, $template);
        exit();
    } // end main
    
    public function handleDisplayList($event)
    {
        global $jimbo;
        
        $jimbo->setTitle($event->currentTarget['info']['caption']);
    } // end handleDisplayList
    
    
    /**
     * Returns the name of the main template
     */
    private function getTemplateName()
    {
        $template = 'main.ihtml';
        
        if (isset($_GET['popup'])) {
            $template = JIMBO_POPUP_MODE == 'popup' ? 'light.ihtml' : false;
        }
        
        return $template;
    } // end getTemplateName
    
    public function getFile($table, $filed, $id)
    {
        global $jimbo;
    
        $sql = "SELECT ".$jimbo->db->escape($filed)." FROM ".$jimbo->db->escape($table)." 
        WHERE id = ".$jimbo->db->quote($id);
        $info = $jimbo->db->getOne($sql);
        
        $info = explode(";0;", $info);
        $info = array('filename' => $info[0], 'filetype' => $info[1]);
        if (isset($_GET['thumb'])) {
            $fname = FS_ROOT.'storage/'.$table.'/thumbs/'.$id.'_'.$filed;
        } else {
            $fname = FS_ROOT.'storage/'.$table.'/'.$id.'_'.$filed;
        }
        
        if (empty($info['filename']) || (!is_file($fname)) ) {
            header("HTTP/1.0 404 Not Found");
        } else {
            header('Content-Disposition: attachment; filename="'.$info['filename'].'"');
            header("Content-type:".$info['filetype']);
            $fp = fopen($fname, 'rb');
            fpassthru($fp);
        }
        exit();
    } // end getFile

	/**
     * Returns the current jimbo menu
     * 
     * @param string $name id in html container
     * @return string
     */
    public function getMenu($name = 'rootMenu')
    {
        global $jimbo;
        
        if (!$jimbo->user->isLogin()) {
            return false;
        }
        
        $structureMenu = $this->getStructureMenu();
        
        // $tpl = dbDisplayer::getTemplateInstance();
        
        $currentTplPath = $this->tpl->template_dir;
        $this->tpl->template_dir = $jimbo->getOption('engine_tpl_path');
        
        $currentItem = $_SERVER['REQUEST_URI']; 
        $currentItem2 = substr($currentItem, -1, 1) != '/' ? $currentItem.'/' : substr($currentItem, 0, -1);
        $this->tpl->assign("currentItem", $currentItem);
        $this->tpl->assign("currentItem2", $currentItem2);
        
        $this->tpl->assign("name", $name);
        $this->tpl->assign("items", array_values($structureMenu));
        
        $content = $this->tpl->fetch("menu.ihtml");
        
        $this->tpl->template_dir = $currentTplPath;
        
        return $content;
    } // end getMenu
    
    public function getStructureMenu()
    {
    	global $jimbo;
    	
    	$search = array(
    		'p.id_role' => $jimbo->user->getRole()
    	);
    	$orderBy = array(
    		'm.id_parent', 'm.order_n'
    	);
    	$tmp = $this->jimboObject->getMenu($search, $orderBy);

    	$menu = array();
        $parents = array();

        foreach ($tmp as $item) {
            
            $parents[$item['id']] = $item['id_parent'];
            if (empty($item['id_parent'])) {
            
                $menu[$item['id']] = array(
                'caption' => $item['caption'],
                'href' => $jimbo->getUrl($item['url']),
                'level' => 1,
                'items' => array()
                );
            } elseif (isset($menu[$item['id_parent']]['level']) && $menu[$item['id_parent']]['level'] == 1) {
            
                $menu[$item['id_parent']]['items'][$item['id']] = array(
                'caption' => $item['caption'],
                'href' => $jimbo->getUrl($item['url']),
                'level' => 2,
                'id_parent' => $item['id_parent'],
                'items' => array()
                );
            } else {
    
                $parent = $item['id_parent'];
                $top = $parents[$parent];
                $menu[$top]['items'][$parent]['items'][] = array(
                'caption' => $item['caption'],
                'href' => $jimbo->getUrl($item['url'])
                );
            }
        }
        
        return $menu;
    } // end getStructureMenu
}

?>