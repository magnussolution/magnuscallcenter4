<?php
/**
 * =======================================
 * ###################################
 * MagnusCallCenter
 *
 * @package MagnusCallCenter
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2012 - 2018 MagnusCallCenter. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnussolution/magnuscallcenter/issues
 * =======================================
 * MagnusCallCenter.com <info@magnussolution.com>
 *
 */

class Model extends CActiveRecord
{

    public function getModule()
    {
        return $this->_module;
    }

    public function beforeSave()
    {

        if (isset($this->creationdate) && !$this->validateDate($this->creationdate)) {
            $this->creationdate = date('Y-m-d H:i:s');
        } else if (isset($this->datecreation) && !$this->validateDate($this->datecreation)) {
            $this->datecreation = date('Y-m-d H:i:s');
        }

        return parent::beforeSave();

    }

    public function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}
