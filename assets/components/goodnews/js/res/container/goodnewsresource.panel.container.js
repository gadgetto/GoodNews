/**
 * GoodNewsResource container panel which holds the main tabs
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
            id: 'modx-resource-settings'
            ,title: _(this.classLexiconKey)
            ,cls: 'modx-resource-tab'
            ,labelAlign: 'top'
            ,bodyCssClass: 'tab-panel-wrapper main-wrapper'
            ,autoHeight: true
            ,items: this.getMainFieldsCombined(config)
        });
        if (config.show_tvs && MODx.config.tvs_below_content != 1) {
            it.push(this.getTemplateVariablesPanel(config));
        }
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
                border: false,
                msgTarget: 'under'
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
        if (MODx.perm.resourcegroup_resource_list) {
            it.push(this.getAccessPermissionsTab(config));
        }
        var its = [];
        its.push(this.getPageHeader(config),{
            id:'modx-resource-tabs'
            ,xtype: 'modx-tabs'
            ,forceLayout: true
            ,deferredRender: false
            ,collapsible: false
            ,animCollapse: false
            ,itemId: 'tabs'
            ,items: it
        });
        if (MODx.config.tvs_below_content == 1) {
            var tvs = this.getTemplateVariablesPanel(config);
            its.push(tvs);
        }
        return its;
    }

    // GoodNewsResourceContainer hidden fields
    ,getGoodNewsHiddenFields: function() {
        return [{
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
        }];
    }

    // Combine MODX main fields with GoodNewsResourceContainer hidden fields
    ,getMainFieldsCombined: function(config) {
        var fc = [];
        fc.push(this.getMainFields(config));
        fc.push(this.getGoodNewsHiddenFields());
        return fc;
    }
    
    ,getSettingLeftFields: function(config) {
        return [{
            xtype: 'textfield'
            ,fieldLabel: _('resource_type')
            ,description: '<b>[[*class_key]]</b><br>'
            ,name: 'class_key'
            ,id: 'modx-resource-class-key'
            ,maxLength: 255
            ,readOnly: true
            ,value: this.defaultClassKey
        },{
            xtype: 'modx-combo-content-type'
            ,fieldLabel: _('resource_content_type')
            ,description: '<b>[[*content_type]]</b><br>'+_('resource_content_type_help')
            ,name: 'content_type'
            ,hiddenName: 'content_type'
            ,id: 'modx-resource-content-type'
            ,allowBlank: false
            ,value: config.record.content_type || (MODx.config.default_content_type || 1)
        }];
    }
    
    ,getSettingRightFieldsetLeft: function(config) {
        return [{
            // is always folder!
            xtype: 'hidden'
            ,name: 'isfolder'
            ,id: 'modx-resource-isfolder'
            ,value: 1
        },{
            xtype: 'xcheckbox'
            ,ctCls: 'display-switch'
            ,boxLabel: _('resource_show_in_tree')
            ,description: '<b>[[*show_in_tree]]</b><br>'+_('resource_show_in_tree_help')
            ,hideLabel: false // needs to be false for first visible element (top margin!)
            ,name: 'show_in_tree'
            ,id: 'modx-resource-show-in-tree'
            ,inputValue: 1
            ,checked: parseInt(config.record.show_in_tree)
        },{
            // child resources are always hidden in tree!
            xtype: 'hidden'
            ,name: 'hide_children_in_tree'
            ,id: 'modx-resource-hide-children-in-tree'
            ,value: 1
        },{
            xtype: 'xcheckbox'
            ,ctCls: 'display-switch'
            ,boxLabel: _('resource_alias_visible')
            ,description: '<b>[[*alias_visible]]</b><br>'+_('resource_alias_visible_help')
            ,hideLabel: true
            ,name: 'alias_visible'
            ,id: 'modx-resource-alias-visible'
            ,inputValue: 1
            ,checked: parseInt(config.record.alias_visible) || 1
        },{
            xtype: 'xcheckbox'
            ,ctCls: 'display-switch'
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
            ,description: '<b>[[*uri]]</b><br>'+_('resource_uri_help')
            ,name: 'uri'
            ,id: 'modx-resource-uri'
            ,maxLength: 255
            ,value: config.record.uri || ''
            ,hidden: !config.record.uri_override
        }];
    }

    ,getMailingsSettingFields: function(config) {
        config = config || {record:{}};
        return [{
            layout:'column'
            ,defaults: {
                defaults: {
                    layout: 'form',
                    labelAlign: 'top',
                    labelSeparator: '',
                    defaults: {
                        validationEvent: 'change',
                        anchor: '100%',
                        msgTarget: 'under'
                    }
                }
            }
            ,items:[{
                columnWidth: .5
                ,items: [{
                    id: 'goodnewsresource-page-mailings-settings-left'
                    ,items: this.getMailingsSettingLeftFields(config)
                }]
            },{
                columnWidth: .5
                ,items: [{
                    id: 'goodnewsresource-page-mailings-settings-right'
                    ,items: this.getMailingsSettingRightFields(config)
                }]
    
            }]
        }];
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
        },{
            xtype: 'modx-combo-template'
            ,fieldLabel: _('goodnews.container_mailing_template')
            ,description: '<b>[[+mailingTemplate]]</b><br />'+_('goodnews.container_mailing_template_desc')
            ,name: 'setting_mailingTemplate'
            ,hiddenName: 'setting_mailingTemplate'
            ,id: 'goodnewsresource-mailing-template'
            ,value: config.record.setting_mailingTemplate || 0
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
        },{
            xtype: 'textfield'
            ,fieldLabel: _('goodnews.container_profile_resource')
            ,description: '<b>[[+profileResource]]</b><br />'+_('goodnews.container_profile_resource_desc')
            ,name: 'setting_profileResource'
            ,value: config.record.setting_profileResource || ''
        }];
    }
});
Ext.reg('goodnewsresource-panel-container',GoodNewsResource.panel.Container);
