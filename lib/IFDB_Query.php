<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * IFDB_Query base class
 *
 * @author pkarman
 * @package default
 */
class IFDB_Query extends Doctrine_Query {
    /* queries without ->select() are implicit select-alls */
    protected $is_explicit_select = false;


    /**
     * Since php doesn't support late static binding in 5.2 we need to override
     * this method to instantiate a new MyQuery instead of Doctrine_Query
     *
     * @param Doctrine_Connection $conn (optional)
     * @return IFDB_Query
     */
    public static function create($conn = null) {
        return new IFDB_Query($conn);
    }


    /**
     * Set the query to use a particular connection
     *
     * @param Doctrine_Connection $conn
     */
    public function set_connection($conn) {
        $this->_conn = $conn;
    }


    /**
     * Prequery hook
     */
    public function preQuery() {
        if ($this->gettype() == Doctrine_Query::SELECT) {
            $this->set_connection(IFDB_DBManager::get_slave_connection());
        }
        else {
            $this->set_connection(IFDB_DBManager::get_master_connection());
        }
    }


    /**
     * After the first time you call $q->select(), a query is no longer an
     * implicit select all.
     *
     * @param string  $select
     * @return Doctrine_Query
     */
    public function select($select=null) {
        $this->is_explicit_select = true;
        return parent::select($select);
    }


    /**
     * The first time you call addSelect (assuming that you didn't do an
     * explicit $q->select() query), all the joined relations will be
     * auto-selected.  After this point, however, your select statement will
     * be explicit, so if you continue to join relations, they will not be
     * automatically included in the select.
     *
     * @param string  $select
     * @return Doctrine_Query
     */
    public function addSelect($select) {
        if (!$this->is_explicit_select) {
            // get the "from" parts, and add them to the select
            $this->getSqlQuery(); // trigger the dql parser
            $aliases = array_keys($this->_queryComponents);
            foreach ($aliases as $idx => $a) {
                $aliases[$idx] .= '.*';
            }
            $this->select(implode(', ', $aliases));
        }

        // now add the select statement
        return parent::addSelect($select);
    }


}
