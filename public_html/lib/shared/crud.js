/***************
 * PIJ.CRUD javascript class
 * Extends Livegrid to help with
 * RESTful CRUD.
 */

Ext.ns('PIJ.CRUD');

/* extend the CheckboxSelectionModel to determine if the click action
   was on the checkbox or elsewhere in the row
 */
PIJ.CRUD.SelectionModel = Ext.extend(Ext.ux.grid.livegrid.RowSelectionModel, {
    header : '<div class="x-grid3-hd-checker">&#160;</div>',
    width : 20,
    sortable : false,

    // private
    menuDisabled : true,
    fixed : true,
    dataIndex : '',
    id : 'checker',

    constructor : function(){
        PIJ.CRUD.SelectionModel.superclass.constructor.apply(this, arguments);

        if(this.checkOnly){
            this.handleMouseDown = Ext.emptyFn;
        }
    },

    // private
    initEvents : function(){
        //Logger("bucket initEvents");
        PIJ.CRUD.SelectionModel.superclass.initEvents.call(this);
        //Logger(this);
        this.grid.on('render', function(){
            var view = this.grid.getView();
            //Logger("grid View:", view);
            view.mainBody.on('mousedown', this.onMouseDown, this);
            Ext.fly(view.innerHd).on('mousedown', this.onHdMouseDown, this);

        }, this);
    },

    // private
    onMouseDown : function(e, t){
        if(e.button === 0 && t.className == 'x-grid3-row-checker'){ // Only fire if left-click
            //Logger("mousedown");
            e.stopEvent();
            var row = e.getTarget('.x-grid3-row');
            if(row){
                var index = row.rowIndex;
                if(this.isSelected(index)){
                    this.deselectRow(index);
                }else{
                    this.selectRow(index, true);
                }
            }
        }
    },

    // private
    onHdMouseDown : function(e, t){
        //Logger("HdMouseDown checking class");
        if(t.className == 'x-grid3-hd-checker'){
            //Logger("checking HdMouseDown");
            e.stopEvent();
            var hd = Ext.fly(t.parentNode);
            var isChecked = hd.hasClass('x-grid3-hd-checker-on');
            if(isChecked){
                hd.removeClass('x-grid3-hd-checker-on');
                this.clearSelections();
            }else{
                hd.addClass('x-grid3-hd-checker-on');
                this.selectAll();
            }
        }
    },

    // private
    renderer : function(v, p, record){
        return '<div class="x-grid3-row-checker">&#160;</div>';
    }

});

PIJ.CRUD.XHR_Button = Ext.extend(Ext.Button, {
    text: 'Save',
    handler: function(btn,ev) {

        var f = Ext.getCmp(this.getFormId());
        var basicForm = f.getForm();
        if (!basicForm.isValid()) {
            alert("One or more fields require your attention.");
            return;
        }
        var values = basicForm.getValues();
        Logger(values);
        if (!values) {
            Logger("no values in form");
            return;
        }
        var uuid = this.getUUIDField();
        var http_method = values[uuid].length ? 'PUT' : 'POST';
        var base_url = this.getURL();
        var url = http_method == 'PUT' 
            ? (base_url + '/' + values[uuid]) 
            : base_url;
        var thisButton = this;
        var opts = {
            url: url + '.json',
            clientValidation: true,
            method: http_method,
            waitMsg: 'Saving details...',
            params: { 
                'radix' : Ext.encode(values), 
                'x-tunneled-method' :  http_method   
            },
            success: function(thisForm, act) {
                //Logger("success");
                //Logger(act.result);
                var rec = act.result['radix'];
                thisForm.setValues(rec);
                
                // refresh grid
                var lg = Ext.getCmp(thisButton.getLiveGridId());
                var sm = lg.getSelectionModel();
                var oldRec = sm.getSelected(); // not getSelections() -- only act on one-at-a-time
                var store = lg.getStore();
                if (!oldRec) {
                    store.reload(); // TODO store.loadData() doesn't work cleanly with livegrid
                }
                else {
                    //Logger(oldRec);
                    Ext.iterate(rec, function(k, v, obj) {
                        oldRec.set(k, v);
                    });
                    oldRec.commit();
                }
            },
            failure: function(thisForm, act) {
                Logger("failure!");
                // TODO
            }
                
        };
        basicForm.submit(opts);
    },
    
    getUUIDField : function() {
        if (!Ext.isDefined(this.uuidField)) {
            throw new Ext.Error("uuidField not defined");
        }
        return this.uuidField;
    },
    
    getLiveGridId : function() {
        return 'PIJ-crud-listgrid';
    },
    
    getFormId : function() {
        return 'PIJ-crud-formpanel';
    },
    
    getURL : function() {
        if (!Ext.isDefined(this.baseURL)) {
            throw new Ext.Error("baseURL not defined");
        }
        return this.baseURL;
    }
    
});

PIJ.CRUD.RelatedPanel = function(related, allowEdits, allowDeletes) {

    if (!related || !related.length) {
        return null;
    }

    var tabs = [];
    Ext.each(related, function(item) {
        tabs.push({
            title: item.label,
            items: [
                PIJ.CRUD.EditGrid(item, allowEdits, allowDeletes)
            ]
        });
    });
    var tp = new Ext.TabPanel({
        id: 'PIJ-crud-related',
        resizeTabs: true,
        minTabWidth: 100,
        tabWidth: 120,
        enableTabScroll: true,
        frame: false,
        //width: 450,
        defaults: {
            autoScroll: true,
            border: false
        },
        items: tabs
    });

    return tp;
}

PIJ.CRUD.Editor = function(opts) {

    if (!Ext.isDefined(opts.allowRelatedEdit)) {
        opts.allowRelatedEdit = true;
    }
    if (!Ext.isDefined(opts.allowRelatedDelete)) {
        opts.allowRelatedDelete = true;
    }
    if (!Ext.isDefined(opts.allowEdit)) {
        opts.allowEdit = true;
    }
    if (!Ext.isDefined(opts.allowDelete)) {
        opts.allowDelete = true;
    }

    var formPanel = PIJ.CRUD.FormPanel(opts);
    var listGrid  = PIJ.CRUD.ListGrid(opts);

    var north = {
        id: 'PIJ-crud-north',
        region: 'north',
        height: 27,
        frame: false,
        border: false,
        defaults: {
            border: false
        },
        items: opts.navigation
    };
    
    var south = {
        id: 'PIJ-crud-south',
        region: 'south',
        height: 25,
        collapsible: false,
        border: true,
        html: 'this is the south region'
    };
    
    // west is livegrid browser of all rows in table
    var west = new Ext.Panel({
        id: 'PIJ-crud-west',
        region: 'west',
        collapsible: true,
        title: opts.title,
        width: '450',
        items: [ listGrid ]
    });
        
    var center = new Ext.Panel({
        region: 'center',
        id: 'PIJ-crud-center',
        minWidth: 350,
        items: [ formPanel ]
    });

    //  whole page layout here
    PIJ.Viewport = new Ext.Viewport({
        layout: 'border',
        split: true,
        defaults: {
            border: false,
            split: true
        },
        items: [
            north,
            south,
            west,
            center
        ]
    });

}

PIJ.CRUD.EditGrid = function(opts, allowEdits, allowDeletes) {

    var store = new Ext.ux.grid.livegrid.Store({
        'autoLoad'  : true,
        'autoSave'  : false, // we handle this ourselves.
        'url'       : opts.url + '.json',
        'bufferSize': 300,
        'restful'   : true,  // use GET where appropriate
        'reader'    : new Ext.ux.grid.livegrid.JsonReader(),
        'writer'    : new Ext.data.JsonWriter({
            encode: true,
            writeAllFields: false
        }),
        'listeners'  : {
            'load' : function(thisStore, theRecords, theOpts) {
                //Logger("editgrid store load:", theOpts);
                
            },
            
            'exception' : function(misc) {
                Logger("editgrid store caught exception:", misc);
            
            
            },
            
            'beforeload' : function(thisStore, theOpts) {
                //Logger("editgrid store beforeload:", theOpts);
            
                return true;
            },
            
            'metachange' : function(thisStore, theMeta) {
                //Logger("new meta:", theMeta);
            
                return true;
            }
        
        
        }
    });
    
    var editor = new Ext.ux.grid.RowEditor({
        //saveText: 'Update',
        errorSummary: false,    // esp with Add button, this is distracting.
        listeners: {
            'afteredit' : function(thisEditor, changes, rec, rowIdx) {
                //Logger("rec edited:", changes);
                Logger(rec);
                
                // format any dates
                Ext.iterate(changes, function(key, val, obj) {
                    if (val instanceof Date) {
                        obj[key] = val.format('Y-m-d H:i:s');
                    }
                });
                var toSave = Ext.encode(changes);
                //Logger(toSave);
                
                // mask the grid
                var myMask = new Ext.LoadMask(Ext.getBody(), { msg: 'Saving changes...' });
                myMask.show();
                
                // new record gets POST
                if (thisEditor.newRow) {
                    Ext.Ajax.request({
                        url: opts.url + '.json',
                        method: 'POST',
                        params: { radix: toSave },
                        success: function(resp, ajaxOpts) {
                            //Logger(resp);
                            //Logger("new record saved");
                            var newRec = Ext.decode(resp.responseText);
                            Ext.iterate(newRec.radix, function(key, val, obj) {
                                if (rec.get(key) instanceof Date) {
                                    rec.set(key, Date.parseDate(val, 'Y-m-d H:i:s'));
                                }
                                else {
                                    rec.set(key, val);
                                }
                            });
                            rec.id = newRec.radix[opts.uuidField]; // update with newly assigned PK
                            rec.commit();
                            thisEditor.grid.getSelectionModel().deselectRow(rowIdx);
                            
                            // delete new row flag if saved ok
                            delete(thisEditor.newRow);
                            myMask.hide();
                             
                        },
                        failure: function(resp, ajaxOpts) {
                            alert("Error POSTing " + toSave);
                            myMask.hide();
                        }
                    });
                }
                else {
                // update record gets PUT
                    Ext.Ajax.request({
                        url: opts.url + '/' + rec.id + '.json',
                        method: 'PUT',
                        params: { radix: toSave },
                        success: function(resp, ajaxOpts) { 
                            //Logger("record updated");
                            rec.commit();
                            thisEditor.grid.getSelectionModel().deselectRow(rowIdx);
                            myMask.hide();
                        },
                        failure: function(resp, ajaxOpts) {
                            alert("Error PUTing " + toSave);
                            myMask.hide();
                        }
                    });
                }
            
            },
            'validateedit' : function(thisEditor, changes, rec, rowIdx) {
                //Logger("rec validate passed");
                
                return true;
            },
            'canceledit' : function(thisEditor, buttonPressed) {
                Logger("edit canceled. buttonPressed=", buttonPressed);
                
                // delete new row if canceled
                if (thisEditor.newRow) {
                    thisEditor.grid.store.remove(thisEditor.newRow);
                    delete(thisEditor.newRow);
                }

            }
        }
    });
    
    var selectModel = new PIJ.CRUD.SelectionModel({
        listeners: {
            rowselect: function(sm, row, rec) {
                if (opts.onselect) {
                    opts.onselect(sm, row, rec);
                    return;
                }
                Logger("selected:", rec);
            },
            
            rowdeselect: function(sm, row, rec) {
                if (opts.ondeselect) {
                    opts.ondeselect(sm, row, rec);
                    return;
                }
                Logger("deselected:", rec);
            }
        }
    });
    
    var lgView = new Ext.ux.grid.livegrid.GridView({
        nearLimit : 100,
        loadMask  : {
            msg :  'Buffering. Please wait...'
        }
    });
    
    var addButton = new Ext.Button({
        text: 'Add',
        iconCls: 'add',
        handler : function(btn, evn) {
            if (Ext.isDefined(editor.newRow)) {
                // TODO disable btn for visual clue? but then how to re-enable?
                return;
            }
            //Logger("add related: " + opts.relName);
            var templ = new store.recordType({
            
            });
            editor.newRow = templ;
            editor.stopEditing();
            store.insert(0, templ);
            editor.startEditing(0);
        }
    });
    
    var removeSelectedButton = new Ext.Button({
        iconCls: 'remove',
        text: 'Delete selected',
        handler: function(btn, ev) {
            var toRemove = selectModel.getSelections();
            if (!toRemove || !toRemove.length) {
                return;
            }
            
            // measure twice, cut once.
            Ext.Msg.confirm(
                'Delete selected '+opts.label,
                'Really delete ' + toRemove.length + ' ' + opts.label + '?',
                function(btnResp) {
                    //Logger(btnResp);
                    
                    if (btnResp === 'yes') {
            
                        for (var i=0; i<toRemove.length; i++) {
                            var rec = toRemove[i];
                            var id  = rec.id;
                            //Logger("DELETE "+id);
                            
                            // fire our own XHR, and remove from UI on success.
                            Ext.Ajax.request({
                                url: opts.url + '/' + id + '.json',
                                method: 'DELETE',
                                success: function() { 
                                    store.reload(); // not remove, since LG can get confused with multiples.
                                },
                                failure: function() {
                                    alert("Error deleting " + id);
                                }
                            });
            
                        }
                        
                    }
                }
            );
        }
    });
    
    var columns = [
        new Ext.grid.RowNumberer({header : '#' })
    ];
    
    var toolbar = new Ext.ux.grid.livegrid.Toolbar({
        view        : lgView,
        displayInfo : true
    });
    
    var plugins = [];
    
    if (allowEdits !== false) {
        toolbar.add(
            addButton,
            '-'
        );
        plugins.push(editor);
    }
    if (allowDeletes !== false) {
        columns.push(selectModel);
        toolbar.add(
            removeSelectedButton,
            '-'
        );
    }
    
    // add each column to array
    Ext.each(opts.colDefs, function(item) { columns.push(item) });
    
    var livegrid = new Ext.ux.grid.livegrid.GridPanel({
        id: 'PIJ-crud-editgrid-'+opts.relName,
        enableDragDrop : false,
        //frame          : true,
        plugins        : plugins,
        cm             : new Ext.grid.ColumnModel(columns),
        loadMask       : {
            msg : 'Loading...'
        },
        //width        : 585,
        height       : '250',
        selModel     : selectModel,
        stripeRows   : true,
        store        : store,
        view     : lgView,
        bbar     : toolbar,
        listeners : {
            'reconfigure' : function(thisGrid, theStore, theColModel) {
                //Logger("reconfigure livegrid:", theColModel);
            
            
            },
            'afterrender' : function(thisGrid) {
                //Logger("afterrender:", thisGrid);
            
            }
        }

    });
    
    //Logger("livegrid ok");

    return livegrid;
}

PIJ.CRUD.ListGrid = function(opts) {

    var store = new Ext.ux.grid.livegrid.Store({
        'autoLoad'  : true,
        'autoSave'  : false, // we handle this ourselves.
        'url'       : opts.url + '.json',
        'bufferSize': 300,
        'restful'   : true,  // use GET where appropriate
        'reader'    : new Ext.ux.grid.livegrid.JsonReader(),
        'writer'    : new Ext.data.JsonWriter({
            encode: true,
            writeAllFields: false
        }),
        'listeners'  : {
            'load' : function(thisStore, theRecords, theOpts) {
                //Logger("grid store load:", theOpts);
                
            },
            
            'metachange' : function(thisStore, theMeta) {
                //Logger("new meta:", theMeta);
            
            
            }
        
        
        }
        
    });
    
    var selectModel = new PIJ.CRUD.SelectionModel({
        listeners: {
            rowselect: function(sm, row, rec) {
            
                if (opts.onselect) {
                    opts.onselect(sm, row, rec);
                    return;
                }
                
                var formPanel = Ext.getCmp('PIJ-crud-formpanel');
                var bForm = formPanel.getForm();
            
                // if more than one selection, do not activate form for each.
                var sel = sm.getSelections();
                //Logger(sel);
                if (Ext.isArray(sel) && sel.length > 1) {
                    bForm.reset();
                    return;
                }
            
                //Logger("selected:", row, rec);
                bForm.loadRecord(rec);
                
                if (Ext.isDefined(opts.related)) {
                
                    // enable the related tabpanel
                    // get the parent wrapper
                    var formWrapper = Ext.getCmp('PIJ-crud-formwrapper');
                    
                    // remove any existing tabpanel
                    var existingTabPanel = formWrapper.findById('PIJ-crud-related');
                    formWrapper.remove(existingTabPanel);
                    
                    // add the new tabpanel
                    Ext.each(opts.related, function(item) {
                        item.url = opts.url + '/' + rec.id + '/' + item.relName;
                    });
                    //Logger(opts.related);
                    var tabPanel = PIJ.CRUD.RelatedPanel(
                        opts.related, 
                        opts.allowRelatedEdit, 
                        opts.allowRelatedDelete
                    );
                    formWrapper.add(tabPanel);
                    formWrapper.doLayout();
                
                }
            },
            
            rowdeselect: function(sm, row, rec) {
            
                if (opts.ondeselect) {
                    opts.ondeselect(sm, row, rec);
                    return;
                }
            
                var bForm = Ext.getCmp('PIJ-crud-formpanel').getForm();
                bForm.reset();
                
                // disable the related tabpanel
                var tabPanel = Ext.getCmp('PIJ-crud-related');
                if (tabPanel && !tabPanel.disabled) {
                    tabPanel.disable();
                }
            }
        }
    });
    
    var lgView = new Ext.ux.grid.livegrid.GridView({
        nearLimit : 100,
        loadMask  : {
            msg :  'Buffering. Please wait...'
        }
    });
    
    var addButton = new Ext.Button({
        text: 'Add',
        iconCls: 'add',
        handler : function(btn, evn) {
            //Logger("new Program");
            var bForm = Ext.getCmp('PIJ-crud-formpanel').getForm();
            bForm.reset();
            bForm.isValid();    // trigger visual clues
        }
    });
    
    var removeSelectedButton = new Ext.Button({
        iconCls: 'remove',
        text: 'Delete selected',
        handler: function(btn, ev) {
            var toRemove = selectModel.getSelections();
            if (!toRemove || !toRemove.length) {
                return;
            }
            
            // measure twice, cut once.
            Ext.Msg.confirm(
                'Delete selected '+opts.title,
                'Really delete ' + toRemove.length + ' ' + opts.title + '?',
                function(btnResp) {
                    //Logger(btnResp);
                    
                    if (btnResp === 'yes') {
            
                        for (var i=0; i<toRemove.length; i++) {
                            var rec = toRemove[i];
                            var id  = rec.id;
                            //Logger("DELETE "+id);
                            
                            // fire our own XHR, and remove from UI on success.
                            Ext.Ajax.request({
                                url: opts.url + '/' + id + '.json',
                                method: 'DELETE',
                                success: function() { 
                                    store.reload(); // not remove, since LG can get confused with multiples.
                                },
                                failure: function() {
                                    alert("Error deleting " + id);
                                }
                            });
            
                        }
                        
                    }
                }
            );
        }
    });
    
    var columns = [
        new Ext.grid.RowNumberer({header : '#' })
    ];
        
    var bbar_items = [];
    if (opts.allowEdit !== false) {
        bbar_items.push(
            addButton,
            '-'
        );
    }
    if (opts.allowDelete !== false) {
        bbar_items.push(
            removeSelectedButton,
            '-'
        );
        columns.push(selectModel);
    }
    
    Ext.each(opts.colDefs, function(item) { columns.push(item) });

    var livegrid = new Ext.ux.grid.livegrid.GridPanel({
        id: 'PIJ-crud-listgrid',
        enableDragDrop : false,
        cm             : new Ext.grid.ColumnModel(columns),
        loadMask       : {
            msg : 'Loading...'
        },
        //width        : 585,
        height       : 465,
        selModel     : selectModel,
        stripeRows   : true,
        store        : store,
        view     : lgView,
        bbar     : new Ext.ux.grid.livegrid.Toolbar({
            view        : lgView,
            displayInfo : true,
            items: bbar_items
        }),
        listeners : {
            'reconfigure' : function(thisGrid, theStore, theColModel) {
                //Logger("reconfigure livegrid:", theColModel);
            
            
            },
            'afterrender' : function(thisGrid) {
                //Logger("afterrender:", thisGrid);
            
            }
        }

    });
    
    //Logger("livegrid ok");

    return livegrid;
}


PIJ.CRUD.FormPanel = function(opts) {

    var f = new Ext.FormPanel({
        id: 'PIJ-crud-formpanel',
        //title: 'Details',
        border: false,
        frame: true,
        //width: 460,
        items: [
          {
            xtype: 'fieldset',
            labelWidth: 90,
            title: 'Details',
            // Default config options for child items
            defaults: {
                width: 350, 
                border:false
            },
            defaultType: 'textfield',
            //autoHeight: true,
            border: false,
            items: opts.fieldDefs
          }
        ]
    });
    
    // if no fields are enabled, do not show button
    var disabled  = 0;
    Ext.each(opts.fieldDefs, function(item) {
        if (Ext.isDefined(item.xtype) && item.xtype == 'displayfield') {
            disabled++;
        }
        else if (item.disabled) {
            disabled++;
        }
    });
    
    if (opts.fieldDefs.length !== disabled) {
        f.addButton(new PIJ.CRUD.XHR_Button({
            'uuidField'  : opts.uuidField,
            'baseURL'    : opts.url
        }));
    }
    
    f.getForm().on('beforeaction', function(thisForm, act) {
        Logger("beforeaction: ", act);
        return true;
    });
        
    var panel = new Ext.Panel({
        id: 'PIJ-crud-formwrapper',
        border: false,
        defaults: {
            border: false
        },
        items: [
            f
        ]
    });
    
    return panel;
}

PIJ.CRUD.RecordEditor = function(opts) {

    if (!Ext.isDefined(opts.allowRelatedEdit)) {
        opts.allowRelatedEdit = true;
    }
    if (!Ext.isDefined(opts.allowRelatedDelete)) {
        opts.allowRelatedDelete = true;
    }
    if (!Ext.isDefined(opts.allowEdit)) {
        opts.allowEdit = true;
    }
    if (!Ext.isDefined(opts.allowDelete)) {
        opts.allowDelete = true;
    }

    var formPanel = PIJ.CRUD.FormPanel(opts);
    if (Ext.isDefined(opts.related)) {
        var tabPanel = PIJ.CRUD.RelatedPanel(opts.related, opts.allowRelatedEdit);
        formPanel.add(tabPanel);
    }
    
    var north = {
        id: 'PIJ-crud-north',
        region: 'north',
        height: 27,
        frame: false,
        border: false,
        defaults: {
            border: false
        },
        items: opts.navigation
    };
    
    var south = {
        id: 'PIJ-crud-south',
        region: 'south',
        height: 25,
        collapsible: false,
        border: true,
        html: 'this is the south region'
    };
            
    var center = new Ext.Panel({
        region: 'center',
        id: 'PIJ-crud-center',
        minWidth: 350,
        title: opts.title,
        items: [ formPanel ]
    });
    
    //Logger("doing Viewport");

    //  whole page layout here
    PIJ.Viewport = new Ext.Viewport({
        layout: 'border',
        split: true,
        defaults: {
            border: false,
            split: true
        },
        items: [
            north,
            south,
            center
        ]
    });

}
