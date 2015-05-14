/**
 * Container panel which holds the main tabs
 * 
 * @class GoodNewsResource.panel.Container
 * @extends MODx.panel.Resource (/manager/assets/modext/widgets/resource/modx.panel.resource.js)
 * @param {Object} config An object of config properties
 * @xtype goodnewsresource-panel-container
 */
GoodNewsResource.panel.Container = function(config) {
    config = config || {};
    GoodNewsResource.panel.Container.superclass.constructor.call(this,config);
};
Ext.extend(GoodNewsResource.panel.Container,MODx.panel.Resource,{
    getFields: function(config) {
        var it = [];
        it.push({
            title: _('goodnews.container_tab_container')
            ,id: 'modx-resource-settings'
            ,cls: 'modx-resource-tab'
            ,layout: 'form'
            ,labelAlign: 'top'
            ,labelSeparator: ''
            ,bodyCssClass: 'tab-panel-wrapper main-wrapper'
            ,autoHeight: true
            ,defaults: {
                border: false
                ,msgTarget: 'side'
                ,width: 400
            }
            ,items: this.getMainFields(config)
        });
        it.push({
            id: 'modx-page-settings'
            ,title: _('goodnews.container_tab_settings_container')
            ,cls: 'modx-resource-tab'
            ,layout: 'form'
            ,forceLayout: true
            ,deferredRender: false
            ,labelWidth: 200
            ,bodyCssClass: 'main-wrapper'
            ,autoHeight: true
            ,defaults: {
                border: false
                ,msgTarget: 'under'
            }
            ,items: this.getSettingFields(config)
        });
        it.push({
            id: 'goodnewsresource-page-mailings-settings'
            ,title: _('goodnews.container_tab_settings_mailings')
            ,cls: 'modx-resource-tab'
            ,layout: 'form'
            ,forceLayout: true
            ,deferredRender: false
            ,labelWidth: 200
            ,bodyCssClass: 'main-wrapper'
            ,autoHeight: true
            ,defaults: {
                border: false
                ,msgTarget: 'under'
            }
            ,items: this.getMailingsSettingFields(config)
        });
        if (config.show_tvs && MODx.config.tvs_below_content != 1) {
            it.push(this.getTemplateVariablesPanel(config));
        }
        if (MODx.perm.resourcegroup_resource_list == 1) {
            it.push(this.getAccessPermissionsTab(config));
        }
        var its = [];
        its.push(this.getPageHeader(config),{
            id:'modx-resource-tabs'
            ,xtype: 'modx-tabs'
            ,forceLayout: true
            ,deferredRender: false
            ,collapsible: true
            ,itemId: 'tabs'
            ,items: it
        });
        var ct = this.getContentField(config);
        if (ct) {
            its.push({
                title: _('resource_content')
                ,id: 'modx-resource-content'
                ,layout: 'form'
                ,bodyCssClass: 'main-wrapper'
                ,autoHeight: true
                ,collapsible: true
                ,animCollapse: false
                ,hideMode: 'offsets'
                ,items: ct
                ,style: 'margin-top: 10px'
            });
        }
        if (MODx.config.tvs_below_content == 1) {
            var tvs = this.getTemplateVariablesPanel(config);
            its.push(tvs);
        }
        return its;
    }
    ,getPageHeader: function(config) {
        config = config || {record:{}};
        return {
            html: '<h2>'+_('goodnews.container_new')+'</h2>'
            ,id: 'modx-resource-header'
            ,cls: 'modx-page-header'
            ,border: false
            ,forceLayout: true
            ,anchor: '100%'
        };
    }
    ,getSettingLeftFields: function(config) {
        return [{
            xtype: 'hidden'
            ,name: 'class_key'
            ,value: 'GoodNewsResourceContainer'
        },{
            xtype: 'hidden'
            ,name: 'setting_editorGroups'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailFrom'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailFromName'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailReplyTo'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailCharset'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailEncoding'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailBounceHandling'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailUseSmtp'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailSmtpAuth'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailSmtpUser'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailSmtpPass'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailSmtpHosts'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailSmtpPrefix'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailSmtpKeepalive'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailSmtpTimeout'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailSmtpSingleTo'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailSmtpHelo'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailService'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailMailHost'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailMailboxUsername'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailMailboxPassword'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailBoxname'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailPort'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailServiceOption'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailSoftBouncedMessageAction'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailSoftMailbox'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailMaxSoftBounces'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailMaxSoftBouncesAction'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailHardBouncedMessageAction'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailHardMailbox'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailMaxHardBounces'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailMaxHardBouncesAction'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailNotClassifiedMessageAction'
        },{
            xtype: 'hidden'
            ,name: 'setting_mailNotClassifiedMailbox'
        },{
            xtype: 'hidden'
            ,name: 'setting_collection1Name'
        },{
            xtype: 'hidden'
            ,name: 'setting_collection1Parents'
        },{
            xtype: 'hidden'
            ,name: 'setting_collection2Name'
        },{
            xtype: 'hidden'
            ,name: 'setting_collection2Parents'
        },{
            xtype: 'hidden'
            ,name: 'setting_collection3Name'
        },{
            xtype: 'hidden'
            ,name: 'setting_collection3Parents'
        },{
            xtype: 'modx-field-parent-change'
            ,fieldLabel: _('resource_parent')
            ,description: '<b>[[*parent]]</b><br />'+_('resource_parent_help')
            ,name: 'parent-cmb'
            ,id: 'modx-resource-parent'
            ,value: config.record.parent || 0
            ,anchor: '100%'
        },{
            xtype: 'xdatetime'
            ,fieldLabel: _('resource_publishedon')
            ,description: '<b>[[*publishedon]]</b><br />'+_('resource_publishedon_help')
            ,name: 'publishedon'
            ,id: 'modx-resource-publishedon'
            ,allowBlank: true
            ,dateFormat: MODx.config.manager_date_format
            ,timeFormat: MODx.config.manager_time_format
            ,startDay: parseInt(MODx.config.manager_week_start)
            ,dateWidth: 120
            ,timeWidth: 120
            ,offset_time: MODx.config.server_offset_time
            ,value: config.record.publishedon
        },{
            xtype: MODx.config.publish_document ? 'xdatetime' : 'hidden'
            ,fieldLabel: _('resource_publishdate')
            ,description: '<b>[[*pub_date]]</b><br />'+_('resource_publishdate_help')
            ,name: 'pub_date'
            ,id: 'modx-resource-pub-date'
            ,allowBlank: true
            ,dateFormat: MODx.config.manager_date_format
            ,timeFormat: MODx.config.manager_time_format
            ,startDay: parseInt(MODx.config.manager_week_start)
            ,dateWidth: 120
            ,timeWidth: 120
            ,offset_time: MODx.config.server_offset_time
            ,value: config.record.pub_date
        },{
            xtype: MODx.config.publish_document ? 'xdatetime' : 'hidden'
            ,fieldLabel: _('resource_unpublishdate')
            ,description: '<b>[[*unpub_date]]</b><br />'+_('resource_unpublishdate_help')
            ,name: 'unpub_date'
            ,id: 'modx-resource-unpub-date'
            ,allowBlank: true
            ,dateFormat: MODx.config.manager_date_format
            ,timeFormat: MODx.config.manager_time_format
            ,startDay: parseInt(MODx.config.manager_week_start)
            ,dateWidth: 120
            ,timeWidth: 120
            ,offset_time: MODx.config.server_offset_time
            ,value: config.record.unpub_date
        }];
    }
    ,getSettingRightFields: function(config) {
        return [{
            xtype: 'fieldset'
            ,fieldLabel: _('goodnews.container_properties')
            ,items: this.getSettingRightFieldset(config)
        },{
            xtype: 'numberfield'
            ,fieldLabel: _('resource_menuindex')
            ,description: '<b>[[*menuindex]]</b><br />'+_('resource_menuindex_help')
            ,name: 'menuindex'
            ,id: 'modx-resource-menuindex'
            ,width: 60
            ,value: parseInt(config.record.menuindex) || 0
        }];
    }
    ,getSettingRightFieldset: function(config) {
        return [{
            layout: 'column'
            ,id: 'modx-page-settings-box-columns'
            ,border: false
            ,anchor: '100%'
            ,defaults: {
                labelSeparator: ''
                ,labelAlign: 'top'
                ,border: false
                ,layout: 'form'
                ,msgTarget: 'under'
            }
            ,items: [{
                columnWidth: .5
                ,id: 'modx-page-settings-right-box-left'
                ,defaults: { msgTarget: 'under' }
                ,items: this.getSettingRightFieldsetLeft(config)
            },{
                columnWidth: .5
                ,id: 'modx-page-settings-right-box-right'
                ,defaults: { msgTarget: 'under' }
                ,items: this.getSettingRightFieldsetRight(config)
            }]
        },{
            xtype: 'xcheckbox'
            ,boxLabel: _('resource_uri_override')
            ,description: _('resource_uri_override_help')
            ,hideLabel: true
            ,name: 'uri_override'
            ,value: 1
            ,checked: parseInt(config.record.uri_override) ? true : false
            ,id: 'modx-resource-uri-override'

        },{
            xtype: 'textfield'
            ,fieldLabel: _('resource_uri')
            ,description: '<b>[[*uri]]</b><br />'+_('resource_uri_help')
            ,name: 'uri'
            ,id: 'modx-resource-uri'
            ,maxLength: 255
            ,anchor: '70%'
            ,value: config.record.uri || ''
            ,hidden: !config.record.uri_override
        }];
    }
    ,getSettingRightFieldsetLeft: function(config) {
        return [{
            xtype: 'xcheckbox'
            ,boxLabel: _('resource_searchable')
            ,description: '<b>[[*searchable]]</b><br />'+_('resource_searchable_help')
            ,hideLabel: true
            ,name: 'searchable'
            ,id: 'modx-resource-searchable'
            ,inputValue: 1
            ,checked: parseInt(config.record.searchable)
        },{
            xtype: 'xcheckbox'
            ,boxLabel: _('resource_richtext')
            ,description: '<b>[[*richtext]]</b><br />'+_('resource_richtext_help')
            ,hideLabel: true
            ,name: 'richtext'
            ,id: 'modx-resource-richtext'
            ,inputValue: 1
            ,checked: parseInt(config.record.richtext)
        }];
    }
    ,getSettingRightFieldsetRight: function(config) {
        return [{
            xtype: 'xcheckbox'
            ,boxLabel: _('deleted')
            ,description: '<b>[[*deleted]]</b>'
            ,hideLabel: true
            ,name: 'deleted'
            ,id: 'modx-resource-deleted'
            ,inputValue: 1
            ,checked: parseInt(config.record.deleted) || false
        }];
    }
    ,getMailingsSettingFields: function(config) {
        config = config || {record:{}};
        var s = [{
            layout:'column'
            ,border: false
            ,anchor: '100%'
            ,defaults: {
                labelSeparator: ''
                ,labelAlign: 'top'
                ,border: false
                ,layout: 'form'
                ,msgTarget: 'under'
            }
            ,items:[{
                columnWidth: .5
                ,id: 'goodnewsresource-page-mailings-settings-left'
                ,defaults: { msgTarget: 'under' }
                ,items: this.getMailingsSettingLeftFields(config)
            },{
                columnWidth: .5
                ,id: 'goodnewsresource-page-mailings-settings-right'
                ,defaults: { msgTarget: 'under' }
                ,items: this.getMailingsSettingRightFields(config)
            }]
        }];
        return s;
    }
    ,getMailingsSettingLeftFields: function(config) {
        return [{
            xtype: 'modx-combo-category'
            ,fieldLabel: _('goodnews.container_templates_category')
            ,description: '<b>[[+templatesCategory]]</b><br />'+_('goodnews.container_templates_category_desc')
            ,name: 'setting_templatesCategory'
            ,hiddenName: 'setting_templatesCategory'
            ,id: 'goodnewsresource-templates-category'
            ,value: config.record.setting_templatesCategory || 0
            ,pageSize: 20
            ,anchor: '100%'
        },{
            xtype: 'modx-combo-template'
            ,fieldLabel: _('goodnews.container_mailing_template')
            ,description: '<b>[[+mailingTemplate]]</b><br />'+_('goodnews.container_mailing_template_desc')
            ,name: 'setting_mailingTemplate'
            ,hiddenName: 'setting_mailingTemplate'
            ,id: 'goodnewsresource-mailing-template'
            ,value: config.record.setting_mailingTemplate || 0
            ,anchor: '100%'
            ,editable: false
        }];
    }
    ,getMailingsSettingRightFields: function(config) {
        return [{
            xtype: 'textfield'
            ,fieldLabel: _('goodnews.container_unsubscribe_resource')
            ,description: '<b>[[+unsubscribeResource]]</b><br />'+_('goodnews.container_unsubscribe_resource_desc')
            ,name: 'setting_unsubscribeResource'
            ,value: config.record.setting_unsubscribeResource || ''
            ,anchor: '60%'
        },{
            xtype: 'textfield'
            ,fieldLabel: _('goodnews.container_profile_resource')
            ,description: '<b>[[+profileResource]]</b><br />'+_('goodnews.container_profile_resource_desc')
            ,name: 'setting_profileResource'
            ,value: config.record.setting_profileResource || ''
            ,anchor: '60%'
        }];
    }
});
Ext.reg('goodnewsresource-panel-container',GoodNewsResource.panel.Container);
