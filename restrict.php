<?php
// Restrict extension, https://github.com/GiovanniSalmeri/yellow-restrict

class YellowRestrict {
    const VERSION = "0.8.16";
    public $yellow;         //access to API
    
    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("restrictUserFile", "restrict.ini");
    }

    // Handle page meta data
    public function onParseMeta($page) {
        if ($page->isExisting("restrict")) {
            $page->set("Description", $this->yellow->language->getTextHtml("RestrictDescription"));
        }
    }
    
    // Handle page layout
    public function onParsePageLayout($page, $name) {
        $masterPage = $page;
        while ($masterPage and !$masterPage->isExisting("restrict")) {
            $parentPage = $masterPage->getParent();
            $homeLocation = $this->yellow->content->getHomeLocation($masterPage->getLocation());
            if (!$parentPage && $masterPage->getLocation()!==$homeLocation) {
                $masterPage = $this->yellow->content->find($homeLocation); // home as parent of top pages
            } else {
                $masterPage = $parentPage;
            }
        }
        $allowedPeople = $masterPage ? $masterPage->get("restrict") : null;
        if ($allowedPeople) {
            if (!$this->matchPermissions($this->yellow->toolbox->getServer('PHP_AUTH_USER'), $this->yellow->toolbox->getServer('PHP_AUTH_PW'), $allowedPeople)) {
                $this->yellow->page->setHeader("WWW-Authenticate", 'Basic realm="", charset="UTF-8"');
                $this->yellow->page->error("401");
            } else {
                $page->set("RestrictUser", $this->yellow->toolbox->getServer('PHP_AUTH_USER'));
            }
        }
    }

    // Handle page extra data
    public function onParsePageExtra($page, $name) {
        $output = null;
        if ($name=="logout") {
            if ($page->isExisting("restrict")) {
                $output .= "<p>".str_replace("@user", "<b>".htmlspecialchars($page->get("RestrictUser"))."</b>", $this->yellow->language->getTextHtml("RestrictLogged"))."</p>\n";
            }
        }
        return $output;
    }

    // Check whether user is allowed
    function matchPermissions($givenUser, $givenPassword, $allowedPeople) {
        $fileNameRestrict = $this->yellow->system->get("coreExtensionDirectory").$this->yellow->system->get("restrictUserFile");
        $lines = @file($fileNameRestrict);
        if ($lines) foreach ($lines as $line) {
            if (trim($line)=="" or $line[0]=="#") continue;
            list($user, $password, $groupsList) = array_map("trim", $this->yellow->toolbox->getTextList($line, ":", 3));
            $groups = array_map("trim", explode(",", $groupsList));
            $groups[] = "@all";
            if ($user==$givenUser && $password==$givenPassword) {
                foreach (array_map("trim", explode(",", $allowedPeople)) as $item) {
                    if ($item=="") continue;
                    if ($item[0]=="@") {
                        if (in_array($item, $groups)) return true;
                    } else {
			if ($item==$user) return true;
                    }
                }
            }
        }
        return false;
    }
}
