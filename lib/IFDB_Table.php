<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * IFDB_Table base class
 *
 * @author rcavis
 * @package default
 */
class IFDB_Table extends Doctrine_Table {
    /* track any relation-aliases that should NOT be exported */
    protected $no_export = array();


    /**
     * Override bind() to check for an 'export' key in the hasOne/hasMany
     * options.  If set to FALSE, Doctrine will not set a foreign key for that
     * relationship.  For example:
     *
     *  $this->hasOne('Foo as Foobar', array(
     *      'local'   => 'bar_foo_id',
     *      'foreign' => 'foo_id',
     *      'export'  =>  false,
     *  );
     *
     * NOTE: because Doctrine_Table->bind() doesn't return the results of the
     * Parser->bind(), we need to bypass the parent class method entirely.
     *
     * @param array   $args
     * @param integer $type
     */
    public function bind($args, $type) {
        $options = (!isset($args[1])) ? array() : $args[1];
        $options['type'] = $type;
        $rel = $this->_parser->bind($args[0], $options);

        if (isset($rel['export']) && $rel['export'] === false) {
            $this->no_export[] = $rel['alias'];
        }
        parent::bind($args, $type);
    }


    /**
     * Before returning the exportable-version of this table, unset any foreign
     * keys that had the option "export => false".
     *
     * @param bool    $parseForeignKeys
     * @return array
     */
    public function getExportableFormat($parseForeignKeys = true) {
        $data = parent::getExportableFormat($parseForeignKeys);

        // unset any fk's that we shouldn't export
        foreach ($this->no_export as $rel_alias) {
            $key_name = $this->getRelation($rel_alias)->getForeignKeyName();
            unset($data['options']['foreignKeys'][$key_name]);
        }
        return $data;
    }


}
