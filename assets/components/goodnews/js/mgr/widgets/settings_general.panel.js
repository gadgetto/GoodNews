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
            ,xtype: 'modx-description'
        },{
            layout: 'form'
            ,cls: 'main-wrapper'
            ,labelAlign: 'top'
            ,anchor: '100%'
            ,defaults: {
                msgTarget: 'under'
            }
            ,items: [{
                layout: 'column'
                ,border: false
                ,defaults: {
                    layout: 'form'
                    ,border: false
                }
                ,items: [{
                    columnWidth: .5
                    ,items: [{
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
                        ,name: 'auto_full_urls'
                        ,id: 'auto_full_urls'
                        ,hideLabel: true
                        ,boxLabel: _('goodnews.settings_auto_full_urls')
                        ,description: MODx.expandHelp ? '' : _('goodnews.settings_auto_full_urls_desc')
                    },{
                        xtype: MODx.expandHelp ? 'label' : 'hidden'
                        ,forId: 'auto_full_urls'
                        ,html: _('goodnews.settings_auto_full_urls_desc')
                        ,cls: 'desc-under'
                    },{
                        xtype: 'xcheckbox'
                        ,name: 'auto_inline_css'
                        ,id: 'auto_inline_css'
                        ,hideLabel: true
                        ,boxLabel: _('goodnews.settings_auto_inline_css')
                        ,description: MODx.expandHelp ? '' : _('goodnews.settings_auto_inline_css_desc')
                    },{
                        xtype: MODx.expandHelp ? 'label' : 'hidden'
                        ,forId: 'auto_inline_css'
                        ,html: _('goodnews.settings_auto_inline_css_desc')
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
                },{
                    columnWidth: .5
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
                        xtype: 'xcheckbox'
                        ,name: 'statusemail_enabled'
                        ,id: 'statusemail_enabled'
                        ,hideLabel: true
                        ,boxLabel: _('goodnews.settings_statusemail_enabled')
                        ,description: MODx.expandHelp ? '' : _('goodnews.settings_statusemail_enabled_desc')
                    },{
                        xtype: MODx.expandHelp ? 'label' : 'hidden'
                        ,forId: 'statusemail_enabled'
                        ,html: _('goodnews.settings_statusemail_enabled_desc')
                        ,cls: 'desc-under'
                    },{
                        xtype: 'textfield'
                        ,name: 'statusemail_fromname'
                        ,id: 'statusemail_fromname'
                        ,fieldLabel: _('goodnews.settings_statusemail_fromname')
                        ,description: MODx.expandHelp ? '' : _('goodnews.settings_statusemail_fromname_desc')
                        ,anchor: '100%'
                    },{
                        xtype: MODx.expandHelp ? 'label' : 'hidden'
                        ,forId: 'statusemail_fromname'
                        ,html: _('goodnews.settings_statusemail_fromname_desc')
                        ,cls: 'desc-under'
                    },{
                        xtype: 'textfield'
                        ,name: 'statusemail_chunk'
                        ,id: 'statusemail_chunk'
                        ,fieldLabel: _('goodnews.settings_statusemail_chunk')
                        ,description: MODx.expandHelp ? '' : _('goodnews.settings_statusemail_chunk_desc')
                        ,anchor: '100%'
                    },{
                        xtype: MODx.expandHelp ? 'label' : 'hidden'
                        ,forId: 'statusemail_chunk'
                        ,html: _('goodnews.settings_statusemail_chunk_desc')
                        ,cls: 'desc-under'
                    }]
                }]
            }]

        }]    
    });
    GoodNews.panel.GeneralSettings.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.panel.GeneralSettings,Ext.Panel);
Ext.reg('goodnews-panel-settings-general', GoodNews.panel.GeneralSettings);
