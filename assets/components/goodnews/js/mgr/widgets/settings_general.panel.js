GoodNews.panel.GeneralSettings = function(config) {
    config = config || {};

    Ext.applyIf(config,{
        id: 'goodnews-panel-settings-general'
        ,title: _('goodnews.settings_general_tab')   
        ,layout: 'anchor'
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
                ,cls: 'desc-under'
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
                ,cls: 'desc-under'
            },{
                xtype: 'xcheckbox'
                ,name: 'auto_fix_imagesizes'
                ,id: 'auto_fix_imagesizes'
                ,disabled: GoodNews.config.pThumbAddOn ? false : true
                ,hideLabel: true
                ,boxLabel: _('goodnews.settings_auto_fix_imagesizes')
                ,description: MODx.expandHelp ? '' : _('goodnews.settings_auto_fix_imagesizes_desc')
            },{
                xtype: MODx.expandHelp ? 'label' : 'hidden'
                ,forId: 'auto_fix_imagesizes'
                ,html: _('goodnews.settings_auto_fix_imagesizes_desc')
                ,cls: 'desc-under'
            },{
                xtype: 'xcheckbox'
                ,name: 'auto_cleanup_subscriptions'
                ,id: 'auto_cleanup_subscriptions'
                ,hideLabel: true
                ,boxLabel: _('goodnews.settings_auto_cleanup_subscriptions')
                ,description: MODx.expandHelp ? '' : _('goodnews.settings_auto_cleanup_subscriptions_desc')
            },{
                xtype: MODx.expandHelp ? 'label' : 'hidden'
                ,forId: 'auto_cleanup_subscriptions'
                ,html: _('goodnews.settings_auto_cleanup_subscriptions_desc')
                ,cls: 'desc-under'
            },{
                xtype: 'numberfield'
                ,name: 'auto_cleanup_subscriptions_ttl'
                ,id: 'auto_cleanup_subscriptions_ttl'
                ,fieldLabel: _('goodnews.settings_auto_cleanup_subscriptions_ttl')
                ,description: MODx.expandHelp ? '' : _('goodnews.settings_auto_cleanup_subscriptions_ttl_desc')
                ,anchor: '25%'
            },{
                xtype: MODx.expandHelp ? 'label' : 'hidden'
                ,forId: 'auto_cleanup_subscriptions_ttl'
                ,html: _('goodnews.settings_auto_cleanup_subscriptions_ttl_desc')
                ,cls: 'desc-under'
            }]
        }]    
    });
    GoodNews.panel.GeneralSettings.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.panel.GeneralSettings,Ext.Panel);
Ext.reg('goodnews-panel-settings-general', GoodNews.panel.GeneralSettings);
