/**************************************************************************
 *
 *   Copyright 2010 American Public Media Group
 *
 *   This file is part of AIR2.
 *
 *   AIR2 is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   AIR2 is distributed in the hope that it will be useful, 
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License 
 *   along with AIR2.  If not, see <http://www.gnu.org/licenses/>.
 *
 *************************************************************************/
/*
    Query builder 
    based on Perl Search::Query    
*/

Ext.ns('Search.Query');

Search.Query.FieldPicker = Ext.extend(Ext.form.ComboBox, {

    initComponent : function() {
    
        //this.displayField = 'label';
        this.forceSelection = true;
        this.triggerAction = 'all';
        this.selectOnFocus = true;
        this.ctCls = 'search-query';
        this.value = '';
        
        this.listeners = {
            'select' : function(thisBox, ev) {
                var afterSelect = thisBox.parentClause.setField(thisBox.getValue());
                thisBox.parentClause.setValue('');  // trigger reset
                thisBox.parentClause.updateDisplay();
                if (Ext.isDefined(afterSelect)) {
                    afterSelect(thisBox);
                }
            }
        
        };
        
        Search.Query.FieldPicker.superclass.initComponent.call(this);
        
    },
    
    reset : function() {
        Search.Query.FieldPicker.superclass.reset.call(this);
        if (Ext.isDefined(this.onReset)) {
            this.onReset();
        }
    }

});

Search.Query.Clause = Ext.extend(Ext.Toolbar, {
    
    constructor : function(config) {
    
        this.isaClause  = true;
        this.field      = '';
        this.value      = '';
        this.op         = '';
        this.proximity  = null;
        this.quote      = null;
                
        Search.Query.Clause.superclass.constructor.call(this,config);
    },

    toJson : function() {
        var o = {
            isaClause : true,
            field     : this.field,
            value     : this.value,
            op        : this.op,
            proximity : this.proximity,
            quote     : this.quote
        };
        if (this.isTree()) {
            o.value = o.value.toJson();
        }
        return o;
    },
    
    initComponent : function() {
        
        var clause = this;
        Ext.each(this.items, function(item,idx) {
            item.parentClause = clause;
        });
        
        Search.Query.Clause.superclass.initComponent.call(this);

    },
    
    updateDisplay : function() {
        if (!Ext.isDefined(this.explainer)) {
            return;
        }
        this.explainer(this);
    },
    
    setField : function(fieldValue) {
        this.field = fieldValue;
        if (Ext.isDefined(this.onFieldChange)) {
            return this.onFieldChange(fieldValue);
        }  
    },
    
    setOp : function(opValue) {
        this.op = opValue;
        if (Ext.isDefined(this.onOpChange)) {
            this.onOpChange(opValue);
        }
    },
    
    setValue : function(val) {
        this.value = val;
        if (Ext.isDefined(this.onValueChange)) {
            this.onValueChange(val);
        }
    },
    
    clear : function() {
        this.field = "";
        this.op    = "";
        this.value = "";
        this.quote = null;
        this.proximity = null;
    },
    
    isTree: function() {
        return Ext.isObject(this.value);
    },
    
    hasChildren: function() {
        if (!this.isTree()) {
            return null;
        }
        var nChildren = 0;
        var dialect = this.value;
        Ext.each(dialect.ops, function(item, idx, ops) {
            var i;
            if (!dialect[item].length) {
                return;
            }
            for (i=0; i<dialect[item].length; i++) {
                nChildren++;
                if (dialect[item][i].isTree()) {
                    nChildren += dialect[item][i].hasChildren();    // recurse
                }
            }
        });
        return nChildren;
    },
    
    toString : function() {
        return this.stringify();
    },
    
    stringify: function() {
        if (this.value == "") {
            return "";
        }
        if (this.isTree()) {
            return "("+this.value+")";
        }
        else if (this.proximity) {
            return this.field+this.op+this.quote+this.value+this.quote+"~"+this.proximity;
        }
        else if (this.quote) {
            return this.field+this.op+this.quote+this.value+this.quote;
        }
        else if (Ext.isArray(this.value)) {
            if (this.op == '..') {
                return this.field+'=('+this.value[0]+this.op+this.value[1]+')';
            }
            else if (this.op == '!..') {
                return this.field+'!=('+this.value[0]+'..'+this.value[1]+')';
            }
        }
        else if (!this.field.length) {
            if (this.op == '!=') {
                return '(NOT ' + this.value + ')';
            }
            return '('+this.value+')';
        }
        else if (this.value === "NULL") {
            return this.field+this.op+this.value;
        }
        else {
            return this.field+this.op+'('+this.value+')';
        }
    }

});

Search.Query.Dialect = Ext.extend(Ext.Container, {

    constructor : function(config) {
    
        this.isaDialect     = true;
        this.default_field  = '';
        this.default_bool   = 'AND';
        this.and            = [];
        this.or             = [];
        this.not            = [];
        this.ops            = ['and','or','not'];
        this.op_map = {
            'and':' AND ',
            'or' :' OR ',
            'not':' '
        };
        this.op_prefix = {
            'and' : '+',
            'or'  : '',
            'not' : '-'
        };
       
        Search.Query.Dialect.superclass.constructor.call(this,config);
        
    },
    
    fromJson : function(json) {
        var d = this;
        Logger(json);
        Ext.iterate(json, function(k,v,obj) {
            Logger(k,v);
            Ext.each(v, function(item,idx,arr) {
                Logger(item,idx);
                var clause = new Search.Query.Clause(item);
                if (k == "+") {
                    d.and.push(clause);
                }
                else if (k == "-") {
                    d.not.push(clause);
                }
                else if (k == "") {
                    d.or.push(clause);
                }
            });
        });
    },
    
    clear : function() {
        this.and = [];
        this.or  = [];
        this.not = [];
    },

    toJson : function() {
        var o = {
            isaDialect    : true,
            default_field : this.default_field,
            default_bool  : this.default_bool,
            ops           : this.ops,
            op_map        : this.op_map,
            op_prefix     : this.op_prefix
        };
        var dialect = this;
        Ext.each(dialect.ops, function(item, idx, ops) {
            var clauses = [];
            var i = 0;
            if (!dialect[item].length) {
                o[item] = [];
                return; 
            }       
            for (i=0; i<dialect[item].length; i++) {
                var clause = dialect[item][i].toJson(); 
                if (!clause || clause == "") {
                    Logger("clause failed toJson", dialect[item][i]);
                    continue;
                }       
                clauses.push( clause );
            }       
            o[item] = clauses; 
        });
        return o;
    },
    
    initComponent : function() {
        
        Search.Query.Dialect.superclass.initComponent.call(this);
        
    },

    toString : function() {
        return this.stringify();
    },
    
    stringify : function(dialect, bool) {
        if (!dialect) {
            dialect = this;
        }
        if (!dialect.stringify) {
            dialect = new Search.Query.Dialect(dialect);
        }
        if (!bool) {
            bool = dialect.default_bool;
        }
        
        var q = [];
        Ext.each(this.ops, function(item, idx, ops) {
            var clauses = [];
            var i = 0;
            var joiner = dialect.op_map[item];
            if (!dialect[item].length) {
                return;
            }
            for (i=0; i<dialect[item].length; i++) {
                var clause = dialect.stringifyClause( dialect[item][i], item );
                if (!clause || clause == "") {
                    continue;
                }
                //clauses.push( dialect.op_prefix[item]+clause );
                clauses.push( clause );
            }
            if (!clauses.length) {
                return;
            }
            
            //joiner = ' ';
            //q.push( '(' + clauses.join(joiner) + ')' );
            q.push( clauses.join( joiner ) );
        });
        //Logger(q, bool);
        return q.join(' '+bool+' ');
        
    },

    removeClause : function(prefix,clause) {
        var idx = null;
        for (var i=0; i<this[prefix].length; i++) {
            if (this[prefix][i].stringify() === clause.stringify()) {
                idx = i;
                break;
            }
        }
        if (idx === null) {
            throw new Ext.Error("No such clause in dialect:"+clause);
        }
        return this[prefix].splice(idx,1);
    },
    
    walk : function(code) {
        var dialect = this;
        Ext.each(this.ops, function(item, idx, ops) {
            if (!dialect[item].length) {
                return;
            }
            for (var i=0; i<dialect[item].length; i++) {
                var clause = dialect[item][i];
                if (!clause || clause == "") {
                    continue;
                }
                if (clause.isaDialect) {
                    clause.walk(code);
                }
                else if (clause.isTree()) {
                    code(clause,dialect,item,code);
                    clause.value.walk(code);
                }
                else {
                    code(clause,dialect,item,code);
                }
            }
        });
    
    },
    
    stringifyClause : function(clause, prefix) {
   
        if (!clause.stringify) {
            clause = new Search.Query.Clause(clause);
        }
 
    // TODO Search::Query now retains () no matter what. confirm this is correct below.. 
        if (clause.op == '()') {
        /*
            var children = clause.hasChildren();
            if (children && children == 1) {
                if (prefix == 'not') {
                    return 'NOT '+this.stringify(clause.value);
                }
                else {
                    return this.stringify(clause.value);
                }
            }
            else {
         */
                if (prefix == 'not') {
                    return 'NOT ('+this.stringify(clause.value)+')';
                }
                else {
                    return '('+this.stringify(clause.value)+')';
                }
            //}
        }
        
        return clause.stringify();
    
    }


});
