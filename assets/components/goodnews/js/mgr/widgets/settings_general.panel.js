GoodNews.panel.GeneralSettings = function(config) {
    config = config || {};

    Ext.applyIf(config,{
        id: 'goodnews-panel-settings-general'
        ,title: _('goodnews.settings_general_tab')   
        ,defaults: { 
            border: false 
        }
        ,items:[{
            html: '<p>'+_('goodnews.settings_general_tab_desc')+'</p>'
            ,border: false
            ,bodyCssClass: 'panel-desc'
        },{
            layout: 'form'
            ,cls: 'main-wrapper'
            ,labelAlign: 'top'
            ,anchor: '100%'
            ,defaults: {
                msgTarget: 'under'
            }
            ,items: [{
                xtype: 'textfield'
                ,name: 'test_subject_prefix'
                ,id: 'test_subject_prefix'
                ,fieldLabel: _('goodnews.settings_test_subject_prefix')
                ,description: MODx.expandHelp ? '' : _('goodnews.settings_test_subject_prefix_desc')
                ,anchor: '100%'
            },{
                xtype: MODx.expandHelp ? 'label' : 'hidden'
                ,forId: 'test_subject_prefix'
                ,html: _('goodnews.settings_test_subject_prefix_desc')
                ,cls: 'gon-desc-under'
            },{
                xtype: 'textfield'
                ,name: 'admin_groups'
                ,id: 'admin_groups'
                ,fieldLabel: _('goodnews.settings_admin_groups')
                ,description: MODx.expandHelp ? '' : _('goodnews.settings_admin_groups_desc')
                ,anchor: '100%'
            },{
                xtype: MODx.expandHelp ? 'label' : 'hidden'
                ,forId: 'admin_groups'
                ,html: _('goodnews.settings_admin_groups_desc')
                ,cls: 'gon-desc-under'
            }]
        }]    
    });
    GoodNews.panel.GeneralSettings.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.panel.GeneralSettings,Ext.Panel);
Ext.reg('goodnews-panel-settings-general', GoodNews.panel.GeneralSettings);
