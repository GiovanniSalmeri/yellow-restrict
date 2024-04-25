<?php
// Restrict extension, https://github.com/GiovanniSalmeri/yellow-restrict

class YellowRestrict {
    const VERSION = "0.9.1";
    public $yellow;         //access to API

    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("restrictUserFile", "restrict.ini");
        $this->yellow->language->setDefaults(array(
            "Language: en",
            "RestrictDescription: Reserved content",
            "RestrictLogged: Logged as @user. Close the browser to log out.",
            "CoreError401Title: Unauthorized",
            "CoreError401Text: Please refresh the page and enter username and password in order to see the content.",
            "Language: de",
            "RestrictDescription: Vertraulicher Inhalt",
            "RestrictLogged: Als @user protokolliert. Schließ den Browser, um dich abzumelden.",
            "CoreError401Title: Unauthorisiert",
            "CoreError401Text: Bitte aktualisiere die Seite und gib Benutzername und Passwort ein, um den Inhalt zu sehen.",
            "Language: fr",
            "RestrictDescription: Contenu confidentiel",
            "RestrictLogged: Connecté en tant que @user. Fermez le navigateur pour vous déconnecter.",
            "CoreError401Title: Non autorisé",
            "CoreError401Text: Veuillez actualiser la page et entrer le nom d'utilisateur et le mot de passe afin de voir le contenu.",
            "Language: it",
            "RestrictDescription: Contenuto riservato",
            "RestrictLogged: Connesso come @user. Chiudi il browser per disconnetterti.",
            "CoreError401Title: Non autorizzato",
            "CoreError401Text: Ricarica la pagina e fornisci le tue credenziali per vedere il contenuto.",
            "Language: es",
            "RestrictDescription: Contenido reservado",
            "RestrictLogged: Conectado como @usuario. Cierra el navegador para desconectarte.",
            "CoreError401Title: No autorizado",
            "CoreError401Text: Actualiza la página e proporcione sus credenciales para ver el contenido.",
            "Language: nl",
            "RestrictDescription: Vertrouwelijke inhoud",
            "RestrictLogged: Gelogd als @user. Sluit de browser om uit te loggen.",
            "CoreError401Title: Onbevoegd",
            "CoreError401Text: Vernieuw de pagina en voer de gebruikersnaam en het wachtwoord in om de inhoud te zien.",
            "Language: pt",
            "RestrictDescription: Conteúdo reservado",
            "RestrictLogged: Conectado como @usuário. Feche o navegador para desconectar.",
            "CoreError401Title: Não autorizado",
            "CoreError401Text: Atualize a página e forneça suas credenciais para visualizar o conteúdo.",
        ));
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
            $page->set("description", $this->yellow->language->getTextHtml("restrictDescription"));
            if (!$this->matchPermissions($this->yellow->toolbox->getServer('PHP_AUTH_USER'), $this->yellow->toolbox->getServer('PHP_AUTH_PW'), $allowedPeople)) {
                $this->yellow->page->setHeader("WWW-Authenticate", 'Basic realm="", charset="UTF-8"');
                $this->yellow->page->error("401");
            }
        }
    }

    // Handle page extra data
    public function onParsePageExtra($page, $name) {
        $output = null;
        if ($name=="logout") {
            if ($page->isExisting("restrict")) {
                $output .= "<p>".str_replace("@user", "<b>".htmlspecialchars($this->yellow->toolbox->getServer('PHP_AUTH_USER'))."</b>", $this->yellow->language->getTextHtml("restrictLogged"))."</p>\n";
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
                    if (substr($item, 0, 1)=="@") {
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
