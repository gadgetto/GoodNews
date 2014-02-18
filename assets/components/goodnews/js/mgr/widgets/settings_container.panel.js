GoodNews.panel.ContainerSettings = function(config) {
    config = config || {};

    Ext.applyIf(config,{
        id: 'goodnews-panel-settings-container'
        ,title: _('goodnews.settings_container_tab')   
        ,defaults: { 
            border: false 
        }
        ,items:[{
            html: '<p>'+_('goodnews.settings_container_tab_desc')+'</p>'
            ,border: false
            ,bodyCssClass: 'panel-desc'
        },{
            xtype: 'goodnews-grid-containers'
            ,cls: 'main-wrapper'
            ,preventRender: true
        }]    
    });
    GoodNews.panel.ContainerSettings.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.panel.ContainerSettings,Ext.Panel);
Ext.reg('goodnews-panel-settings-container', GoodNews.panel.ContainerSettings);


GoodNews.grid.Containers = function(config) {
    config = config || {};
    
    Ext.applyIf(config,{
        id: 'goodnews-grid-containers'
        ,url: GoodNews.config.connectorUrl
        ,baseParams: { action: 'mgr/settings/containers/getList' }
        ,autoExpandColumn: 'pagetitle'
        ,fields: [
            'id'
            ,'pagetitle'
            ,'editor_groups'
            ,'context_key'
            ,'menu'
        ]
        ,emptyText: _('goodnews.settings_containers_none')
        ,paging: true
        ,remoteSort: true
        ,save_action: 'mgr/settings/containers/updateFromGrid'
        ,autosave: true
        ,columns: [{
            header: _('goodnews.settings_container_id')
            ,dataIndex: 'id'
            ,sortable: true
            ,editable: false
            ,align: 'right'
            ,width: 30
        },{
            header: _('goodnews.settings_container_pagetitle')
            ,id: 'pagetitle'
            ,dataIndex: 'pagetitle'
            ,sortable: true
            ,editable: false
        },{
            header: _('goodnews.settings_container_editor_groups')
            ,dataIndex: 'editor_groups'
            ,sortable: false
            ,editable: true
            ,width: 150
            ,editor: { xtype: 'textfield' }
        },{
            header: _('goodnews.settings_container_context_key')
            ,dataIndex: 'context_key'
            ,sortable: true
            ,editable: false
            ,width: 100
        }]
    });
    GoodNews.grid.Containers.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.grid.Containers,MODx.grid.Grid,{
    getMenu: function() {
        var r = this.getSelectionModel().getSelected();
        var p = r.data.perm;

        return [{
            text: _('goodnews.settings_container_update')
            ,handler: this.updateContainerSettings
        }];
    }
    ,updateContainerSettings: function(btn,e) {
        if (!this.updateContainerSettingsWindow) {
            this.updateContainerSettingsWindow = MODx.load({
                xtype: 'goodnews-window-container-settings-update'
                ,record: this.menu.record
                ,listeners: {
                    'success': {fn:this.refresh,scope:this}
                }
            });
        }
        this.updateContainerSettingsWindow.setValues(this.menu.record);
        this.updateContainerSettingsWindow.show(e.target);
    }
});
Ext.reg('goodnews-grid-containers',GoodNews.grid.Containers);


GoodNews.window.UpdateContainerSettings = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        title: _('goodnews.settings_container_update')
        ,url: GoodNews.config.connectorUrl
        ,baseParams: {
            action: 'mgr/settings/containers/update'
        }
        ,fields: [{
            xtype: 'hidden'
            ,name: 'id'
        },{
            xtype: 'textfield'
            ,fieldLabel: _('goodnews.settings_container_editor_groups')
            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_editor_groups_desc')
            ,id: 'editor_groups'
            ,name: 'editor_groups'
            ,anchor: '100%'
        },{
            xtype: MODx.expandHelp ? 'label' : 'hidden'
            ,forId: 'editor_groups'
            ,html: _('goodnews.settings_container_editor_groups_desc')
            ,cls: 'gon-desc-under'
        }]
    });
    GoodNews.window.UpdateContainerSettings.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.window.UpdateContainerSettings,MODx.Window);
Ext.reg('goodnews-window-container-settings-update',GoodNews.window.UpdateContainerSettings);
