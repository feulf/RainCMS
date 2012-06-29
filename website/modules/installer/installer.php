<?php

class InstallerModule extends Module {

    function index() {
        
        $installed_modules = Content::get_module_list();
        
        $tpl = new View;
        $tpl->assign("installed_modules", $installed_modules );
        $tpl->draw("installer/installer");
    }

}

// -- end