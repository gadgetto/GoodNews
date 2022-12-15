/**
 * Panel to manage groups.
 * 
 * @class GoodNews.panel.Groups
 * @extends Ext.Panel
 * @param {Object} config An object of options.
 * @xtype goodnews-panel-groups
 */
GoodNews.panel.Groups = function(config) {
    config = config || {};

    Ext.applyIf(config,{
        id: 'goodnews-panel-groups'
        ,title: _('goodnews.groups')
        ,layout: 'anchor'
        ,defaults: {
            border: false
        }
        ,items:[{
            html: '<p>'+_('goodnews.groups_management_desc')+'</p>'
            ,border: false
            ,bodyCssClass: 'panel-desc'
        },{
            xtype: 'goodnews-grid-groups'
            ,cls: 'main-wrapper'
            ,preventRender: true
        }]    
        ,listeners: {
            'activate': {fn: function() {
                var groupsGrid = Ext.getCmp('goodnews-grid-groups');
                groupsGrid.loadMask.disable();
                groupsGrid.refresh();
                groupsGrid.loadMask.enable();
            }
            ,scope: this}
        }
    });
    GoodNews.panel.Groups.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.panel.Groups,Ext.Panel);
Ext.reg('goodnews-panel-groups', GoodNews.panel.Groups);


/**
 * Grid which lists groups.
 * 
 * @class GoodNews.grid.Groups
 * @extends MODx.grid.Grid
 * @param {Object} config An object of options.
 * @xtype goodnews-grid-groups
 */
GoodNews.grid.Groups = function(config) {
    config = config || {};
    
    Ext.applyIf(config,{
        id: 'goodnews-grid-groups'
        ,url: GoodNews.config.connectorUrl
        ,baseParams: { action: 'Bitego\\GoodNews\\Processors\\Groups\\GetList' }
        ,fields: [
            'id'
            ,'name'
            ,'description'
            ,'public'
            ,'modxusergroup'
            ,'modxusergroup_name'
            ,'membercount'
            ,'menu'
        ]
        ,emptyText: _('goodnews.groups_none')
        ,paging: true
        ,remoteSort: true
        ,save_action: 'Bitego\\GoodNews\\Processors\\Groups\\UpdateFromGrid'
        ,autosave: true
        ,autoExpandColumn: 'description'
        /*
        ,viewConfig: {
            forceFit: true
            getRowClass: function(record, index) {
                if (record.get('modxusergroup_name') !== null) {
                    return 'gon-modx-group-assigned';
                } else {
                    return '';
                }
            }
        }
        */
        ,columns: [{
            header: _('goodnews.id')
            ,dataIndex: 'id'
            ,sortable: true
            ,width: 40
        },{
            header: _('goodnews.group_name')
            ,dataIndex: 'name'
            ,sortable: true
            ,width: 150
            ,editor: { xtype: 'textfield' }
            ,renderer: function(value,meta,record){
                if (record.get('modxusergroup_name') !== null) {
                    return '<span class="gon-modx-group-assigned">'+value+'</span>';
                } else {
                    return '<span>'+value+'</span>';
                }
            }
        },{
            header: _('goodnews.group_description')
            ,dataIndex: 'description'
            ,sortable: false
            ,width: 250
            ,editor: { xtype: 'textfield' }
        },{
            header: _('goodnews.group_public')
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
            header: _('goodnews.modx_usergroup')
            ,dataIndex: 'modxusergroup_name'
            ,sortable: true
            ,width: 100
        },{
            header: _('goodnews.group_membercount')
            ,dataIndex: 'membercount'
            ,align: 'center'
            ,sortable: false
            ,width: 100
        }]
        ,tbar:[{
            text: _('goodnews.group_create')
            ,handler: this.addGroup
            ,scope: this
            ,cls: 'primary-button'
        },'->',{
            xtype: 'textfield'
            ,cls: 'x-form-filter'
            ,id: 'goodnews-groups-search-filter'
            ,emptyText: _('goodnews.input_search_filter')
            ,listeners: {
                'change': {fn: this.search,scope:this}
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
            ,cls: 'x-form-filter-clear'
            ,id: 'goodnews-groups-filter-clear'
            ,text: _('goodnews.button_filter_clear')
            ,listeners: {
                'click': {fn: this.clearFilter, scope: this}
            }
        }]
    });
    GoodNews.grid.Groups.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.grid.Groups,MODx.grid.Grid,{
    getMenu: function() {
        var r = this.getSelectionModel().getSelected();
        var p = r.data.perm;

        return [{
            text: _('goodnews.group_update')
            ,handler: this.updateGroup
        },{
            text: _('goodnews.group_remove')
            ,handler: this.removeGroup
        }];
    }
    ,addGroup: function(btn,e) {
        var win = MODx.load({
            xtype: 'goodnews-window-group'
            ,listeners: {
                success: {fn: function(r) {
                    this.refresh();
                },scope: this}
                ,scope: this
            }
        });
        win.show(e.target);
    }
    ,updateGroup: function(btn,e) {
        var win = MODx.load({
            xtype: 'goodnews-window-group'
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
    ,removeGroup: function() {
        MODx.msg.confirm({
            title: _('goodnews.group_remove')
            ,text: _('goodnews.group_remove_confirm')
            ,url: this.config.url
            ,params: {
                action: 'Bitego\\GoodNews\\Processors\\Groups\\Remove'
                ,id: this.menu.record.id
            }
            ,listeners: {
                'success': {fn: this.refresh, scope: this}
            }
        });
    }
    ,search: function(tf,nv,ov) {
        var s = this.getStore();
        s.baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }
    ,clearFilter: function() {
    	this.getStore().baseParams = {
            action: 'Bitego\\GoodNews\\Processors\\Groups\\GetList'
    	};
        Ext.getCmp('goodnews-groups-search-filter').reset();
    	this.getBottomToolbar().changePage(1);
        this.refresh();
    }
});
Ext.reg('goodnews-grid-groups',GoodNews.grid.Groups);


/**
 * Window to create/edit a group.
 * 
 * @class GoodNews.window.Group
 * @extends MODx.Window
 * @param {Object} config An object of options.
 * @xtype goodnews-window-group
 */
GoodNews.window.Group = function(config) {
    config = config || {};
    config.id = config.id || Ext.id(),
    Ext.applyIf(config,{
        id: 'goodnews-window-group'
        ,title: (config.isUpdate) ?
            _('goodnews.group_update') :
            _('goodnews.group_create')
        ,url: GoodNews.config.connectorUrl
        ,baseParams: {
            action: (config.isUpdate) ?
                'Bitego\\GoodNews\\Processors\\Groups\\Update' :
                'Bitego\\GoodNews\\Processors\\Groups\\Create'
        }
        ,closeAction: 'close'
        ,fields: [{
            xtype: 'hidden'
            ,name: 'id'
        },{
            xtype: 'textfield'
            ,fieldLabel: _('goodnews.group_name')
            ,name: 'name'
            ,anchor: '100%'
        },{
            xtype: 'textarea'
            ,fieldLabel: _('goodnews.group_description')
            ,name: 'description'
            ,anchor: '100%'
        },{
            xtype: 'modx-combo-usergroup'
            ,baseParams: {
                action: 'Security/Group/GetList'
                ,addNone: true
            }
            ,fieldLabel: _('goodnews.group_belongs_to_modx_usergroup')
            ,name: 'modxusergroup'
            ,hiddenName: 'modxusergroup'
            ,emptyText: _('goodnews.choose_modx_user_group')
            ,anchor: '100%'
            ,lazyRender: true
            ,itemId: 'usergroup'
        },{
            xtype: 'xcheckbox'
            ,name: 'public'
            ,hideLabel: true
            ,boxLabel: _('goodnews.group_public')
            ,anchor: '100%'
        }]
    });
    GoodNews.window.Group.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.window.Group,MODx.Window);
Ext.reg('goodnews-window-group',GoodNews.window.Group);
