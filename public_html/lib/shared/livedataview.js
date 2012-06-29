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

Ext.namespace('PIN');

if (typeof Logger != 'function') {
    Logger = function() { } // no-op
}

/**
 * PIN.LiveDataView
 *
 * @param config Configuration options
 * ~store (PIN.LiveJsonStore)
 * ~tpl (Ext.Template) template to render A SINGLE ROW, not the entire store. In
 *   other words, don't include a for="." in your template!
 * ~bufferAmount (int, default:1) number of pages to buffer on either side of
 *   the pages in the viewport. For example, if you're viewing pages 3 and 4,
 *   the buffer would contain pages 1-4.
 * ~prependStatic (string) an html fragment to render before each row in the tpl
 * ~appendStatic (string) an html fragment to render after each row in the tpl
 */
PIN.LiveDataView = function(config) {
    if (!config) config = {};

    this.bufferAmount = config.bufferAmount || 1;
    this.pagesLoadedStack = [];
    this.pagesRequestedStack = [];

    // create row template
    var bodyHtml = (config.prependStatic || '') + '{0}' + (config.appendStatic || '');
    this.itemSelector = (config.itemSelector || this.itemSelector);
    this.rowTpl = new Ext.DomHelper.createTemplate({
        tag: 'div',
        cls: this.itemSelector.substring(1) + ' {1}',
        html: bodyHtml
    });
    this.rowTpl.compile(); //improve render time

    if (!config.listeners) config.listeners = {};
    config.listeners.render = function(dview) {
    	dview.el.setHeight('100%'); // to fix load mask height
        this.initScroller(dview.el); // initialize the scroller listener
    }

    // call parent constructor
    PIN.LiveDataView.superclass.constructor.call(this, config);

    // event handlers
    this.on('destroy',  function() {
        if (this.maskEl) this.maskEl.remove();
    }, this);
    this.on('beforeclick', function(dview, idx, node, e) {
        if (dview.multiSelect) {
            if (dview.allSelected) {
                if (e.shiftKey || e.ctrlKey) return false;
                dview.allSelected = false;
            }
            else if (e.shiftKey) {
                // for now, only allow shift-clicking on the same pagenum
                // TODO: find a way to allow spanning non-loaded pages (server side)
                if (dview.getRecord(node).pageNumber != dview.lastClickedPage) {
                    return false;
                }
            }
            dview.lastClickedPage = dview.getRecord(node).pageNumber;
        }
    });

    // fire the first page load RIGHT AWAY (before render) IF not inline-loaded
    this.topPage = 0;
    this.bottomPage = 0;
    if (!this.store.hasRequested) {
        this.pagesRequestedStack.push(0);
        this.store.loadPage(0);
    }
}
Ext.extend(PIN.LiveDataView, Ext.DataView, {
    cls: 'pin-livedataview',
    deferEmptyText: true,
    emptyText: 'No data to display',
    autoScroll: true,
    blockRefresh: true, // IMPORTANT!
    itemSelector: '.pin-livedataview-selector',
    initPages: function(recs) {
        //first, buffer the records
        if (!this.pageElementMap) {
            this.el.update('');
            var firstPage = this.el.createChild({
                cls: 'livedataview-page'
            });
            this.pageElementMap = [firstPage];
            this.onAdd(null, recs, 0);
        }

        // measure the height of the page -- NOTE: this doesn't always show up
        // right away, and there's not a good event to listen for, so just keep
        // trying after a delay until it works
        var firstHeight = this.pageElementMap[0].getHeight();
        if (firstHeight == 0 && recs.length > 0) {
            var task = new Ext.util.DelayedTask(function() {
                this.initPages(recs);
            }, this);
            task.delay(100); // try again in 100 ms
            return;
        }

        // calculate page heights
        if (recs.length > 0) {
            this.rowHeight = firstHeight / this.store.getCount();
            this.pageHeight = this.rowHeight * this.store.pageSize;
            this.totalHeight = this.rowHeight * this.store.getTotalCount();
            this.totalPages = Math.ceil(this.store.getTotalCount() / this.store.pageSize);
        }
        else {
            this.rowHeight = 0;
            this.pageHeight = 0;
            this.totalHeight = 0;
            this.totalPages = 1;
        }

        // create the empty pages
        if (this.totalPages > 1) {
            this.pageElementMap[0].setHeight(this.pageHeight);
            for(var i=1; i<this.totalPages; i++) {
                this.pageElementMap.push(this.el.createChild({
                    cls: 'livedataview-page',
                    style: 'height: '+this.pageHeight+'px;'
                    // comment-in next for debugging ui
                    //style: 'height: '+this.pageHeight+'px; border: solid red 1px;'
                }));
            }

            // fix the height of the last page
            if (this.store.getTotalCount() % this.store.pageSize !== 0) {
                var lastHeight = this.totalHeight % this.pageHeight;
                this.pageElementMap[this.totalPages-1].setHeight(lastHeight);
            }
        }

        // unmask the inital load
        this.refreshLoadMask();
    },
    initScroller: function(scrollEl) {
        // delayed function to prevent loading lots of stuff on a drag-scroll
        var bufferFn = new Ext.util.DelayedTask(function() {
            this.showPages(this.topPage, this.bottomPage, true);
            this.cleanBuffers(this.topPage, this.bottomPage);
        }, this);

        // setup scrolling listener
        scrollEl.on('scroll', function(event, htmlEl, options) {
            var top = scrollEl.getScroll().top;
            this.topPage = Math.floor(top / this.pageHeight);
            this.bottomPage = Math.floor((top + scrollEl.getHeight()) / this.pageHeight);
            this.refreshLoadMask(); //mask if current page isn't loaded yet
            bufferFn.delay(200); // wait 200ms for any scrolling to stop
        }, this);
    },
    refreshLoadMask: function() {
        if (!this.maskEl) {
            this.maskEl = this.el.insertSibling({
                cls: 'livedataview-loading-ct',
                style: 'visibility:hidden',
                html: '<div class="mask"></div>' +
                    '<div class="text"><div>Loading...</div></div>'
            });
            if (!this.maskEl) return;
        }

        // check if we're looking at a non-loaded page
        var showMask = false;
        if (this.store.getTotalCount() > 0) {
            for(var i=this.topPage; i<=this.bottomPage; i++) {
                if (this.pagesLoadedStack.indexOf(i) < 0) showMask = true;
            }
        }
        if (showMask && !this.maskEl.isVisible()) {
            // resize if non-zero
            var sz = this.el.getSize(true);
            if (sz.height != 0 && sz.width != 0) {
                this.maskEl.setSize(this.el.getSize(true));
            }
            this.maskEl.show();
        }
        else if (!showMask && this.maskEl.isVisible()) {
            this.maskEl.hide();
        }
    },
    showPages: function(rangeLow, rangeHigh, loadBuffers) {
    	if (!this.isVisible()) {
            Logger("INVISIBLE!!");
            //return; // loading doesn't really seem to be a problem
    	}

        rangeLow = Math.max(rangeLow, 0);
        if (this.totalPages) //in case we haven't gotten a total yet
            rangeHigh = Math.min(rangeHigh, this.totalPages-1);

        // first check/load in the page(s) we're looking at
        for (var i=rangeLow; i<=rangeHigh; i++) {
            if (this.pagesLoadedStack.indexOf(i) < 0
                    && this.pagesRequestedStack.indexOf(i) < 0) {
                this.pagesRequestedStack.push(i);
                this.store.loadPage(i);
            }
        }

        // optionally load the buffers
        if (loadBuffers && this.bufferAmount > 0) {
            //load the post-buffer
            this.showPages(rangeHigh + 1, rangeHigh + this.bufferAmount, false);

            //load the pre-buffer
            this.showPages(rangeLow - this.bufferAmount, rangeLow - 1, false);
        }
    },
    cleanBuffers: function(rangeLow, rangeHigh) {
        // cleanup our buffered pages, removing pages that aren't in-range
        for (var i=0; i<this.pagesLoadedStack.length; i++) {
            if (this.pagesLoadedStack[i] < (rangeLow - this.bufferAmount) ||
                    this.pagesLoadedStack[i] > (rangeHigh + this.bufferAmount)) {
                var rmvPage = this.pagesLoadedStack[i];
                this.pagesLoadedStack.remove(rmvPage);
                Logger("--- Removing "+rmvPage+" ---");
                Logger(this.pagesLoadedStack);
                this.store.removePage(rmvPage);
                i--; // decrement, for removed item
                this.pageElementMap[rmvPage].update(''); // clear DOM, just in case...
            }
        }
        // cleanup requested pages
        for (var j=0; j<this.pagesRequestedStack.length; j++) {
            if (this.pagesRequestedStack[i] < (rangeLow - this.bufferAmount) ||
                    this.pagesRequestedStack[i] > (rangeHigh + this.bufferAmount)) {
                rmvPage = this.pagesRequestedStack[i];
                Logger("CANCELLING PAGE REQUEST" + rmvPage);
                this.pagesRequestedStack.remove(rmvPage);
                this.store.removePage(rmvPage);
                j--; // decrement, for removed item
            }
        }
    },
    onAdd: function(ds, records, index) {
        if (!this.rendered) {
            // re-try adding after render
            this.on('render', function() {
                this.onAdd(ds, records, index);
            }, this, {single:true});
            return;
        }

        // calculate paging on the initial load
        if (!this.pageElementMap) {
            this.initPages(records);
            return;
        }

        // if the just adding one phantom (new) record, only render the div
        if (records.length == 1 && records[0].phantom) {
            var add = this.rowTpl.insertFirst(this.pageElementMap[0], ['', '']);
            this.all.elements.splice(0, 0, add);
            this.updateIndexes(0);
            return;
        }

        // setup the pagenumber to load --- if none given
        var pageNum = (records.length > 0) ? records[0].pageNumber : 0;
        var pageEl = this.pageElementMap[pageNum];
        this.pagesRequestedStack.remove(pageNum);
        this.pagesLoadedStack.push(pageNum);

        // do the add operation
        if (!this.initialRender && this.all.getCount() === 0) {
            this.initialRender = true;
            this.refresh();
            return;
        }
        var nodes = this.bufferRender(records, index), a = this.all.elements;
        if(index < this.all.getCount()) {
            pageEl.appendChild(nodes);
            a.splice.apply(a, [index, 0].concat(nodes));
        }
        else {
            pageEl.appendChild(nodes);
            a.push.apply(a, nodes);
        }
        this.updateIndexes(index);
        this.refreshLoadMask(); // check the loading mask

        // if we're in "select all" mode, select the elements
        if (this.allSelected) {
            this.select(nodes, true);
        }
        if (this.cachedSel && this.cachedSel.length > 0) {
            //TODOTODOTODOTODOTODOTODO
        }

        Logger("--- Added "+pageNum+" ---", this.pagesLoadedStack);
    },
    onRemove: function(ds, record, index) {
        if (record.removingPage && this.isSelected(index)) {
            if (!this.cachedSel) this.cachedSel = [];
            this.cachedSel.push(record);
        }
        this.deselect(index);
        this.all.removeElement(index, true);
        this.updateIndexes(index);

        // clear dataview if this was the only record
        if (ds.getCount() == 0) this.clearDataView(true);
    },
    onUpdate : function(ds, record){
        // avoid updating dirty records --- wait until they're saved by
        // the remote server
        if (!record.dirty) {
            PIN.LiveDataView.superclass.onUpdate.call(this, ds, record);
        }
    },
    bufferRender : function(records){
        var div = document.createElement('div');
        var elements = [];
        var i = 0;
        Ext.each(records, function(rec) {
            rec.data.index = rec.index;
            var rowHtml = this.tpl.apply(rec.data);
            elements.push(this.rowTpl.append(div, [rowHtml, (i%2==0) ? '' : 'row-alternate']));
            i++;
        }, this);
        return elements;
    },
    refresh: function() {
        this.clearSelections(false, true);
        if (this.store.getCount() < 1) {
            if (!this.deferEmptyText || this.hasSkippedEmptyText || this.store.loadedEmpty) {
                if (this.maskEl) this.maskEl.hide();
            	if (this.pageElementMap && this.pageElementMap.length > 0) {
            	    this.pageElementMap[0].update(this.emptyText);
            	}
            	else {
            	    this.el.update(this.emptyText);
            	}
            }
            this.all.clear();
        } else {
            // check for store loading before DV renders... call onAdd manually
            if (!this.pageElementMap) {
                this.onAdd(null, this.store.getRange(), 0);
                return;
            }

            this.all.clear();
            var i = 0;
            this.store.each(function(rec) {
                //Logger("LDV rec:", rec);
                rec.data.index = rec.index;
                var el = this.pageElementMap[rec.pageNumber];
                var rowHtml = this.tpl.apply(rec.data);
                this.all.add(this.rowTpl.append(el, [rowHtml, (i%2==0) ? '' : 'row-alternate']));
                i++;
            }, this);
            this.updateIndexes(0);
        }
        this.hasSkippedEmptyText = true;
    },
    clearDataView: function(noMask) {
        this.pagesLoadedStack = [];
        this.pagesRequestedStack = [];
        delete this.pageElementMap;
        this.store.removeAll(true); //silent
        this.clearSelections(true); //silent
        this.el.update(''); //destroy everything
        this.all.clear();
        this.hasSkippedEmptyText = false;
        if (!noMask) this.refreshLoadMask(); // set the loading mask
        this.initialRender = false;
    },
    reloadDataView: function(noMask) {
        this.clearDataView(noMask);
        this.showPages(0, 0, false);
    },
    selectAll: function() {
        this.allSelected = true;
        this.select(this.getNodes());
    },
    getSelectionCount: function() {
        if (this.allSelected) {
            return this.store.getTotalCount();
        }
        else if (this.cachedSel && this.cachedSel.length > 0) {
            return this.selected.getCount() + this.cachedSel.length;
        }
        else {
            return PIN.LiveDataView.superclass.getSelectionCount.call(this);
        }
    },
    getSelectedRecords: function() {
        if (this.allSelected) {
            return -1; //just return an indicator that EVERYTHING is selected
        }
        else if (this.cachedSel && this.cachedSel.length > 0) {
            var recs = PIN.LiveDataView.superclass.getSelectedRecords.call(this);
            for (var i=0; i<this.cachedSel.length; i++) {
                recs[recs.length] = this.cachedSel[i];
            }
            return recs;
        }
        else {
            return PIN.LiveDataView.superclass.getSelectedRecords.call(this);
        }
    },
    clearSelections: function(suppress, skip) {
        if (this.cachedSel && this.cachedSel.length > 0) {
            this.cacheSel = false;
        }
        PIN.LiveDataView.superclass.clearSelections.call(this, suppress, skip);
    },
    onItemClick: function(item, index, e) {
        // allow regular clicking on links
        var clickTarget = e.getTarget();
        if (Ext.isDefined(clickTarget.href)) return;

        PIN.LiveDataView.superclass.onItemClick.call(this, item, index, e);
    }
});
Ext.reg('livedataview', PIN.LiveDataView);


/**
 * PIN.LiveJsonStore
 *
 * @param config Configuration options
 * ~pageSize
 */
PIN.LiveJsonStore = function(config) {
    this.pageSize = config.pageSize || 100;
    this.pageMap = new Array();
    this.queueRemove = new Array();
    config.autoLoad = false; // force manual pagewise loading

    // call parent constructor
    PIN.LiveJsonStore.superclass.constructor.call(this, config);

}
Ext.extend(PIN.LiveJsonStore, Ext.data.JsonStore, {
    loadPage: function(pageNum) {
        this.hasRequested = true;
        this.load({
            params: {start: (pageNum * this.pageSize), limit: this.pageSize},
            pageNumber: pageNum
        });
    },
    removePage: function(pageNum) {
        if (this.pageMap[pageNum]) {
            Ext.each(this.pageMap[pageNum], function(rec) {
                rec.phantom = true; // prevent a DELETE from firing
                rec.removingPage = true;
                this.remove(rec);
            }, this);
        } else {
            this.queueRemove.push(pageNum);
        }
    },
    loadRecords: function(o, options, success) {
        // set totallength first, so events have the correct count
        this.totalLength = o.totalRecords || o.records.length;

        if (success)
            this.fireEvent('load', this, [], options);

        if (!success) {
            this.loadedEmpty = true;
            this.fireEvent('add', this, [], 0);
            return;
        }
        if (!Ext.isDefined(options.pageNumber)) {
            if (this.hasRequested) {
                Logger("ERROR: I only know how to load page-wise");
                return;
            } else {
                // loading inline data!
                options.pageNumber = 0;
                this.hasRequested = true;
            }
        }

        // check if this page has been queued to remove while we were loading it
        if (this.queueRemove.indexOf(options.pageNumber) > -1) {
            this.queueRemove.remove(options.pageNumber);
            return;
        }

        // mark these records with the page they came in on
        Ext.each(o.records, function(rec, idx) {
            rec.index = (options.pageNumber*this.pageSize) + idx;
            rec.pageNumber = options.pageNumber;
        }, this);

        this.pageMap[options.pageNumber] = o.records;

        // check for an empty page 0 (manually fire 'add' event)
        if (options.pageNumber == 0 && o.records.length < 1) {
            this.loadedEmpty = true;
            this.fireEvent('add', this, [], 0);
        }
        else {
            // find the first record of a greater pagenum than these records
            var idx = this.findBy(function(rec, id) {
                return (options.pageNumber < rec.pageNumber);
            });
            if (idx < 0) {
                this.add(o.records);
            }
            else {
                this.insert(idx, o.records);
            }
        }
    }
});
Ext.reg('livejsonstore', PIN.LiveJsonStore);
