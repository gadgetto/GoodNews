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
            ,'mail_from'
            ,'mail_from_name'
            ,'mail_reply_to'
            ,'mail_bouncehandling'
            ,'mail_service'
            ,'mail_mailhost'
            ,'mail_mailbox_username'
            ,'mail_mailbox_password'
            ,'mail_boxname'
            ,'mail_port'
            ,'mail_service_option'
            ,'mail_softbounced_message_action'
            ,'mail_soft_mailbox'
            ,'mail_hardbounced_message_action'
            ,'mail_hard_mailbox'
            ,'mail_max_softbounces'
            ,'mail_max_softbounces_action'
            ,'mail_max_hardbounces'
            ,'mail_max_hardbounces_action'
            ,'mail_notclassified_message_action'
            ,'mail_notclassified_mailbox'
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
            header: _('goodnews.settings_container_mail_bouncehandling')
            ,dataIndex: 'mail_bouncehandling'
            ,align: 'center'
            ,sortable: false
            ,width: 100
            ,editable: true
            ,editor: { 
                xtype: 'combo-boolean'
                ,renderer: 'boolean'
            }
        },{
            header: _('goodnews.settings_container_context_key')
            ,dataIndex: 'context_key'
            ,sortable: true
            ,editable: false
            ,width: 80
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
        ,autoHeight: true
        ,width: 640
        ,closeAction: 'hide'
        ,fields: [{
            xtype: 'modx-tabs'
            ,hideMode: 'offsets'
            ,autoHeight: true
            ,deferredRender: false
            ,forceLayout: true
            ,anchor: '100%'
            ,bodyStyle: 'padding: 10px 10px 10px 10px;'
            ,border: true
            ,defaults: {
                border: false
                ,autoHeight: true
                ,bodyStyle: 'padding: 5px 8px 5px 5px;'
                ,layout: 'form'
                ,deferredRender: false
                ,forceLayout: true
            }
            ,items: [{
                title: _('goodnews.settings_container_tab_general')
                ,layout: 'form'
                ,items: [{
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
                },{
                    xtype: 'textfield'
                    ,fieldLabel: _('goodnews.settings_container_mail_from')
                    ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_mail_from_desc')
                    ,id: 'mail_from'
                    ,name: 'mail_from'
                    ,anchor: '100%'
                },{
                    xtype: MODx.expandHelp ? 'label' : 'hidden'
                    ,forId: 'mail_from'
                    ,html: _('goodnews.settings_container_mail_from_desc')
                    ,cls: 'gon-desc-under'
                },{
                    xtype: 'textfield'
                    ,fieldLabel: _('goodnews.settings_container_mail_from_name')
                    ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_mail_from_name_desc')
                    ,id: 'mail_from_name'
                    ,name: 'mail_from_name'
                    ,anchor: '100%'
                },{
                    xtype: MODx.expandHelp ? 'label' : 'hidden'
                    ,forId: 'mail_from_name'
                    ,html: _('goodnews.settings_container_mail_from_name_desc')
                    ,cls: 'gon-desc-under'
                },{
                    xtype: 'textfield'
                    ,fieldLabel: _('goodnews.settings_container_mail_reply_to')
                    ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_mail_reply_to_desc')
                    ,id: 'mail_reply_to'
                    ,name: 'mail_reply_to'
                    ,anchor: '100%'
                },{
                    xtype: MODx.expandHelp ? 'label' : 'hidden'
                    ,forId: 'mail_reply_to'
                    ,html: _('goodnews.settings_container_mail_reply_to_desc')
                    ,cls: 'gon-desc-under'
                },{
                    xtype: 'modx-combo'
                    ,fieldLabel: _('goodnews.settings_container_mail_bouncehandling')
                    ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_mail_bouncehandling_desc')
                    ,id: 'mail_bouncehandling'
                    ,name: 'mail_bouncehandling'
                    ,hiddenName: 'mail_bouncehandling'
                    ,store: [[1,_('enabled')],[0,_('disabled')]]
                    ,value: 1
                    ,triggerAction: 'all'
                    ,editable: false
                    ,selectOnFocus: false
                    ,preventRender: true
                    ,forceSelection: true
                    ,enableKeyEvents: true
                    ,anchor: '100%'
                },{
                    xtype: MODx.expandHelp ? 'label' : 'hidden'
                    ,forId: 'mail_bouncehandling'
                    ,html: _('goodnews.settings_container_mail_bouncehandling_desc')
                    ,cls: 'gon-desc-under'
                }]
            },{
                title: _('goodnews.settings_container_tab_bouncemailbox')
                ,layout: 'form'
                ,items: [{
                    xtype: 'modx-combo'
                    ,fieldLabel: _('goodnews.settings_container_mail_service')
                    ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_mail_service_desc')
                    ,id: 'mail_service'
                    ,name: 'mail_service'
                    ,hiddenName: 'mail_service'
                    ,store: [
                        ['pop3',_('goodnews.settings_container_mail_pop3')]
                        ,['imap',_('goodnews.settings_container_mail_imap')]
                    ]
                    ,triggerAction: 'all'
                    ,editable: false
                    ,selectOnFocus: false
                    ,preventRender: true
                    ,forceSelection: true
                    ,enableKeyEvents: true
                    ,anchor: '100%'
                    /*
                    ,listeners: {
                        'select': {
                            scope:this
                            ,fn:function(combo,record,index) {
                                var sb_message_action = Ext.getCmp('mail_softbounced_message_action');
                                // disable/enable fields related to imap service
                                if (index=='pop3') {
                                    tplsel.show();
                                } else {
                                    tplsel.hide();
                                }
                            }
                        }
                    }
                    */
                },{
                    xtype: MODx.expandHelp ? 'label' : 'hidden'
                    ,forId: 'mail_service'
                    ,html: _('goodnews.settings_container_mail_service_desc')
                    ,cls: 'gon-desc-under'
                },{
                    xtype: 'textfield'
                    ,fieldLabel: _('goodnews.settings_container_mail_mailhost')
                    ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_mail_mailhost_desc')
                    ,id: 'mail_mailhost'
                    ,name: 'mail_mailhost'
                    ,anchor: '100%'
                },{
                    xtype: MODx.expandHelp ? 'label' : 'hidden'
                    ,forId: 'mail_mailhost'
                    ,html: _('goodnews.settings_container_mail_mailhost_desc')
                    ,cls: 'gon-desc-under'
                },{
                    layout: 'column'
                    ,border: false
                    ,anchor: '100%'
                    ,defaults: {
                        labelAlign: 'top'
                        ,border: false
                        ,layout: 'form'
                        ,msgTarget: 'under'
                    }
                    ,items: [{
                        columnWidth: .5
                        ,style: 'margin: 0 7px 0 0;'
                        ,items: [{
                            xtype: 'textfield'
                            ,fieldLabel: _('goodnews.settings_container_mail_mailbox_username')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_mail_mailbox_username_desc')
                            ,id: 'mail_mailbox_username'
                            ,name: 'mail_mailbox_username'
                            ,anchor: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_mailbox_username'
                            ,html: _('goodnews.settings_container_mail_mailbox_username_desc')
                            ,cls: 'gon-desc-under'
                        }]
                    },{
                        columnWidth: .5
                        ,style: 'margin: 0 0 0 7px;'
                        ,items: [{
                            xtype: 'textfield'
                            ,inputType: 'password'
                            ,fieldLabel: _('goodnews.settings_container_mail_mailbox_password')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_mail_mailbox_password_desc')
                            ,id: 'mail_mailbox_password'
                            ,name: 'mail_mailbox_password'
                            ,anchor: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_boxname'
                            ,html: _('goodnews.settings_container_mail_mailbox_password_desc')
                            ,cls: 'gon-desc-under'
                        }]
                    }]
                },{
                    xtype: 'textfield'
                    ,fieldLabel: _('goodnews.settings_container_mail_boxname')
                    ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_mail_boxname_desc')
                    ,id: 'mail_boxname'
                    ,name: 'mail_boxname'
                    ,anchor: '100%'
                },{
                    xtype: MODx.expandHelp ? 'label' : 'hidden'
                    ,forId: 'mail_boxname'
                    ,html: _('goodnews.settings_container_mail_boxname_desc')
                    ,cls: 'gon-desc-under'
                },{
                    layout: 'column'
                    ,border: false
                    ,anchor: '100%'
                    ,defaults: {
                        labelAlign: 'top'
                        ,border: false
                        ,layout: 'form'
                        ,msgTarget: 'under'
                    }
                    ,items: [{
                        columnWidth: .5
                        ,style: 'margin: 0 7px 0 0;'
                        ,items: [{
                            xtype: 'modx-combo'
                            ,fieldLabel: _('goodnews.settings_container_mail_service_option')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_mail_service_option_desc')
                            ,id: 'mail_service_option'
                            ,name: 'mail_service_option'
                            ,hiddenName: 'mail_service_option'
                            ,store: [
                                ['none',_('goodnews.settings_container_mail_none')]
                                ,['tls',_('goodnews.settings_container_mail_tls')]
                                ,['notls',_('goodnews.settings_container_mail_notls')]
                                ,['ssl',_('goodnews.settings_container_mail_ssl')]
                            ]
                            ,triggerAction: 'all'
                            ,editable: false
                            ,selectOnFocus: false
                            ,preventRender: true
                            ,forceSelection: true
                            ,enableKeyEvents: true
                            ,anchor: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_service_option'
                            ,html: _('goodnews.settings_container_mail_service_option_desc')
                            ,cls: 'gon-desc-under'
                        }]
                    },{
                        columnWidth: .5
                        ,style: 'margin: 0 0 0 7px;'
                        ,items: [{
                            xtype: 'textfield'
                            ,fieldLabel: _('goodnews.settings_container_mail_port')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_mail_port_desc')
                            ,id: 'mail_port'
                            ,name: 'mail_port'
                            ,anchor: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_port'
                            ,html: _('goodnews.settings_container_mail_port_desc')
                            ,cls: 'gon-desc-under'
                        }]
                    }]
                }]
            },{
                title: _('goodnews.settings_container_tab_bouncerules')
                ,layout: 'form'
                ,items: [{
                    layout: 'column'
                    ,border: false
                    ,width: '100%'
                    ,defaults: {
                        labelAlign: 'top'
                        ,border: false
                        ,layout: 'form'
                        ,msgTarget: 'under'
                    }
                    ,items: [{
                        columnWidth: .5
                        ,style: 'margin: 0 7px 0 0;'
                        ,items: [{
                            xtype: 'modx-combo'
                            ,fieldLabel: _('goodnews.settings_container_softbounced_msg_action')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_softbounced_msg_action_desc')
                            ,id: 'mail_softbounced_message_action'
                            ,name: 'mail_softbounced_message_action'
                            ,hiddenName: 'mail_softbounced_message_action'
                            ,store: [
                                ['move',_('goodnews.settings_container_softbounced_msg_move')]
                                ,['delete',_('goodnews.settings_container_softbounced_msg_delete')]
                            ]
                            ,triggerAction: 'all'
                            ,editable: false
                            ,selectOnFocus: false
                            ,preventRender: true
                            ,forceSelection: true
                            ,enableKeyEvents: true
                            ,anchor: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_softbounced_message_action'
                            ,html: _('goodnews.settings_container_softbounced_msg_action_desc')
                            ,cls: 'gon-desc-under'
                        },{
                            xtype: 'textfield'
                            ,fieldLabel: _('goodnews.settings_container_softbounces_mailbox')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_softbounces_mailbox_desc')
                            ,id: 'mail_soft_mailbox'
                            ,name: 'mail_soft_mailbox'
                            ,anchor: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_soft_mailbox'
                            ,html: _('goodnews.settings_container_softbounces_mailbox_desc')
                            ,cls: 'gon-desc-under'
                        },{
                            xtype: 'numberfield'
                            ,fieldLabel: _('goodnews.settings_container_max_softbounces')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_max_softbounces_desc')
                            ,id: 'mail_max_softbounces'
                            ,name: 'mail_max_softbounces'
                            ,minValue: 1
                            ,maxValue: 10
                            ,anchor: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_max_softbounces'
                            ,html: _('goodnews.settings_container_max_softbounces_desc')
                            ,cls: 'gon-desc-under'
                        },{
                            xtype: 'modx-combo'
                            ,fieldLabel: _('goodnews.settings_container_max_softbounces_action')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_max_softbounces_action_desc')
                            ,id: 'mail_max_softbounces_action'
                            ,name: 'mail_max_softbounces_action'
                            ,hiddenName: 'mail_max_softbounces_action'
                            ,store: [
                                ['disable',_('goodnews.settings_container_soft_subscriber_disable')]
                                ,['delete',_('goodnews.settings_container_soft_subscriber_delete')]
                            ]
                            ,triggerAction: 'all'
                            ,editable: false
                            ,selectOnFocus: false
                            ,preventRender: true
                            ,forceSelection: true
                            ,enableKeyEvents: true
                            ,anchor: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_max_softbounces_action'
                            ,html: _('goodnews.settings_container_max_softbounces_action_desc')
                            ,cls: 'gon-desc-under'
                        }]
                    },{
                        columnWidth: .5
                        ,style: 'margin: 0 0 0 7px;'
                        ,items: [{
                            xtype: 'modx-combo'
                            ,fieldLabel: _('goodnews.settings_container_hardbounced_msg_action')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_hardbounced_msg_action_desc')
                            ,id: 'mail_hardbounced_message_action'
                            ,name: 'mail_hardbounced_message_action'
                            ,hiddenName: 'mail_hardbounced_message_action'
                            ,store: [
                                ['move',_('goodnews.settings_container_hardbounced_msg_move')]
                                ,['delete',_('goodnews.settings_container_hardbounced_msg_delete')]
                            ]
                            ,triggerAction: 'all'
                            ,editable: false
                            ,selectOnFocus: false
                            ,preventRender: true
                            ,forceSelection: true
                            ,enableKeyEvents: true
                            ,anchor: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_hardbounced_message_action'
                            ,html: _('goodnews.settings_container_hardbounced_msg_action_desc')
                            ,cls: 'gon-desc-under'
                        },{
                            xtype: 'textfield'
                            ,fieldLabel: _('goodnews.settings_container_hardbounces_mailbox')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_hardbounces_mailbox_desc')
                            ,id: 'mail_hard_mailbox'
                            ,name: 'mail_hard_mailbox'
                            ,anchor: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_hard_mailbox'
                            ,html: _('goodnews.settings_container_hardbounces_mailbox_desc')
                            ,cls: 'gon-desc-under'
                        },{
                            xtype: 'numberfield'
                            ,fieldLabel: _('goodnews.settings_container_max_hardbounces')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_max_hardbounces_desc')
                            ,id: 'mail_max_hardbounces'
                            ,name: 'mail_max_hardbounces'
                            ,minValue: 1
                            ,maxValue: 10
                            ,anchor: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_max_hardbounces'
                            ,html: _('goodnews.settings_container_max_hardbounces_desc')
                            ,cls: 'gon-desc-under'
                        },{
                            xtype: 'modx-combo'
                            ,fieldLabel: _('goodnews.settings_container_max_hardbounces_action')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_max_hardbounces_action_desc')
                            ,id: 'mail_max_hardbounces_action'
                            ,name: 'mail_max_hardbounces_action'
                            ,hiddenName: 'mail_max_hardbounces_action'
                            ,store: [
                                ['disable',_('goodnews.settings_container_hard_subscriber_disable')]
                                ,['delete',_('goodnews.settings_container_hard_subscriber_delete')]
                            ]
                            ,triggerAction: 'all'
                            ,editable: false
                            ,selectOnFocus: false
                            ,preventRender: true
                            ,forceSelection: true
                            ,enableKeyEvents: true
                            ,anchor: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_max_hardbounces_action'
                            ,html: _('goodnews.settings_container_max_hardbounces_action_desc')
                            ,cls: 'gon-desc-under'
                        }]
                    }]
                }]
            },{
                title: _('goodnews.settings_container_tab_notclassified_rules')
                ,layout: 'form'
                ,items: [{
                    xtype: 'modx-combo'
                    ,fieldLabel: _('goodnews.settings_container_notclassified_msg_action')
                    ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_notclassified_msg_action_desc')
                    ,id: 'mail_notclassified_message_action'
                    ,name: 'mail_notclassified_message_action'
                    ,hiddenName: 'mail_notclassified_message_action'
                    ,store: [
                        ['move',_('goodnews.settings_container_notclassified_msg_move')]
                        ,['delete',_('goodnews.settings_container_notclassified_msg_delete')]
                    ]
                    ,triggerAction: 'all'
                    ,editable: false
                    ,selectOnFocus: false
                    ,preventRender: true
                    ,forceSelection: true
                    ,enableKeyEvents: true
                    ,anchor: '100%'
                },{
                    xtype: MODx.expandHelp ? 'label' : 'hidden'
                    ,forId: 'mail_notclassified_message_action'
                    ,html: _('goodnews.settings_container_notclassified_msg_action_desc')
                    ,cls: 'gon-desc-under'
                },{
                    xtype: 'textfield'
                    ,fieldLabel: _('goodnews.settings_container_notclassified_mailbox')
                    ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_notclassified_mailbox_desc')
                    ,id: 'mail_notclassified_mailbox'
                    ,name: 'mail_notclassified_mailbox'
                    ,anchor: '100%'
                },{
                    xtype: MODx.expandHelp ? 'label' : 'hidden'
                    ,forId: 'mail_notclassified_mailbox'
                    ,html: _('goodnews.settings_container_notclassified_mailbox_desc')
                    ,cls: 'gon-desc-under'
                }]
            }]
            ,listeners: {
                'tabchange': function() {
                    this.syncSize();
                }
                ,scope: this
            }
        }]
    });
    GoodNews.window.UpdateContainerSettings.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.window.UpdateContainerSettings,MODx.Window);
Ext.reg('goodnews-window-container-settings-update',GoodNews.window.UpdateContainerSettings);
