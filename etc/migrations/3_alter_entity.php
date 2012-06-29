<?php

/**
 *
 *
 * @author ktaborski
 * @package default
 */
class AlterEntity extends Doctrine_Migration_Base {


    /**
     * fixing the length from text to char(12) and adding a unique constraint
     */
    public function up() {
        $this->addColumn('entity', 'entt_confidence', 'string', 32, array());
        $this->addColumn('entity', 'entt_artcl_id', 'integer', 4, array());
        $this->renameColumn('entity', 'athr_cre_dtim', 'entt_cre_dtim');
        $this->renameColumn('entity', 'athr_upd_dtim', 'entt_upd_dtim');
    }


    /**
     * leaving the length, but dropping the constraint
     */
    public function down() {
        $this->removeColumn('entity', 'entt_confidence', 'string', 32, array());
        $this->removeColumn('entity', 'entt_artcl_id', 'integer', 4, array());
        $this->renameColumn('entity', 'entt_cre_dtim', 'athr_cre_dtim');
        $this->renameColumn('entity', 'entt_upd_dtim', 'athr_upd_dtim');
    }


}
