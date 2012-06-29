<?php

/**
 *
 *
 * @author ktaborski
 * @package default
 */
class AlterComment extends Doctrine_Migration_Base {


    /**
     * adding columns
     * and fixing cmmnt_private's blank status
     */
    public function up() {
        $this->addColumn('comment', 'cmmnt_type', 'string', 1, array());
        $this->addColumn('comment', 'cmmnt_fb_export_status', 'string', 1, array());
        $this->changeColumn('comment', 'cmmnt_private', 'string', 16, array('notnull' => true, 'notblank' => false));
    }


    /**
     *
     */
    public function down() {
        $this->removeColumn('comment', 'cmmnt_type');
        $this->removeColumn('comment', 'cmmnt_fb_export_status');
        $this->changeColumn('comment', 'cmmnt_private', 'string', 16, array('notnull' => true, 'notblank' => true));
    }


}
