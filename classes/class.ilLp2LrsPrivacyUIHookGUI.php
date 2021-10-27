<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see LICENSE */

use ILIAS\DI\Container;
include_once './Services/UIComponent/classes/class.ilUIHookPluginGUI.php';

/**
 * Class ilLp2LrsPrivacyUIHookGUI.
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 */
class ilLp2LrsPrivacyUIHookGUI extends ilUIHookPluginGUI
{
    const COMPONENT_CONTAINER = 'Services/Container';
    const COMPONENT_DASHBOARD = 'Services/Dashboard';
    const PART_RIGHT_COLUMN = 'right_column';

    /** @var Container $dic */
    private $dic;

    public function __construct()
    {
        global $DIC; /** @var Container $DIC */
        $this->dic = $DIC;
    }

    /**
     * HTML Output.
     *
     * @param mixed $a_comp
     * @param mixed $a_part
     * @param array $a_par
     * @return array
     * @throws ilObjectException
     * @throws ilObjectNotFoundException
     * @global mixed $DIC
     */
    public function getHTML($a_comp, $a_part, $a_par = array()): array
    {
        if( ilPluginAdmin::isPluginActive("xlpc") ) {
            if ($this->dic->ctrl()->getCmdClass() === strtolower(ilObjCourseGUI::class)
                && $a_comp === self::COMPONENT_CONTAINER
                && $a_part === self::PART_RIGHT_COLUMN
            ) {
                $checkParam = 'lp2lrscy_' . $this->filterRefId() . '_' . $this->dic->user()->getId();
                $checked = $this->plugin_object->getConfig()->getCheck($checkParam);
                if (isset($_GET['privacy_lp2lrs'])) {
                    $checked = (int)filter_var($_GET['privacy_lp2lrs'], FILTER_SANITIZE_NUMBER_INT);
                    $this->plugin_object->getConfig()->setCheck($checkParam, $checked);
                    $guiF = new ilObjectGUIFactory();
                    $guiClass = $guiF->getInstanceByRefId($this->filterRefId());
                    $this->dic->ctrl()->redirect($guiClass, $this->dic->ctrl()->getCmd());
                }

                return array(
                    'mode' => self::APPEND,
                    'html' => $this->getForm($checked)
                );
            }
        }

        return array (
            'mode' =>   '',
            'html' =>   ''
        );
    }

    private function getForm(bool $currState): string
    {
        $this->dic->language()->loadLanguageModule('cmix');
        $tpl = $this->plugin_object->getTemplate('tpl.panel.html');
        $tpl->setVariable('TXT_BLOCK_HEADER', '');
        $tpl->setVariable('INFO_ALLOW_LP2LRS', $this->plugin_object->txt('info_allow_lp2lrs'));
        #$tpl->setVariable('TXT_CURRENT_STATE', $this->plugin_object->txt('txt_current_state'));
        $tpl->setVariable('CURRENT_STATE',  $currState ? $this->plugin_object->txt('enabled') : $this->plugin_object->txt('disabled'));
        $tpl->setVariable('TOGGLE_BUTTON',
            $this->dic->ui()->renderer()->render([$this->getToggleButtonUI($currState)])
        );

        #cmix_lrs_type
        $tpl->setVariable('HEADER_PRIVACY_LRS_TITLE', $this->dic->language()->txt('cmix_lrs_type'));
        $tpl->setVariable('PRIVACY_LRS_TITLE', $this->getPrivacyData()->lrsTitle);

        $tpl->setVariable('HEADER_PRIVACY_IDENT_MODE', $this->dic->language()->txt('username'));
        $tpl->setVariable('PRIVACY_IDENT_MODE', $this->dic->language()->txt($this->getPrivacyData()->identMode));

        $tpl->setVariable('HEADER_PRIVACY_NAME_MODE', $this->dic->language()->txt('content_privacy_ident'));
        $tpl->setVariable('PRIVACY_NAME_MODE', $this->dic->language()->txt($this->getPrivacyData()->nameMode));

        # nameMode

        # $this->dic->language()->txt("conf_user_registered_mail")


        $status = '<div class="dropdown" style="display: inline; float: right;"><span id="lp2lrsCurrentState"><i>' . ($currState ? $this->plugin_object->txt('enabled') : $this->plugin_object->txt('disabled')) . '</i></span></div>';
        return $this->getAccordion($this->plugin_object->txt('header_allow_lp2lrs') . $status, $tpl->get());
    }

    private function getAccordion($header, $content) {
        #$tpl = $this->plugin_object->getTemplate('tpl.panel.html');
        $acc = new ilAccordionGUI();
        $acc->addItem($header, $content);
        return $acc->getHTML();
    }

    private function getToggleButtonUI(bool $currentState)
    {
        $uri = $this->dic->http()->request()->getUri() . '&privacy_lp2lrs=';
        $url = substr($uri, 0, strpos($uri, '&privacy_lp2lrs='));
        $url .= '&privacy_lp2lrs=';

        $b = $this->dic->ui()->factory()->button()->toggle('', $url . '1', $url . '0', $currentState);
        #$b->withUnavailableAction();

        return $b->withAdditionalOnLoadCode(function($id) {
            return '
                $(\'#' . $id . '\').on(\'click\', function(e) {
                    $(\'#' . $id . '\').prop(\'disabled\', \'disabled\');
                    $(\'#lp2lrsCurrentState\').html(\'&nbsp;\').addClass(\'il-btn-with-loading-animation\');
                }).prop(\'disabled\', \'disabled\');
                setTimeout(function() {
                    $(\'#' . $id . '\').prop(\'disabled\', \'\');
                }, 1000);
            ';
        });
    }

    private function getPrivacyData() {
        $settings = new ilSetting(ilLp2LrsCron::JOB_ID);
        $lrsTypeId = $settings->get('lrs_type_id', 0);
        $lrsType = new ilCmiXapiLrsType($lrsTypeId);

        $endpoint = $lrsType->getLrsEndpoint();
        if(isset(array_flip(get_class_methods($lrsType))['getPrivacyName'])) //ILIAS 7
        {
            $identMode = 'conf_privacy_ident_' . ilObjCmiXapiGUI::getPrivacyIdentString($lrsType->getPrivacyIdent());
            $nameMode = 'conf_privacy_name_' . ilObjCmiXapiGUI::getPrivacyNameString($lrsType->getPrivacyName());
        } else {
            $identMode = 'conf_user_ident_' . $lrsType->getUserIdent();
            $nameMode = 'conf_user_name_' . $lrsType->getUserName();
        }
        return (object)[
            'lrsTitle' => $lrsType->getTitle(),
            'identMode' => $identMode,
            'nameMode' => $nameMode
        ];
    }

    /**
     * Get the Object RefId from the URL.
     *
     * @return integer|null
     */
    private function filterRefId(): ?int
    {
        $obj_ref_id = filter_input(INPUT_GET, 'ref_id');

        if ($obj_ref_id === null) {
            $param_target = filter_input(INPUT_GET, 'target');
            $obj_ref_id = explode("_", $param_target)[1];
        }

        $obj_ref_id = intval($obj_ref_id);

        if ($obj_ref_id > 0) {
            return $obj_ref_id;
        } else {
            return null;
        }
    }

}
