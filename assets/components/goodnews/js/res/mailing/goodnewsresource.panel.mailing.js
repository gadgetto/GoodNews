/**
 * Mailing panel which holds the main tabs
 * 
 * @class GoodNewsResource.panel.Mailing
 * @extends MODx.panel.Resource (/manager/assets/modext/widgets/resource/modx.panel.resource.js)
 * @param {Object} config An object of config properties
 * @xtype goodnewsresource-panel-mailing
 */
GoodNewsResource.panel.Mailing = function(config) {
    config = config || {};
    config.trackResetOnLoad = true;
    GoodNewsResource.panel.Mailing.superclass.constructor.call(this,config);
};
Ext.extend(GoodNewsResource.panel.Mailing,MODx.panel.Resource,{
    
    beforeSubmit: function(o) {
        var ta = Ext.get(this.contentField);
        if (ta) {
            var v = ta.dom.value;
            var hc = Ext.getCmp('hiddenContent');
            if (hc) { hc.setValue(v); }
        }
        var g = Ext.getCmp('modx-grid-resource-security');
        if (g) {
            Ext.apply(o.form.baseParams,{
                resource_groups: g.encode()
            });
        }
        if (ta) {
            this.cleanupEditor();
        }
        if(this.getForm().baseParams.action == 'Resource/Create') {
            var btn = Ext.getCmp('modx-abtn-save');
            if (btn) { btn.disable(); }
        }
        
        // get selected groups and categories from tree
        var nodeIDs = '';
        var selNodes;
        var tree = Ext.getCmp('goodnewsresource-tree-groupscategories');
        selNodes = tree.getChecked();
        Ext.each(selNodes, function(node){
            if (nodeIDs!='') {
                nodeIDs += ',';
            }
            nodeIDs += node.id;
        });
        // write selected nodes to hidden field for saving
        this.getForm().setValues({
          groupscategories: nodeIDs
        });

        // get selected rows from collect resources grids for saving
        var rc1grid = Ext.getCmp('goodnewsresource-collection1-grid');
        var rc1IDs = '';
        if (rc1grid) {
            rc1IDs = rc1grid.getSelectedAsList();
        }
        // write resource collection to hidden field for saving
        this.getForm().setValues({
          collection1: rc1IDs
        });
        
        var rc2grid = Ext.getCmp('goodnewsresource-collection2-grid');
        var rc2IDs = '';
        if (rc2grid) {
            rc2IDs = rc2grid.getSelectedAsList();
        }
        // write resource collection to hidden field for saving
        this.getForm().setValues({
          collection2: rc2IDs
        });

        var rc3grid = Ext.getCmp('goodnewsresource-collection3-grid');
        var rc3IDs = '';
        if (rc3grid) {
            rc3IDs = rc3grid.getSelectedAsList();
        }
        // write resource collection to hidden field for saving
        this.getForm().setValues({
          collection3: rc3IDs
        });

        return this.fireEvent('save',{
            values: this.getForm().getValues()
            ,stay: Ext.state.Manager.get('modx.stay.'+MODx.request.a,'stay')
        });
    }

    ,getFields: function(config) {
        var it = [];
        it.push({
            id: 'modx-resource-settings'
            ,title: _('goodnews.mailing')
            ,cls: 'modx-resource-tab'
            ,labelAlign: 'top'
            ,bodyCssClass: 'tab-panel-wrapper main-wrapper'
            ,autoHeight: true
            ,items: this.getMainFieldsCombined(config)
        });
        it.push(
            this.getResourceCollectionTabs(config)
        );
        if (config.show_tvs && MODx.config.tvs_below_content != 1) {
            it.push(
                this.getTemplateVariablesPanel(config)
            );
        }
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

    // GoodNewsResourceMailing hidden fields
    ,getGoodNewsHiddenFields: function(config) {
        return [{
            xtype: 'hidden'
            ,id: 'goodnewsresource-groupscategories'
            ,name: 'groupscategories'
        },{
            xtype: 'hidden'
            ,id: 'goodnewsresource-collection1'
            ,name: 'collection1'
        },{
            xtype: 'hidden'
            ,id: 'goodnewsresource-collection2'
            ,name: 'collection2'
        },{
            xtype: 'hidden'
            ,id: 'goodnewsresource-collection3'
            ,name: 'collection3'
        },{
            xtype: 'hidden'
            ,id: 'modx-resource-longtitle'
            ,name: 'longtitle'
            ,value: config.record.longtitle || ''
        },{
            xtype: 'hidden'
            ,id: 'modx-resource-description'
            ,name: 'description'
            ,value: config.record.description || ''
        },{
            xtype: 'hidden'
            ,id: 'modx-resource-unpub-date'
            ,name: 'unpub_date'
            ,value: config.record.unpub_date
        },{
            xtype: 'hidden'
            ,id: 'modx-resource-hidemenu'
            ,name: 'hidemenu'
            ,value: config.record.hidemenu
        },{
            xtype: 'hidden'
            ,id: 'modx-resource-menutitle'
            ,name: 'menutitle'
            ,value: config.record.menutitle || ''
        },{
            xtype: 'hidden'
            ,id: 'modx-resource-link-attributes'
            ,name: 'link_attributes'
            ,value: config.record.link_attributes || ''
        },{
            xtype: 'hidden'
            ,id: 'modx-resource-menuindex'
            ,name: 'menuindex'
            ,value: parseInt(config.record.menuindex) || 0
        },{
            xtype: 'hidden'
            ,id: 'modx-resource-class-key'
            ,name: 'class_key'
            ,value: 'Bitego\\GoodNews\\Model\\GoodNewsResourceMailing'
        },{
            xtype: 'hidden'
            ,id: 'modx-resource-content-type'
            ,name: 'content_type'
            ,value: 1
        },{
            xtype: 'hidden'
            ,id: 'modx-resource-parent'
            ,name: 'parent'
            ,value: config.record.parent
        },{
            xtype: 'hidden'
            ,id: 'modx-resource-content-dispo'
            ,name: 'content_dispo'
            ,value: 0
        },{
            xtype: 'hidden'
            ,id: 'modx-resource-isfolder'
            ,name: 'isfolder'
            ,value: 0
        },{
            xtype: 'hidden'
            ,id: 'modx-resource-show-in-tree'
            ,name: 'show_in_tree'
            ,value: 0
        },{
            xtype: 'hidden'
            ,id: 'modx-resource-hide-children-in-tree'
            ,name: 'hide_children_in_tree'
            ,value: 1
        },{
            xtype: 'hidden'
            ,id: 'modx-resource-alias-visible'
            ,name: 'alias_visible'
            ,value: parseInt(config.record.alias_visible) || 1
        },{
            xtype: 'hidden'
            ,id: 'modx-resource-uri-override'
            ,name: 'uri_override'
            ,value: parseInt(config.record.uri_override) || 0
        },{
            xtype: 'hidden'
            ,id: 'modx-resource-uri'
            ,name: 'uri'
            ,value: config.record.uri || ''
        },{
            xtype: 'hidden'
            ,id: 'modx-resource-searchable'
            ,name: 'searchable'
            ,value: parseInt(config.record.searchable)
        },{
            xtype: 'hidden'
            ,id: 'modx-resource-cacheable'
            ,name: 'cacheable'
            ,value: parseInt(config.record.cacheable)
        }];
    }

    // Combine MODX main fields with GoodNewsResourceMailing hidden fields
    // and change column widths
    ,getMainFieldsCombined: function(config) {
        var fc = [];
        var mainFields = this.getMainFields(config);
        // Change columnWidths in mainfield columns
        mainFields[0].items[0].columnWidth = 0.7;
        mainFields[0].items[1].columnWidth = 0.3;
        fc.push(mainFields);
        fc.push(this.getGoodNewsHiddenFields(config));
        return fc;
    }

    ,getResourceCollectionTabs: function(config) {
        var cTabs = [];
        if (config.record.collection1Name && config.record.collection1Parents) {
            cTabs.push({
                id: 'goodnewsresource-collection1-tab'
                ,title: _('goodnews.mailing_resource_collection')+config.record.collection1Name
                ,cls: 'modx-resource-tab'
                ,labelAlign: 'top'
                ,bodyCssClass: 'tab-panel-wrapper main-wrapper'
                ,autoHeight: true
                ,items: [{
                    html: '<p>'+_('goodnews.mailing_resource_collection_desc')+'</p>'
                    ,xtype: 'modx-description'
                },{
                    xtype: 'goodnewsresource-grid-collect-resources'
                    ,cls: 'main-wrapper'
                    ,preventRender: true
                    ,baseParams: {
                        action: 'Bitego\\GoodNews\\Processors\\Collection\\GetList'
                        ,parentIds: config.record.collection1Parents
                        ,collectionIds: config.record.collection1 || ''
                        ,collectionInternalName: 'collection1'
                    }
                }]
            });
        }
        if (config.record.collection2Name && config.record.collection2Parents) {
            cTabs.push({
                id: 'goodnewsresource-collection2-tab'
                ,title: _('goodnews.mailing_resource_collection')+config.record.collection2Name
                ,cls: 'modx-resource-tab'
                ,labelAlign: 'top'
                ,bodyCssClass: 'tab-panel-wrapper main-wrapper'
                ,autoHeight: true
                ,items: [{
                    html: '<p>'+_('goodnews.mailing_resource_collection_desc')+'</p>'
                    ,xtype: 'modx-description'
                },{
                    xtype: 'goodnewsresource-grid-collect-resources'
                    ,cls: 'main-wrapper'
                    ,preventRender: true
                    ,baseParams: {
                        action: 'Bitego\\GoodNews\\Processors\\Collection\\GetList'
                        ,parentIds: config.record.collection2Parents
                        ,collectionIds: config.record.collection2 || ''
                        ,collectionInternalName: 'collection2'
                    }
                }]
            });
        }
        if (config.record.collection3Name && config.record.collection3Parents) {
            cTabs.push({
                id: 'goodnewsresource-collection3-tab'
                ,title: _('goodnews.mailing_resource_collection')+config.record.collection3Name
                ,cls: 'modx-resource-tab'
                ,labelAlign: 'top'
                ,bodyCssClass: 'tab-panel-wrapper main-wrapper'
                ,autoHeight: true
                ,items: [{
                    html: '<p>'+_('goodnews.mailing_resource_collection_desc')+'</p>'
                    ,xtype: 'modx-description'
                },{
                    xtype: 'goodnewsresource-grid-collect-resources'
                    ,cls: 'main-wrapper'
                    ,preventRender: true
                    ,baseParams: {
                        action: 'Bitego\\GoodNews\\Processors\\Collection\\GetList'
                        ,parentIds: config.record.collection3Parents
                        ,collectionIds: config.record.collection3 || ''
                        ,collectionInternalName: 'collection2'
                    }
                }]
            });
        }
        return cTabs;
    }

    ,getMainLeftFields: function(config) {
        config = config || {record:{}};
        const aliasLength = ~~MODx.config['friendly_alias_max_length'] || 0;
        return [{
            layout: 'column'
            ,defaults: {
                layout: 'form',
                labelSeparator: '',
                defaults: {
                    layout: 'form',
                    anchor: '100%',
                    validationEvent: 'change',
                    msgTarget: 'under'
                }
            }
            ,items: [{
                columnWidth: .7
                ,items: [{
                    xtype: 'textfield'
                    ,fieldLabel: _('goodnews.mail_subject')
                    ,required: true
                    ,description: '<b>[[*pagetitle]]</b><br>'+_('goodnews.mail_subject_desc')
                    ,name: 'pagetitle'
                    ,id: 'modx-resource-pagetitle'
                    ,maxLength: 191
                    ,allowBlank: false
                    ,enableKeyEvents: true
                    ,listeners: {
                        keyup: {
                            fn: function(cmp) {
                                const title = this.formatMainPanelTitle('resource', this.config.record, cmp.getValue(), true);
                                this.generateAliasRealTime(title);
                                // check some system settings before doing real time alias transliteration
                                if (parseInt(MODx.config.friendly_alias_realtime, 10) && parseInt(MODx.config.automatic_alias, 10)) {
                                    // handles the realtime-alias transliteration
                                    if (this.config.aliaswasempty && title !== '') {
                                        this.translitAlias(title);
                                    }
                                }
                            },
                            scope: this
                        }
                        // also do realtime transliteration of alias on blur of pagetitle field
                        // as sometimes (when typing very fast) the last letter(s) are not caught
                        ,blur: {
                            fn: function(cmp, e) {
                                const title = Ext.util.Format.stripTags(cmp.getValue());
                                this.generateAliasRealTime(title);
                            },
                            scope: this
                        }
                    }
                }]
            },{
                columnWidth: .3
                ,items: [{
                    xtype: 'textfield'
                    ,fieldLabel: _('resource_alias')
                    ,description: '<b>[[*alias]]</b><br>'+_('resource_alias_help')
                    ,name: 'alias'
                    ,id: 'modx-resource-alias'
                    ,maxLength: (aliasLength > 191 || aliasLength === 0) ? 191 : aliasLength
                    ,value: config.record.alias || ''
                    ,listeners: {
                        change: {fn: function(f,e) {
                                // when the alias is manually cleared, enable real time alias
                                if (Ext.isEmpty(f.getValue())) {
                                    this.config.aliaswasempty = true;
                                }
                            }, scope: this}
                    }
                }]
            }]
        },{
            xtype: 'textarea'
            ,fieldLabel: _('goodnews.mail_summary')
            ,description: '<b>[[*introtext]]</b><br>'+_('goodnews.mail_summary_desc')
            ,name: 'introtext'
            ,id: 'modx-resource-introtext'
            ,value: config.record.introtext || ''
        },
        this.getContentField(config)];
    }

    ,getMainRightFields: function(config) {
        config = config || {};
        return [{
            id: 'goodnewsresource-send-to'
            ,cls: 'modx-resource-panel'
            ,title: _('goodnews.mail_send_to')
            ,collapsible: true
            ,stateful: true
            ,stateEvents: ['collapse', 'expand']
            ,getState: function() {
                return { collapsed: this.collapsed };
            }
            ,items: [{
                xtype: 'modx-tree'
                ,id: 'goodnewsresource-tree-groupscategories'
                ,cls: 'gonr-tree-groupscategories'
                ,url: GoodNewsResource.connector_url
                ,action: 'Bitego\\GoodNews\\Processors\\Group\\GroupCategoryGetNodes'
                ,baseParams: {
                    addModxGroups: true
                    ,resourceID: config.record.id || 0
                }
                ,autoHeight: true
                ,height: 200
                ,root: {
                    text: _('goodnews.mail_groups_categories')
                    ,id: 'n_gongrp_0'
                    ,cls: 'tree-pseudoroot-node'
                    ,iconCls: 'icon-tags'
                    ,draggable: false
                    ,nodeType: 'async'
                }
                ,rootVisible: false
                ,enableDD: false
                ,ddAppendOnly: true
                ,useDefaultToolbar: true
                ,stateful: false
                ,collapsed: false
                ,listeners: {
                    'afterrender': function(){
                        var tree = Ext.getCmp('goodnewsresource-tree-groupscategories');
                        tree.expandAll();
                    },
                    'checkchange': function(node,checked){
                        // make dirty
                        this.fireEvent('fieldChange');
                        node.expand();
                        // check all leafs (categories) if node (group) is checked
                        if(checked){
                            node.eachChild(function(n) {
                                n.getUI().toggleCheck(checked);
                            });
                        }else{
                            pn = node.parentNode;
                            pn.getUI().toggleCheck(checked);
                        }
                    }
                    ,scope:this
                }
            }]
        },{
            defaults: {
                layout: 'form',
                anchor: '100%',
                labelSeparator: '',
                validationEvent: 'change',
                msgTarget: 'under',
                defaults: {
                    layout: 'form',
                    anchor: '100%',
                    validationEvent: 'change',
                    msgTarget: 'under'
                }
            }
            ,id: 'goodnewsresource-mailing-options'
            ,cls: 'modx-resource-panel'
            ,title: _('goodnews.mail_options')
            ,collapsible: true
            ,stateful: true
            ,stateEvents: ['collapse', 'expand']
            ,getState: function() {
                return { collapsed: this.collapsed };
            }
            ,items: [{
                xtype: 'modx-combo'
                ,id: 'goodnewsresource-mail-format'
                ,fieldLabel: _('goodnews.mail_format')
                ,description: '<b>[[*richtext]]</b><br>'+_('goodnews.mail_format_desc')
                ,name: 'richtext'
                ,hiddenName: 'richtext'
                ,store: [
                    [1,_('goodnews.mail_format_html')],
                    [0,_('goodnews.mail_format_plaintxt')]
                ]
                ,value: 1
                ,triggerAction: 'all'
                ,editable: false
                ,selectOnFocus: false
                ,preventRender: true
                ,forceSelection: true
                ,enableKeyEvents: true
                ,listeners: {
                    'select': {
                        scope:this
                        ,fn:function(combo,record,index) {
                            var tplsel = Ext.getCmp('goodnewsresource-template');
                            // Hide/show template selector
                            if (index==0) {
                                tplsel.show();
                            } else {
                                tplsel.hide();
                            }
                        }
                    }
                }
            },{
                xtype: 'modx-combo'
                ,id: 'goodnewsresource-template'
                ,url: GoodNewsResource.connector_url
                ,baseParams: {
                    action: 'Bitego\\GoodNews\\Processors\\Mailing\\MailingTemplatesGetList'
                    ,catid: config.record.templatesCategory || 0
                    ,limit: '0'
                }
                ,fields: ['id','templatename','description']
                ,fieldLabel: _('goodnews.mail_template')
                ,description: '<b>[[*template]]</b><br>'+_('goodnews.mail_template_desc')
                ,name: 'template'
                ,hiddenName: 'template'
                ,displayField: 'templatename'
                ,valueField: 'id'
                ,tpl: new Ext.XTemplate(
                     '<tpl for=".">'
                    ,'    <div class="x-combo-list-item">'
                    ,'        <span style="font-weight: bold;">{templatename}</span>'
                    ,'        <br>{description}'
                    ,'    </div>'
                    ,'</tpl>'
                )
                ,pageSize: 10
                ,anchor: '100%'
                ,allowBlank: true
                ,editable: false
                ,hidden: !config.record.richtext
            }]
        },{
            defaults: {
                layout: 'form',
                anchor: '100%',
                labelSeparator: '',
                validationEvent: 'change',
                msgTarget: 'under',
                defaults: {
                    layout: 'form',
                    anchor: '100%',
                    validationEvent: 'change',
                    msgTarget: 'under'
                }
            }
            ,id: 'goodnewsresource-publishing'
            ,cls: 'modx-resource-panel'
            ,title: _('goodnews.mail_publishing')
            ,collapsible: true
            ,stateful: true
            ,stateEvents: ['collapse','expand']
            ,getState: function() {
                return { collapsed: this.collapsed };
            }
            ,items: [{
                items: [{
                    xtype: 'xcheckbox'
                    ,id: 'modx-resource-published'
                    ,ctCls: 'display-switch'
                    ,boxLabel: _('resource_published')
                    ,hideLabel: true
                    ,description: '<b>[[*published]]</b><br>'+_('resource_published_help')
                    ,name: 'published'
                    ,inputValue: 1
                    ,checked: parseInt(config.record.published)
                },{
                    xtype: 'xcheckbox'
                    ,id: 'modx-resource-deleted'
                    ,ctCls: 'display-switch'
                    ,boxLabel: _('deleted')
                    ,description: '<b>[[*deleted]]</b><br>'+_('resource_delete')
                    ,hideLabel: true
                    ,cls: 'danger'
                    ,name: 'deleted'
                    ,inputValue: 1
                    ,checked: parseInt(config.record.deleted) || false
                }]
            },{
                xtype: 'xdatetime'
                ,id: 'modx-resource-publishedon'
                ,fieldLabel: _('resource_publishedon')
                ,description: '<b>[[*publishedon]]</b><br>'+_('resource_publishedon_help')
                ,name: 'publishedon'
                ,allowBlank: true
                ,dateFormat: MODx.config.manager_date_format
                ,timeFormat: MODx.config.manager_time_format
                ,startDay: parseInt(MODx.config.manager_week_start)
                ,dateWidth: '100%'
                ,timeWidth: '100%'
                ,offset_time: MODx.config.server_offset_time
                ,value: config.record.publishedon
            },{
                xtype: MODx.config.publish_document ? 'xdatetime' : 'hidden'
                ,id: 'modx-resource-pub-date'
                ,fieldLabel: _('goodnews.mail_sending_scheduled')
                ,description: '<b>[[*pub_date]]</b><br>'+_('goodnews.mail_sending_scheduled_desc')
                ,name: 'pub_date'
                ,allowBlank: true
                ,dateFormat: MODx.config.manager_date_format
                ,timeFormat: MODx.config.manager_time_format
                ,startDay: parseInt(MODx.config.manager_week_start)
                ,dateWidth: '100%'
                ,timeWidth: '100%'
                ,offset_time: MODx.config.server_offset_time
                ,value: config.record.pub_date
            },{
                xtype: MODx.config.publish_document ? 'modx-combo-user' : 'hidden'
                ,fieldLabel: _('resource_createdby')
                ,description: '<b>[[*createdby]]</b><br>'+_('resource_createdby_help')
                ,name: 'created_by'
                ,hiddenName: 'createdby'
                ,id: 'modx-resource-createdby'
                ,anchor: '100%'
                ,value: config.record.createdby || MODx.user.id
            }]
        }
    ]}
    
    ,getContentField: function(config) {
        return {
            id: 'modx-resource-content'
            ,layout: 'form'
            ,autoHeight: true
            ,hideMode: 'offsets'
            ,items: [{
                id: 'modx-content-above'
                ,border: false
            },{
                xtype: 'textarea'
                ,name: 'ta'
                ,id: 'ta'
                ,fieldLabel: _('goodnews.mail_body')
                ,anchor: '100%'
                ,height: 488
                ,grow: false
                ,value: (config.record.content || config.record.ta) || ''
            },{
                id: 'modx-content-below'
                ,border: false
            }]
        };
    }
});
Ext.reg('goodnewsresource-panel-mailing',GoodNewsResource.panel.Mailing);
