<?php

/**
 *
 *
 * @author ktaborski
 * @package default
 */
class ChangeColumn extends Doctrine_Migration_Base {


    /**
     * fixing the length from text to char(12) and adding a unique constraint
     */
    public function up() {
        $this->ChangeColumn('article', 'artcl_uuid', 'string', 12, array(
                'notnull' => true,
                'fixed' => true,
                'unique' => true
            )
        );
        $this->ChangeColumn('author', 'athr_uuid', 'string', 12, array(
                'notnull' => true,
                'fixed' => true,
                'unique' => true
            )
        );
        $this->ChangeColumn('entity', 'entt_uuid', 'string', 12, array(
                'notnull' => true,
                'fixed' => true,
                'unique' => true
            )
        );
        $this->ChangeColumn('newsroom', 'nwsrn_uuid', 'string', 12, array(
                'notnull' => true,
                'fixed' => true,
                'unique' => true
            )
        );
        $this->ChangeColumn('selection', 'slctn_uuid', 'string', 12, array(
                'notnull' => true,
                'fixed' => true,
                'unique' => true
            )
        );
        $this->ChangeColumn('semantic_result', 'smrslt_uuid', 'string', 12, array(
                'notnull' => true,
                'fixed' => true,
                'unique' => true
            )
        );
    }


    /**
     * leaving the length, but dropping the constraint
     */
    public function down() {
        $this->ChangeColumn('article', 'artcl_uuid', 'string', 12, array(
                'notnull' => 1,
                'notblank' => 1,
                'fixed' => 1
            )
        );
        $this->ChangeColumn('author', 'athr_uuid', 'string', 12, array(
                'notnull' => 1,
                'notblank' => 1,
                'fixed' => 1
            )
        );
        $this->ChangeColumn('entity', 'entt_uuid', 'string', 12, array(
                'notnull' => 1,
                'notblank' => 1,
                'fixed' => 1
            )
        );
        $this->ChangeColumn('newsroom', 'nwsrn_uuid', 'string', 12, array(
                'notnull' => 1,
                'notblank' => 1,
                'fixed' => 1
            )
        );
        $this->ChangeColumn('selection', 'slctn_uuid', 'string', 12, array(
                'notnull' => 1,
                'notblank' => 1,
                'fixed' => 1
            )
        );
        $this->ChangeColumn('semantic_result', 'smrslt_uuid', 'string', 12, array(
                'notnull' => 1,
                'notblank' => 1,
                'fixed' => 1
            )
        );

        throw new Doctrine_Migration_IrreversibleMigrationException(
            'You must manually remove the unique key\'s, as doctrine migrations don\'t seem to support this implicitly or explicitly.'
        );
    }


}
