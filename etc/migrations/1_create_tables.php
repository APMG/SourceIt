<?php

/**
 *
 *
 * @author ktaborski
 * @package default
 */
class AddTable extends Doctrine_Migration_Base {


    /**
     *
     */
    public function up() {
        $this->createTable('article', array(
                'artcl_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'primary' => 1,
                    'autoincrement' => 1
                ),
                'artcl_uuid' => array(
                    'type' => 'string',
                    'length' => 12,
                    'notnull' => 1,
                    'notblank' => 1,
                    'fixed' => 1
                ),
                'artcl_url' => array(
                    'type' => 'string',
                    'length' => 2083,
                    'notnull' => 1,
                    'notblank' => 1
                ),
                'artcl_url_md5' => array(
                    'type' => 'string',
                    'length' => 2083,
                    'notnull' => 1,
                    'notblank' => 1
                ),
                'artcl_content' => array(
                    'type' => 'clob'
                ),
                'artcl_athr_id' => array(
                    'type' => 'integer',
                    'length' => 4
                ),
                'artcl_nwsrn_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                ),
                'artcl_cre_dtim' => array(
                    'type' => 'timestamp',
                    'length' => 4,
                    'notnull' => 1
                ),
                'artcl_upd_dtim' => array(
                    'type' => 'timestamp',
                    'length' => 4
                ),
            )
        );

        $this->createTable('author', array(
                'athr_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'primary' => 1,
                    'autoincrement' => 1
                ),
                'athr_uuid' => array(
                    'type' => 'string',
                    'length' => 12,
                    'notnull' => 1,
                    'notblank' => 1,
                    'fixed' => 1
                ),
                'athr_name' => array(
                    'type' => 'string',
                    'length' => 255,
                    'notnull' => 1,
                    'notblank' => 1
                ),
                'athr_cre_dtim' => array(
                    'type' => 'timestamp',
                    'length' => 4,
                    'notnull' => 1
                ),
                'athr_upd_dtim' => array(
                    'type' => 'timestamp',
                    'length' => 4
                ),
            )
        );

        $this->createTable('entity', array(
                'entt_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'primary' => 1,
                    'autoincrement' => 1
                ),
                'entt_uuid' => array(
                    'type' => 'string',
                    'length' => 12,
                    'notnull' => 1,
                    'notblank' => 1,
                    'fixed' => 1
                ),
                'entt_value' => array(
                    'type' => 'string',
                    'length' => 255,
                    'notnull' => 1,
                    'notblank' => 1
                ),
                'entt_type' => array(
                    'type' => 'string',
                    'length' => 1
                ),
                'athr_cre_dtim' => array(
                    'type' => 'timestamp',
                    'length' => 4,
                    'notnull' => 1
                ),
                'athr_upd_dtim' => array(
                    'type' => 'timestamp',
                    'length' => 4
                ),
            )
        );

        $this->createTable('newsroom', array(
                'nwsrn_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'primary' => 1,
                    'autoincrement' => 1
                ),
                'nwsrn_uuid' => array(
                    'type' => 'string',
                    'length' => 12,
                    'notnull' => 1,
                    'notblank' => 1,
                    'fixed' => 1
                ),
                'nwsrn_name' => array(
                    'type' => 'string',
                    'length' => 255,
                    'notnull' => 1,
                    'notblank' => 1
                ),
                'nwsrn_url' => array(
                    'type' => 'string',
                    'length' => 2083,
                    'notnull' => 1,
                    'notblank' => 1
                ),
                'nwsrn_cre_dtim' => array(
                    'type' => 'timestamp',
                    'length' => 4,
                    'notnull' => 1
                ),
                'nwsrn_upd_dtim' => array(
                    'type' => 'timestamp',
                    'length' => 4
                ),
            )
        );

        $this->createTable('selection', array(
                'slctn_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'primary' => 1,
                    'autoincrement' => 1
                ),
                'slctn_uuid' => array(
                    'type' => 'string',
                    'length' => 12,
                    'notnull' => 1,
                    'notblank' => 1,
                    'fixed' => 1
                ),
                'slctn_value' => array(
                    'type' => 'string',
                    'length' => 9999,
                    'notnull' => 1,
                    'notblank' => 1
                ),
                'slctn_type' => array(
                    'type' => 'string',
                    'length' => 1
                ),
                'slctn_artcl_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                ),
                'slctn_cre_dtim' => array(
                    'type' => 'timestamp',
                    'length' => 4,
                    'notnull' => 1
                ),
                'slctn_upd_dtim' => array(
                    'type' => 'timestamp',
                    'length' => 4
                ),
            )
        );

        $this->createTable('semantic_result', array(
                'smrslt_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'primary' => 1,
                    'autoincrement' => 1
                ),
                'smrslt_uuid' => array(
                    'type' => 'string',
                    'length' => 12,
                    'notnull' => 1,
                    'notblank' => 1,
                    'fixed' => 1
                ),
                'smrslt_content' => array(
                    'type' => 'clob'
                ),
                'smrslt_type' => array(
                    'type' => 'string',
                    'length' => 1
                ),
                'smrslt_artcl_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                ),
                'smrslt_cre_dtim' => array(
                    'type' => 'timestamp',
                    'length' => 4,
                    'notnull' => 1
                ),
                'smrslt_upd_dtim' => array(
                    'type' => 'timestamp',
                    'length' => 4
                ),
            )
        );
    }


    /**
     *
     */
    public function down() {
        $this->dropTable('article');
        $this->dropTable('author');
        $this->dropTable('entity');
        $this->dropTable('newsroom');
        $this->dropTable('selection');
        $this->dropTable('semantic_result');
    }


}
