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
    
    this.sm = new Ext.grid.CheckboxSelectionModel({
        listeners: {
            'rowselect': function(sm,rowIndex,record) {
                this.rememberRow(record);
            },scope: this
            ,'rowdeselect': function(sm,rowIndex,record) {
                this.forgetRow(record);
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
        ,grouping: true
        ,groupBy: 'parent'
        ,singleText: _('goodnews.mailing_rc_resource')
        ,pluralText: _('goodnews.mailing_rc_resources')
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
            ,hidden: true
            ,width: 120
            ,sortable: true
        }]
        ,listeners: {
            // little workaround as we cant differentiate if store is loaded for the first time or refreshed
            'click': {fn:this.makeDirty,scope:this}
        }
	});
	GoodNewsResource.grid.CollectResources.superclass.constructor.call(this,config);
    this.store.on('load',this.restoreSelection,this);
    this.getView().on('refresh',this.refreshSelection,this);
};
Ext.extend(GoodNewsResource.grid.CollectResources,MODx.grid.Grid,{
    selectionRestoreFinished: false
    ,selectedRecords: []
    ,rememberRow: function(record) {
        if(this.selectedRecords.indexOf(record.id) == -1){
            this.selectedRecords.push(record.id);
        }
    }
    ,forgetRow: function(record) {
        this.selectedRecords.remove(record.id);
    }
    ,refreshSelection: function() {
        var rowsToSelect = [];
        Ext.each(this.selectedRecords,function(item){
            rowsToSelect.push(this.store.indexOfId(item));
        },this);
        this.getSelectionModel().selectRows(rowsToSelect);
    }
    ,getSelectedAsList: function() {
        return this.selectedRecords.join();
    }
    ,restoreSelection: function() {
        // only execute on initial store load!
        if (!this.selectionRestoreFinished) {
            var collection = this.config.baseParams.collectionIds.split(',');
            var rowIndex;
            for (var i=0; i<collection.length; ++i) {
                rowIndex = this.store.find('id',collection[i]);
                this.getSelectionModel().selectRow(rowIndex,true);
            }
            this.refresh(); 
            this.selectionRestoreFinished = true;
        }
    }
    ,makeDirty: function() {
        Ext.getCmp('goodnewsresource-'+this.config.baseParams.collectionInternalName).fireEvent('change');
    }
});
Ext.reg('goodnewsresource-grid-collect-resources',GoodNewsResource.grid.CollectResources);
