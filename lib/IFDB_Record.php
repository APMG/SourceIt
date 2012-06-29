<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

abstract class IFDB_Record extends Doctrine_Record {

    /**
     * tests validity of the record using the current data.
     *
     * (This is an override of base Doctrine functionality, to fix a bug with validation.)
     *
     * @param boolean $deep  (optional) run the validation process on the relations
     * @param boolean $hooks (optional) invoke save hooks before start
     * @return boolean        whether or not this record is valid
     */
    public function isValid($deep = false, $hooks = true) {
        if ( ! $this->_table->getAttribute(Doctrine_Core::ATTR_VALIDATE)) {
            return true;
        }

        if ($this->_state == self::STATE_LOCKED || $this->_state == self::STATE_TLOCKED) {
            return true;
        }

        if ($hooks) {
            $this->invokeSaveHooks('pre', 'save');
            $this->invokeSaveHooks('pre', $this->exists() ? 'update' : 'insert');
        }

        // Clear the stack from any previous errors.
        $this->getErrorStack()->clear();

        // Run validation process
        $event = new Doctrine_Event($this, Doctrine_Event::RECORD_VALIDATE);
        $this->preValidate($event);
        $this->getTable()->getRecordListener()->preValidate($event);

        if ( ! $event->skipOperation) {

            $validator = new Doctrine_Validator();
            $validator->validateRecord($this);
            $this->validate();
            if ($this->_state == self::STATE_TDIRTY || $this->_state == self::STATE_TCLEAN) {
                $this->validateOnInsert();
            } else {
                $this->validateOnUpdate();
            }
        }

        $this->getTable()->getRecordListener()->postValidate($event);
        $this->postValidate($event);

        $valid = $this->getErrorStack()->count() == 0 ? true : false;
        if ($valid && $deep) {
            $stateBeforeLock = $this->_state;
            $this->_state = $this->exists() ? self::STATE_LOCKED : self::STATE_TLOCKED;

            foreach ($this->_references as $reference) {
                if ($reference instanceof Doctrine_Record) {
                    if ( ! $valid = $reference->isValid($deep)) {
                        break;
                    }
                } else if ($reference instanceof Doctrine_Collection) {
                        foreach ($reference as $record) {
                            if ( ! $valid = $record->isValid($deep)) {
                                break;
                            }
                        }

                        // Bugfix.
                        if (!$valid) {
                            break;
                        }
                    }
            }
            $this->_state = $stateBeforeLock;
        }

        return $valid;
    }


    /**
     * Save the record to the database
     *
     * @param object  $conn (optional)
     */
    public function save( Doctrine_Connection $conn=null ) {
        // unless explicitly passed, we find the _master connection
        // for the current env.
        if ( $conn === null ) {
            $conn = IFDB_DBManager::get_master_connection();
        }
        parent::save($conn);
        return true;    // so unit tests can ok() this method
    }


    /**
     * All tables should be UTF8
     */
    public function setTableDefinition() {
        // utf8 charset
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }


}
