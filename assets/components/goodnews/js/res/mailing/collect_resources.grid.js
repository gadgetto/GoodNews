/**
 * Grid to collect resource content
 * 
 * @class GoodNewsResource.grid.CollectResources
 * @extends MODx.grid.Grid
 * @param {Object} config An object of config properties
 * @xtype goodnewsresource-grid-collect-resources
 */
GoodNewsResource.grid.CollectResources = function(config) {
    config = config || {};
    
    this.key = config.baseParams.collectionInternalName;
    this.selectedRecords = [];
    this.selectedRecords[this.key] = [];
    this.selectionRestoreFinished = false;
    
    // Initially fill selectedRecords array with restored ids from database
    // (converted to integer as the store holds its record ids as integers!)
    var tmpArray = config.baseParams.collectionIds.split(',');
    var len = tmpArray.length;
    for (var i=0; i<len; ++i) {
        var id = parseInt(tmpArray[i],10);
        this.rememberRow(id);
    }
    
    this.sm = new Ext.grid.CheckboxSelectionModel({
        listeners: {
            'rowselect': function(sm,rowIndex,record) {
                this.rememberRow(record.id);
            },scope: this
            ,'rowdeselect': function(sm,rowIndex,record) {
                this.forgetRow(record.id);
            },scope: this
        }
    });
        
    this.exp = new Ext.grid.RowExpander({
        tpl : new Ext.Template(
            '<p>{preview}</p>'
        )
    });

	Ext.applyIf(config,{
		title: _('resources_active')
		,id: 'goodnewsresource-'+config.baseParams.collectionInternalName+'-grid'
        ,url: GoodNewsResource.connector_url
		,fields: [
		    'id'
		    ,'pagetitle'
		    ,'preview'
		    ,'publishedon'
		    ,'createdon'
		    ,'parent'
        ]
        ,cls: 'main-wrapper'
        ,remoteSort: true
        ,sortBy: 'publishedon' // default sort field of grouped grid (this is not the grouping field!)
        ,sortDir: 'DESC'       // (the grouping field is pre-sorted in getlist processor)
		,paging: true
        ,emptyText: _('goodnews.mailing_rc_resources_none')
        ,preventRender: true
        ,autoExpandColumn: 'pagetitle'
        ,sm: this.sm
        ,plugins: [this.exp]
        ,columns: [
        this.sm
        ,this.exp
        ,{
            header: _('id')
            ,dataIndex: 'id'
            ,width: 40
            ,sortable: true
        },{
            header: _('page_title')
            ,dataIndex: 'pagetitle'
            ,width: 260
            ,sortable: true
        },{
            header: _('publishedon')
            ,dataIndex: 'publishedon'
            ,width: 90
            ,sortable: true
        },{
            header: _('createdon')
            ,dataIndex: 'createdon'
            ,width: 90
            ,sortable: true
        },{
            header: _('goodnews.mailing_rc_container')
            ,dataIndex: 'parent'
            ,width: 120
            ,sortable: true
        }]
        ,tbar:['->',{
            xtype: 'modx-combo'
            ,id: 'goodnewsresource-'+config.baseParams.collectionInternalName+'-parent-filter'
            ,emptyText: _('goodnews.mailing_rc_parent_filter')
            ,width: 300
            ,listWidth: 300
            ,displayField: 'container'
            ,valueField: 'id'
            ,value: 'all'
            ,store: new Ext.data.JsonStore({
                url: GoodNewsResource.connector_url
                ,baseParams: {
                    action: 'mgr/collection/getParentList'
                    ,addAllOption: true
                    ,parentIds: config.baseParams.parentIds
                }
                ,fields: ['id','container']
                ,root: 'results'
            })
            ,listeners: {
                'select': {fn: this.filterByParent, scope: this}
            }
        }]
        ,listeners: {
            // little workaround as we cant differentiate if store is loaded for the first time or refreshed
            'click': {fn:this.makeDirty,scope:this}
        }
	});
	GoodNewsResource.grid.CollectResources.superclass.constructor.call(this,config);
    this.store.on('load',this.refreshSelection,this);
};
Ext.extend(GoodNewsResource.grid.CollectResources,MODx.grid.Grid,{
    rememberRow: function(resourceid) {
        if (this.selectedRecords[this.key].indexOf(resourceid) == -1){
            this.selectedRecords[this.key].push(resourceid);
        }
    }
    ,forgetRow: function(resourceid) {
        this.selectedRecords[this.key].remove(resourceid);
    }
    ,refreshSelection: function() {
        var rowsToSelect = [];        
        Ext.each(this.selectedRecords[this.key],function(item){
            rowsToSelect.push(this.store.indexOfId(item));
        },this);       
        Ext.getCmp(this.config.id).getSelectionModel().selectRows(rowsToSelect);
        
        // workaround:
        // @todo: find fix for: checkboxes are only checked in first grid
        if (!this.selectionRestoreFinished) {
            this.refresh(); 
            this.selectionRestoreFinished = true;
        }
    }
    ,getSelectedAsList: function(){
        return this.selectedRecords[this.key].join();
    }
    ,makeDirty: function() {
        Ext.getCmp('goodnewsresource-'+this.config.baseParams.collectionInternalName).fireEvent('change');
    }
    ,filterByParent: function(combo) {
        var s = this.getStore();
        s.baseParams.parentfilter = combo.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }
});
Ext.reg('goodnewsresource-grid-collect-resources',GoodNewsResource.grid.CollectResources);
