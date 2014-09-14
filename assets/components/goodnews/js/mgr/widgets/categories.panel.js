/**
 * Panel to manage categories.
 * 
 * @class GoodNews.panel.Categories
 * @extends Ext.Panel
 * @param {Object} config An object of options.
 * @xtype goodnews-panel-categories
 */
GoodNews.panel.Categories = function(config) {
    config = config || {};

    Ext.applyIf(config,{
        id: 'goodnews-panel-categories'
        ,title: _('goodnews.categories')
        ,layout: 'anchor'
        ,defaults: { 
            border: false 
        }
        ,items:[{
            html: '<p>'+_('goodnews.categories_management_desc')+'</p>'
            ,border: false
            ,bodyCssClass: 'panel-desc'
        },{
            xtype: 'goodnews-grid-categories'
            ,cls: 'main-wrapper'
            ,preventRender: true
        }]
        ,listeners: {
            'activate': {fn: function() {
                Ext.getCmp('goodnews-grid-categories').refresh();
            }
            ,scope: this}
        }
    });
    GoodNews.panel.Categories.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.panel.Categories,Ext.Panel);
Ext.reg('goodnews-panel-categories', GoodNews.panel.Categories);


/**
 * Grid which lists categories.
 * 
 * @class GoodNews.grid.Categories
 * @extends MODx.grid.Grid
 * @param {Object} config An object of options.
 * @xtype goodnews-grid-categories
 */
GoodNews.grid.Categories = function(config) {
    config = config || {};
    
    Ext.applyIf(config,{
        id: 'goodnews-grid-categories'
        ,url: GoodNews.config.connectorUrl
        ,baseParams: { action: 'mgr/category/getList' }
        ,fields: [
            'id'
            ,'name'
            ,'description'
            ,'goodnewsgroup_id'
            ,'goodnewsgroup_name'
            ,'membercount'
            ,'public'
            ,'menu'
        ]
        ,grouping: true
        ,groupBy: 'goodnewsgroup_name'
        ,singleText: _('goodnews.category')
        ,pluralText: _('goodnews.categories')
        ,primaryKey: 'name'
        ,sortBy: 'name'
        ,sortDir: 'ASC'
        ,paging: true
        ,remoteSort: true
        ,emptyText: _('goodnews.categories_none')
        ,save_action: 'mgr/category/updateFromGrid'
        ,autosave: true
        ,autoExpandColumn: 'description'
        ,collapseFirst: false
        ,columns: [{
            header: _('goodnews.id')
            ,dataIndex: 'id'
            ,sortable: true
            ,width: 40
        },{
            header: _('goodnews.category_name')
            ,dataIndex: 'name'
            ,sortable: true
            ,width: 150
            ,editable: true
            ,editor: { xtype: 'textfield' }
        },{
            header: _('goodnews.category_description')
            ,dataIndex: 'description'
            ,sortable: false
            ,width: 250
            ,editable: true
            ,editor: { xtype: 'textfield' }
        },{
            header: _('goodnews.category_usergroup')
            ,dataIndex: 'goodnewsgroup_name'
            ,sortable: true
            ,hidden: true
            ,width: 100
            ,editable: false
        },{
            header: _('goodnews.category_public')
            ,dataIndex: 'public'
            ,align: 'center'
            ,sortable: false
            ,width: 80
            ,editable: true
            ,editor: { 
                xtype: 'combo-boolean'
                ,renderer: 'boolean'
            }
        },{
            header: _('goodnews.category_membercount')
            ,dataIndex: 'membercount'
            ,align: 'center'
            ,sortable: false
            ,width: 100
            ,editable: false
        }]
        ,tbar:[{
            text: _('goodnews.category_create')
            ,handler: this.addCategory
            ,scope: this
            ,cls: 'primary-button'
        },'->',{
            xtype: 'modx-combo'
            ,id: 'goodnews-categories-group-filter'
            ,emptyText: _('goodnews.subscribers_user_group_filter')
            ,width: 200
            ,listWidth: 200
            ,displayField: 'name'
            ,valueField: 'id'
            ,store: new Ext.data.JsonStore({
                url: GoodNews.config.connectorUrl
                ,baseParams: {
                    action : 'mgr/groups/getGroupFilterList'
                }
                ,fields: ['id','name']
                ,root: 'results'
            })
            ,listeners: {
                'select': {fn: this.filterByUserGroup, scope: this}
            }
        },'-',{
            xtype: 'textfield'
            ,cls: 'x-form-filter'
            ,id: 'goodnews-categories-search-filter'
            ,emptyText: _('goodnews.input_search_filter')
            ,listeners: {
                'change': {fn: this.search, scope: this}
                ,'render': {fn: function(cmp) {
                    new Ext.KeyMap(cmp.getEl(), {
                        key: Ext.EventObject.ENTER
                        ,fn: function() {
                            this.fireEvent('change',this);
                            this.blur();
                            return true;
                        }
                        ,scope: cmp
                    });
                },scope:this}
            }
        },{
            xtype: 'button'
            ,id: 'goodnews-categories-filter-clear'
            ,cls: 'x-form-filter-clear'
            ,text: _('goodnews.button_filter_clear')
            ,listeners: {
                'click': {fn: this.clearFilter, scope: this}
            }
        }]
    });
    GoodNews.grid.Categories.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.grid.Categories,MODx.grid.Grid,{
    getMenu: function() {
        var r = this.getSelectionModel().getSelected();
        var p = r.data.perm;

        return [{
            text: _('goodnews.category_update')
            ,handler: this.updateCategory
        },{
            text: _('goodnews.category_remove')
            ,handler: this.removeCategory
        }];
    }
    ,addCategory: function(btn,e) {
        var win = MODx.load({
            xtype: 'goodnews-window-category'
            ,listeners: {
                success: {fn: function(r) {
                    this.refresh();
                },scope: this}
                ,scope: this
            }
        });
        win.show(e.target);
    }
    ,updateCategory: function(btn,e) {
        var win = MODx.load({
            xtype: 'goodnews-window-category'
            ,listeners: {
                success: {fn: function(r) {
                    this.refresh();
                },scope: this}
                ,scope: this
            }
            ,isUpdate: true
        });
        win.setValues(this.menu.record);
        win.show(e.target);
    }
    ,removeCategory: function() {
        MODx.msg.confirm({
            title: _('goodnews.category_remove')
            ,text: _('goodnews.category_remove_confirm')
            ,url: this.config.url
            ,defaultButton: 'no'
            ,params: {
                action: 'mgr/category/remove'
                ,id: this.menu.record.id
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }
    ,filterByUserGroup: function(combo) {
        var s = this.getStore();
        s.baseParams.groupfilter = combo.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }
    ,search: function(tf,nv,ov) {
        var s = this.getStore();
        s.baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }
    ,clearFilter: function() {
    	this.getStore().baseParams = {
            action: 'mgr/category/getList'
    	};
        Ext.getCmp('goodnews-categories-search-filter').reset();
        Ext.getCmp('goodnews-categories-group-filter').reset();
    	this.getBottomToolbar().changePage(1);
        this.refresh();
    }
});
Ext.reg('goodnews-grid-categories',GoodNews.grid.Categories);


/**
 * Window to create/edit a category.
 * 
 * @class GoodNews.window.Category
 * @extends MODx.Window
 * @param {Object} config An object of options.
 * @xtype goodnews-window-category
 */
GoodNews.window.Category = function(config) {
    config = config || {};
    config.id = config.id || Ext.id(),
    Ext.applyIf(config,{
        id: 'goodnews-window-category'
        ,title: (config.isUpdate) ?
            _('goodnews.category_update') :
            _('goodnews.category_create')
        ,url: GoodNews.config.connectorUrl
        ,baseParams: {
            action: (config.isUpdate) ?
                'mgr/category/update' :
                'mgr/category/create'
        }
        ,closeAction: 'close'
        ,fields: [{
            xtype: 'hidden'
            ,name: 'id'
        },{
            xtype: 'textfield'
            ,fieldLabel: _('goodnews.category_name')
            ,name: 'name'
            ,anchor: '100%'
        },{
            xtype: 'textarea'
            ,fieldLabel: _('goodnews.category_description')
            ,name: 'description'
            ,anchor: '100%'
        },{
            xtype: 'modx-combo'
            ,fieldLabel: _('goodnews.category_belongs_to_usergroup')
            ,name: 'goodnewsgroup_id'
            ,hiddenName: 'goodnewsgroup_id'
            ,emptyText: _('goodnews.subscribers_choose_user_group')
            ,anchor: '100%'
            ,lazyRender: true
            ,displayField: 'name'
            ,valueField: 'id'
            ,store: new Ext.data.JsonStore({
                url: GoodNews.config.connectorUrl
                ,baseParams: {
                    action : 'mgr/groups/getlist'
                    ,noModxGroups: true
                }
                ,fields: ['id','name']
                ,root: 'results'
            })
        },{
            xtype: 'xcheckbox'
            ,name: 'public'
            ,hideLabel: true
            ,boxLabel: _('goodnews.category_public')
            ,anchor: '100%'
        }]
    });
    GoodNews.window.Category.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.window.Category,MODx.Window);
Ext.reg('goodnews-window-category',GoodNews.window.Category);
