<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see LICENSE */

include_once './Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php';
 
/**
 * class ilLp2LrsPrivacyPlugin.
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 */
class ilLp2LrsPrivacyPlugin extends ilUserInterfaceHookPlugin
{
    const PLUGIN_ID = "xlpp";
    const PLUGIN_NAME = "Lp2LrsPrivacy";
    const PLUGIN_CLASS_NAME = self::class;

    /** @var ilLp2LrsPrivacyConfig $config */
    private $config;

    /**
     * Get the Plugin Name.
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return self::PLUGIN_NAME;
    }
    
    /**
     * Constructor ilLPPlugin
     */
    public function __construct()
    {
        parent::__construct();

        $this->includeClass('class.ilLp2LrsPrivacyConfig.php');
        $this->config = new ilLp2LrsPrivacyConfig($this->getSlotId().'_'.$this->getId());
    }

    /**
     * Get the Config.
     *
     * @return object
     */
    public function getConfig()
    {
        return $this->config;
    }

    protected function afterUninstall()
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilDB->manipulate('DELETE FROM settings WHERE module = ' . $ilDB->quote('uihk_xlpp', 'string'));
        $ilDB->dropTable($ilDB->quote('uihk_xlpp_log', 'string'));
    }
}
