<?php
/* Copyright (c) internetlehrer GmbH, Extended GPL, see LICENSE */


/**
 * Class ilLp2LrsPrivacyConfigGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 */

use ILIAS\DI\Container;

class ilLp2LrsPrivacyConfigGUI extends ilPluginConfigGUI
{
    /** @var ilLp2LrsPrivacyPlugin $plugin_object */
    protected $plugin_object;

    /** @var Container $dic */
    private $dic;

    /** @var string $cmdFilterSession */
    private $cmdFilterSession = 'resetFilter'; # resetFilter writeFilterToSession

    public function __construct()
    {
        global $DIC /** @var Container $DIC */;
        $this->dic = $DIC;
    }

    public function performCommand($cmd)
    {
        $this->initTabs();
        $this->{$cmd}();
    }


    /**
     * Init Tabs
     *
     * @param string	mode ('edit_type' or '')
     */
    function initTabs($a_mode = "")
    {
        global $ilCtrl, $ilTabs, $lng;

        $ilTabs->addTab("list",
            $this->plugin_object->txt('content_types'),
            $ilCtrl->getLinkTarget($this, 'configure')
        );
    }


    /**
     * @param ilPropertyFormGUI|null $form
     * @throws ilPluginException
     */
    protected function configure($resetFilter = false, ilPropertyFormGUI $form = null)
    {
        if( !ilPluginAdmin::isPluginActive('xlpc') && !ilPluginAdmin::isPluginActive('xelrs') ) {
            ilUtil::sendFailure($this->plugin_object->txt('not_active_xlpc'));
            return;
        }

        if( isset($_POST['cmd']['resetFilter']) || isset($_GET['cmd']) && filter_var($_GET['cmd'], FILTER_SANITIZE_STRING) === 'configure' ) {
            $resetFilter = true;
            ilSession::clear('xlpp_form');
            unset($_POST['query']);
            unset($_POST['currStatusOnly']);
        }

        if( isset($_GET['xlpp_table_nav']) ) {
            $resetFilter = false;
            $_POST = [
                'cmd' => ['applyFilter' => true],
                'query' => unserialize($_SESSION["form_xlpp"]['query']),
                'currStatusOnly' => unserialize($_SESSION["form_xlpp"]['currStatusOnly'])
            ];
        }

        $this->plugin_object->includeClass('class.ilLp2LrsPrivacyTableGUILogStatus.php');
        $tableGui = new ilLp2LrsPrivacyTableGUILogStatus($this);

        $tableGui->init($this, $resetFilter);

        if( !$resetFilter && isset($_GET['cmd']) && $_GET['cmd'] === 'post' || isset($_GET['xlpp_table_nav']) ) {
            $tableGui->writeFilterToSession();
        }
        /*
        if( isset($_POST['cmd']['resetFilter']) || isset($_GET['cmd']) && filter_var($_GET['cmd'], FILTER_SANITIZE_STRING) === 'configure' ) {
            $tableGui->resetOffset();
            $tableGui->resetFilter(); # {$this->cmdFilterSession}
            unset($_SESSION["form_xlpp"]);
        } else {

            if( isset($_GET['cmd']) && $_GET['cmd'] === 'post' ) {
                $tableGui->writeFilterToSession();
            } else {
                if (isset($_SESSION["form_xlpp"])) {
                    if (isset($_SESSION["form_xlpp"]['query'])) {
                        $tableGui->getFilterItemByPostVar('query')->setValue(
                            unserialize($_SESSION["form_xlpp"]['query'])
                        );
                    }
                }
            }

            #$tableGui->{$this->cmdFilterSession}();
            #echo '<pre>'; var_dump($_SESSION); exit;

        }
        */
        $this->dic->ui()->mainTemplate()->setContent($tableGui->getHTML());
    }

    /**
     * @throws ilPluginException
     */
    public function applyFilter()
    {
        $this->cmdFilterSession = 'writeFilterToSession';
        $this->configure();
    }

    /**
     * @throws ilPluginException
     */
    public function resetFilter()
    {
        unset($_SESSION["form_xlpp"]);
        $this->configure();
    }

    /**
     * @throws ilPluginException
     */
    protected function save()
    {
        global $DIC; /* @var Container $DIC */

        $this->dic->ctrl()->redirect($this, 'configure');
    }

    /**
     * Show auto complete results
     */
    public function userAutoComplete()
    {
        include_once './Services/User/classes/class.ilUserAutoComplete.php';
        $auto = new ilUserAutoComplete();
        $auto->setSearchFields(array('login', 'firstname', 'lastname', 'email', 'second_email'));
        $auto->enableFieldSearchableCheck(false);
        $auto->setMoreLinkAvailable(false);

        echo $auto->getList($_REQUEST['term']);
        exit();
    }
}
