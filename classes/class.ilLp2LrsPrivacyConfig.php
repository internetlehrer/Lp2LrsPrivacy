<?php
/* Copyright (c) internetlehrer GmbH, Extended GPL, see LICENSE */

/**
 * Class ilLp2LrsPrivacyConfig
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 */

use ILIAS\DI\Container;

class ilLp2LrsPrivacyConfig
{
    /**
     * @var ilSetting
     */
    protected $settings;


    /** @var Container $dic */
    private $dic;

    private $logged = false;


    /**
     * Constructor ilLp2LrsPrivacyConfig
     *
     * @param string $settingsId
     */
    public function __construct(string $settingsId)
    {
        global $DIC; /** @var Container $DIC */
        $this->dic = $DIC;
        $this->settings = new ilSetting($settingsId);
    }

    /**
     * Get the value of Checkbox.
     *
     * @param string $a_check
     * @return bool
     */
    public function getCheck(string $a_check): bool
    {
        return (bool)$this->settings->get($a_check, 0);
    }
    
    /**
     * Set the value of Checkbox.
     *
     * @param string $a_check
     * @param mixed $a_checked
     */
    public function setCheck(string $a_check, $a_checked)
    {
        $this->settings->set($a_check, $a_checked);
        $this->logStatus($a_check, $a_checked);
    }

    public function logStatus(string $a_check, bool $a_checked): void
    {
        $db = $this->dic->database();
        list($prefix, $refId, $usrId) = explode('_', $a_check);
        $data = [
            'usr_id' => ['integer', $usrId],
            'ref_id' => ['integer', $refId],
            'status' => ['integer', $a_checked],
            'log_date' => ['timestanp', date('Y-m-d H:i:s')],
        ];
        $db->insert('uihk_xlpp_log', $data);
    }

    public function getPrivacySettingsFromLrsType(int $lrsId = 1): array
    {
        $data = [];
        $db = $this->dic->database();
        $from = " FROM cmix_lrs_types";
        $where = " WHERE type_id = " . $db->quote($lrsId, 'integer');
        $select = 'SELECT lrs_endpoint, privacy_ident, privacy_name';
        $query = $select . $from . $where;
        $res = $db->query($query);
        while($row = $db->fetchAssoc($res)) {
            $data[] = $row;
        }
        return $data;
    }

}
