GoodNews.panel.ContainerSettings = function(config) {
    config = config || {};

    Ext.applyIf(config,{
        id: 'goodnews-panel-settings-container'
        ,title: _('goodnews.settings_container_tab')
        ,layout: 'anchor'
        ,defaults: { 
            border: false 
        }
        ,items:[{
            html: '<p>'+_('goodnews.settings_container_tab_desc')+'</p>'
            ,xtype: 'modx-description'
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
        ,baseParams: { action: 'Bitego\\GoodNews\\Processors\\Settings\\Container\\GetList' }
        ,autoExpandColumn: 'pagetitle'
        ,fields: [
            'id'
            ,'pagetitle'
            ,'editor_groups'
            ,'mail_from'
            ,'mail_from_name'
            ,'mail_reply_to'
            ,'mail_charset'
            ,'mail_encoding'
            ,'mail_bouncehandling'
            ,'mail_use_smtp'
            ,'mail_smtp_auth'
            ,'mail_smtp_user'
            ,'mail_smtp_pass'
            ,'mail_smtp_hosts'
            ,'mail_smtp_prefix'
            ,'mail_smtp_keepalive'
            ,'mail_smtp_timeout'
            ,'mail_smtp_single_to'
            ,'mail_smtp_helo'
            ,'mail_service'
            ,'mail_mailhost'
            ,'mail_mailbox_username'
            ,'mail_mailbox_password'
            ,'mail_boxname'
            ,'mail_port'
            ,'mail_service_option'
            ,'mail_softbounced_message_action'
            ,'mail_soft_mailbox'
            ,'mail_max_softbounces'
            ,'mail_max_softbounces_action'
            ,'mail_hardbounced_message_action'
            ,'mail_hard_mailbox'
            ,'mail_max_hardbounces'
            ,'mail_max_hardbounces_action'
            ,'mail_notclassified_message_action'
            ,'mail_notclassified_mailbox'
            ,'collection1_name'
            ,'collection1_parents'
            ,'collection2_name'
            ,'collection2_parents'
            ,'collection3_name'
            ,'collection3_parents'
            ,'context_key'
            ,'menu'
            ,'actions'
        ]
        ,emptyText: _('goodnews.settings_containers_none')
        ,paging: true
        ,remoteSort: true
        ,save_action: 'Bitego\\GoodNews\\Processors\\Settings\\Container\\UpdateFromGrid'
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
    this.on('click',this.handleActionButtons,this);
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
	,handleActionButtons: function(e) {
		var t = e.getTarget();
		var elm = t.className.split(' ')[0];
		if(elm == 'controlLink') {
			var action = t.className.split(' ')[1];
			var record = this.getSelectionModel().getSelected();
            this.menu.record = record.data;
			switch (action) {
                case 'settings':
					this.updateContainerSettings(t,e);
                    break;
				default:
					break;
            }
		}
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
        this.updateContainerSettingsWindow.enableDisableSMTPAuthFields(this.menu.record.mail_smtp_auth);
        this.updateContainerSettingsWindow.enableDisableBounceFields(this.menu.record.mail_service);
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
            action: 'Bitego\\GoodNews\\Processors\\Settings\\Container\\Update'
        }
        ,layout: 'anchor'
        ,bwrapCssClass: 'x-window-with-tabs'
        ,autoHeight: true
        ,width: 760
        ,closeAction: 'hide'
        ,fields: [{
            xtype: 'modx-tabs'
            ,bodyStyle: { background: 'transparent' }
            ,border: true
            ,deferredRender: false
            ,autoHeight: true
            ,autoScroll: false
            ,anchor: '100% 100%'
            ,defaults: {
                layout: 'form'
                ,autoHeight: true
            }
            ,items: [{
                title: _('goodnews.settings_container_tab_general')
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
                    ,cls: 'desc-under'
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
                        ,items: [{
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
                            ,cls: 'desc-under'
                        }]
                    },{
                        columnWidth: .5
                        ,items: [{
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
                            ,cls: 'desc-under'
                        }]
                    }]
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
                    ,cls: 'desc-under'
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
                        ,items: [{
                            xtype: 'modx-combo-charset'
                            ,fieldLabel: _('goodnews.settings_container_mail_charset')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_mail_charset_desc')
                            ,id: 'mail_charset'
                            ,name: 'mail_charset'
                            ,hiddenName: 'mail_charset'
                            ,anchor: '100%'
                            ,width: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_charset'
                            ,html: _('goodnews.settings_container_mail_charset_desc')
                            ,cls: 'desc-under'
                        }]
                    },{
                        columnWidth: .5
                        ,items: [{
                            xtype: 'textfield'
                            ,fieldLabel: _('goodnews.settings_container_mail_encoding')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_mail_encoding_desc')
                            ,id: 'mail_encoding'
                            ,name: 'mail_encoding'
                            ,anchor: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_encoding'
                            ,html: _('goodnews.settings_container_mail_encoding_desc')
                            ,cls: 'desc-under'
                        }]
                    }]
                },{
                    xtype: 'modx-combo'
                    ,fieldLabel: _('goodnews.settings_container_mail_bouncehandling')
                    ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_mail_bouncehandling_desc')
                    ,id: 'mail_bouncehandling'
                    ,name: 'mail_bouncehandling'
                    ,hiddenName: 'mail_bouncehandling'
                    ,store: [[0,_('no')],[1,_('yes')]]
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
                    ,cls: 'desc-under'
                }]
            },{
                title: _('goodnews.settings_container_tab_smtp')
                ,items: [{
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
                        ,items: [{
                            xtype: 'modx-combo'
                            ,fieldLabel: _('goodnews.settings_container_smtp_use')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_smtp_use_desc')
                            ,id: 'mail_use_smtp'
                            ,name: 'mail_use_smtp'
                            ,hiddenName: 'mail_use_smtp'
                            ,store: [
                                [0,_('no')]
                                ,[1,_('yes')]
                                ,[2,_('goodnews.settings_container_smtp_mandrill_service')]
                            ]
                            ,triggerAction: 'all'
                            ,editable: false
                            ,selectOnFocus: false
                            ,preventRender: true
                            ,forceSelection: true
                            ,enableKeyEvents: true
                            ,anchor: '100%'
                            ,listeners: {
                                'select': {
                                    scope:this
                                    ,fn:function(combo,record,index) {
                                        this.presetMandrillSMTPSettings(combo.value);
                                    }
                                }
                            }
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_use_smtp'
                            ,html: _('goodnews.settings_container_smtp_use_desc')
                            ,cls: 'desc-under'
                        }]
                    },{
                        columnWidth: .5
                        ,items: [{
                            xtype: 'modx-combo'
                            ,fieldLabel: _('goodnews.settings_container_smtp_auth')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_smtp_auth_desc')
                            ,id: 'mail_smtp_auth'
                            ,name: 'mail_smtp_auth'
                            ,hiddenName: 'mail_smtp_auth'
                            ,store: [[0,_('no')],[1,_('yes')]]
                            ,triggerAction: 'all'
                            ,editable: false
                            ,selectOnFocus: false
                            ,preventRender: true
                            ,forceSelection: true
                            ,enableKeyEvents: true
                            ,anchor: '100%'
                            ,listeners: {
                                'select': {
                                    scope:this
                                    ,fn:function(combo,record,index) {
                                        this.enableDisableSMTPAuthFields(combo.value);
                                    }
                                }
                            }
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_smtp_auth'
                            ,html: _('goodnews.settings_container_smtp_auth_desc')
                            ,cls: 'desc-under'
                        }]
                    }]
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
                        ,items: [{
                            xtype: 'textfield'
                            ,fieldLabel: _('goodnews.settings_container_smtp_user')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_smtp_user_desc')
                            ,id: 'mail_smtp_user'
                            ,name: 'mail_smtp_user'
                            ,anchor: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_smtp_user'
                            ,html: _('goodnews.settings_container_smtp_user_desc')
                            ,cls: 'desc-under'
                        }]
                    },{
                        columnWidth: .5
                        ,items: [{
                            xtype: 'textfield'
                            ,inputType: 'password'
                            ,fieldLabel: _('goodnews.settings_container_smtp_pass')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_smtp_pass_desc')
                            ,id: 'mail_smtp_pass'
                            ,name: 'mail_smtp_pass'
                            ,anchor: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_smtp_pass'
                            ,html: _('goodnews.settings_container_smtp_pass_desc')
                            ,cls: 'desc-under'
                        }]
                    }]
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
                        ,items: [{
                            xtype: 'textfield'
                            ,fieldLabel: _('goodnews.settings_container_smtp_hosts')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_smtp_hosts_desc')
                            ,id: 'mail_smtp_hosts'
                            ,name: 'mail_smtp_hosts'
                            ,anchor: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_smtp_hosts'
                            ,html: _('goodnews.settings_container_smtp_hosts_desc')
                            ,cls: 'desc-under'
                        }]
                    },{
                        columnWidth: .5
                        ,items: [{
                            xtype: 'textfield'
                            ,fieldLabel: _('goodnews.settings_container_smtp_prefix')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_smtp_prefix_desc')
                            ,id: 'mail_smtp_prefix'
                            ,name: 'mail_smtp_prefix'
                            ,anchor: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_smtp_prefix'
                            ,html: _('goodnews.settings_container_smtp_prefix_desc')
                            ,cls: 'desc-under'
                        }]
                    }]
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
                        ,items: [{
                            xtype: 'modx-combo'
                            ,fieldLabel: _('goodnews.settings_container_smtp_keepalive')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_smtp_keepalive_desc')
                            ,id: 'mail_smtp_keepalive'
                            ,name: 'mail_smtp_keepalive'
                            ,hiddenName: 'mail_smtp_keepalive'
                            ,store: [[0,_('no')],[1,_('yes')]]
                            ,triggerAction: 'all'
                            ,editable: false
                            ,selectOnFocus: false
                            ,preventRender: true
                            ,forceSelection: true
                            ,enableKeyEvents: true
                            ,anchor: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_smtp_keepalive'
                            ,html: _('goodnews.settings_container_smtp_keepalive_desc')
                            ,cls: 'desc-under'
                        }]
                    },{
                        columnWidth: .5
                        ,items: [{
                            xtype: 'textfield'
                            ,fieldLabel: _('goodnews.settings_container_smtp_timeout')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_smtp_timeout_desc')
                            ,id: 'mail_smtp_timeout'
                            ,name: 'mail_smtp_timeout'
                            ,anchor: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_smtp_timeout'
                            ,html: _('goodnews.settings_container_smtp_timeout_desc')
                            ,cls: 'desc-under'
                        }]
                    }]
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
                        ,items: [{
                            xtype: 'modx-combo'
                            ,fieldLabel: _('goodnews.settings_container_smtp_single_to')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_smtp_single_to_desc')
                            ,id: 'mail_smtp_single_to'
                            ,name: 'mail_smtp_single_to'
                            ,hiddenName: 'mail_smtp_single_to'
                            ,store: [[0,_('no')],[1,_('yes')]]
                            ,triggerAction: 'all'
                            ,editable: false
                            ,selectOnFocus: false
                            ,preventRender: true
                            ,forceSelection: true
                            ,enableKeyEvents: true
                            ,anchor: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_smtp_single_to'
                            ,html: _('goodnews.settings_container_smtp_single_to_desc')
                            ,cls: 'desc-under'
                        }]
                    },{
                        columnWidth: .5
                        ,items: [{
                            xtype: 'textfield'
                            ,fieldLabel: _('goodnews.settings_container_smtp_helo')
                            ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_smtp_helo_desc')
                            ,id: 'mail_smtp_helo'
                            ,name: 'mail_smtp_helo'
                            ,anchor: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'mail_smtp_helo'
                            ,html: _('goodnews.settings_container_smtp_helo_desc')
                            ,cls: 'desc-under'
                        }]
                    }]
                }]
            },{
                title: _('goodnews.settings_container_tab_bouncemailbox')
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
                    ,listeners: {
                        'select': {
                            scope:this
                            ,fn:function(combo,record,index) {
                                this.enableDisableBounceFields(combo.value);
                            }
                        }
                    }
                },{
                    xtype: MODx.expandHelp ? 'label' : 'hidden'
                    ,forId: 'mail_service'
                    ,html: _('goodnews.settings_container_mail_service_desc')
                    ,cls: 'desc-under'
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
                    ,cls: 'desc-under'
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
                            ,cls: 'desc-under'
                        }]
                    },{
                        columnWidth: .5
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
                            ,forId: 'mail_mailbox_password'
                            ,html: _('goodnews.settings_container_mail_mailbox_password_desc')
                            ,cls: 'desc-under'
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
                    ,cls: 'desc-under'
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
                            ,cls: 'desc-under'
                        }]
                    },{
                        columnWidth: .5
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
                            ,cls: 'desc-under'
                        }]
                    }]
                }]
            },{
                title: _('goodnews.settings_container_tab_bouncerules')
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
                            ,cls: 'desc-under'
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
                            ,cls: 'desc-under'
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
                            ,cls: 'desc-under'
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
                            ,cls: 'desc-under'
                        }]
                    },{
                        columnWidth: .5
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
                            ,cls: 'desc-under'
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
                            ,cls: 'desc-under'
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
                            ,cls: 'desc-under'
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
                            ,cls: 'desc-under'
                        }]
                    }]
                }]
            },{
                title: _('goodnews.settings_container_tab_unclassified_bounces')
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
                    ,cls: 'desc-under'
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
                    ,cls: 'desc-under'
                }]
            },{
                title: _('goodnews.settings_container_tab_resource_collection')
                ,layout: 'accordion'
                ,items: [{
                    xtype:'fieldset'
                    ,title:  _('goodnews.settings_container_collection1_fieldset')
                    ,autoHeight: true
                    ,items: [{
                        xtype: 'textfield'
                        ,fieldLabel: _('goodnews.settings_container_collection_label')
                        ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_collection_label_desc')
                        ,id: 'collection1_name'
                        ,name: 'collection1_name'
                        ,anchor: '100%'
                    },{
                        xtype: MODx.expandHelp ? 'label' : 'hidden'
                        ,forId: 'collection1_name'
                        ,html: _('goodnews.settings_container_collection_label_desc')
                        ,cls: 'desc-under'
                    },{
                        xtype: 'textfield'
                        ,fieldLabel: _('goodnews.settings_container_collection_parents')
                        ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_collection_parents_desc')
                        ,id: 'collection1_parents'
                        ,name: 'collection1_parents'
                        ,maskRe: /[\d,]+/
                        ,anchor: '100%'
                    },{
                        xtype: MODx.expandHelp ? 'label' : 'hidden'
                        ,forId: 'collection1_parents'
                        ,html: _('goodnews.settings_container_collection_parents_desc')
                        ,cls: 'desc-under'
                    }]
                },{
                    xtype:'fieldset'
                    ,title:  _('goodnews.settings_container_collection2_fieldset')
                    ,autoHeight: true
                    ,items: [{
                        xtype: 'textfield'
                        ,fieldLabel: _('goodnews.settings_container_collection_label')
                        ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_collection_label_desc')
                        ,id: 'collection2_name'
                        ,name: 'collection2_name'
                        ,anchor: '100%'
                    },{
                        xtype: MODx.expandHelp ? 'label' : 'hidden'
                        ,forId: 'collection2_name'
                        ,html: _('goodnews.settings_container_collection_label_desc')
                        ,cls: 'desc-under'
                    },{
                        xtype: 'textfield'
                        ,fieldLabel: _('goodnews.settings_container_collection_parents')
                        ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_collection_parents_desc')
                        ,id: 'collection2_parents'
                        ,name: 'collection2_parents'
                        ,maskRe: /[\d,]+/
                        ,anchor: '100%'
                    },{
                        xtype: MODx.expandHelp ? 'label' : 'hidden'
                        ,forId: 'collection2_parents'
                        ,html: _('goodnews.settings_container_collection_parents_desc')
                        ,cls: 'desc-under'
                    }]
                },{
                    xtype:'fieldset'
                    ,title:  _('goodnews.settings_container_collection3_fieldset')
                    ,autoHeight: true
                    ,items: [{
                        xtype: 'textfield'
                        ,fieldLabel: _('goodnews.settings_container_collection_label')
                        ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_collection_label_desc')
                        ,id: 'collection3_name'
                        ,name: 'collection3_name'
                        ,anchor: '100%'
                    },{
                        xtype: MODx.expandHelp ? 'label' : 'hidden'
                        ,forId: 'collection3_name'
                        ,html: _('goodnews.settings_container_collection_label_desc')
                        ,cls: 'desc-under'
                    },{
                        xtype: 'textfield'
                        ,fieldLabel: _('goodnews.settings_container_collection_parents')
                        ,description: MODx.expandHelp ? '' : _('goodnews.settings_container_collection_parents_desc')
                        ,id: 'collection3_parents'
                        ,name: 'collection3_parents'
                        ,maskRe: /[\d,]+/
                        ,anchor: '100%'
                    },{
                        xtype: MODx.expandHelp ? 'label' : 'hidden'
                        ,forId: 'collection3_parents'
                        ,html: _('goodnews.settings_container_collection_parents_desc')
                        ,cls: 'desc-under'
                    }]
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
Ext.extend(GoodNews.window.UpdateContainerSettings,MODx.Window,{
    enableDisableSMTPAuthFields: function(enabled) {
        var msu = Ext.getCmp('mail_smtp_user');
        var msp = Ext.getCmp('mail_smtp_pass');
        // disable/enable fields related to smtp auth
        if (enabled==false) {
            msu.disable();
            msp.disable();
        } else {
            msu.enable();
            msp.enable();
        }
    }
    ,presetMandrillSMTPSettings: function(service) {
        var msa = Ext.getCmp('mail_smtp_auth');
        var msh = Ext.getCmp('mail_smtp_hosts');
        var msp = Ext.getCmp('mail_smtp_prefix');
        // preset Mandrill settings
        if (service==2) {
            msa.setValue(1);
            msh.setValue('smtp.mandrillapp.com:587');
            msp.setValue('');
        }
    }
    ,enableDisableBounceFields: function(service) {
        var sbma = Ext.getCmp('mail_softbounced_message_action');
        var hbma = Ext.getCmp('mail_hardbounced_message_action');
        var smb  = Ext.getCmp('mail_soft_mailbox');
        var hmb  = Ext.getCmp('mail_hard_mailbox');
        var ncma = Ext.getCmp('mail_notclassified_message_action');
        var ncmb = Ext.getCmp('mail_notclassified_mailbox');
        // disable/enable fields related to imap service
        if (service=='pop3') {
            sbma.disable();
            hbma.disable();
            smb.disable();
            hmb.disable();
            ncma.disable();
            ncmb.disable();
        } else {
            sbma.enable();
            hbma.enable();
            smb.enable();
            hmb.enable();
            ncma.enable();
            ncmb.enable();
        }
    }
});
Ext.reg('goodnews-window-container-settings-update',GoodNews.window.UpdateContainerSettings);
