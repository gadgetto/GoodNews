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
    Ext.applyIf(config,{});
    GoodNewsResource.panel.Mailing.superclass.constructor.call(this,config);
};
Ext.extend(GoodNewsResource.panel.Mailing,MODx.panel.Resource,{
    beforeSubmit: function(o) {
        var ta = Ext.get('ta');
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
        if(this.getForm().baseParams.action == 'create') {
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
            title: _('goodnews.mailing')
            ,id: 'modx-resource-settings'
            ,cls: 'modx-resource-tab'
            ,layout: 'form'
            ,labelAlign: 'top'
            ,labelSeparator: ''
            ,bodyCssClass: 'tab-panel-wrapper main-wrapper'
            ,autoHeight: true
            ,defaults: {
                border: false
                ,msgTarget: 'under'
                ,width: 400
            }
            ,items: this.getMainFields(config)
        });
        it.push(this.getResourceCollectionTabs(config));
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
            ,animCollapse: false
            ,itemId: 'tabs'
            ,items: it
        });

        if (MODx.config.tvs_below_content == 1) {
            var tvs = this.getTemplateVariablesPanel(config);
            tvs.style = 'margin-top: 10px';
            its.push(tvs);
        }
        return its;
    }
    ,getResourceCollectionTabs: function(config) {
        var cTabs = [];
        if (config.record.collection1Name && config.record.collection1Parents) {
            cTabs.push({
                id: 'goodnewsresource-collection1-tab'
                ,autoHeight: true
                ,title: _('goodnews.mailing_resource_collection')+config.record.collection1Name
                ,layout: 'form'
                ,anchor: '100%'
                ,items: [{
                    html: '<p>'+_('goodnews.mailing_resource_collection_desc')+'</p>'
                    ,bodyCssClass: 'panel-desc'
                    ,border: false
                },{
                    xtype: 'goodnewsresource-grid-collect-resources'
                    ,baseParams: {
                        action: 'mgr/collection/getResourceList'
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
                ,autoHeight: true
                ,title: _('goodnews.mailing_resource_collection')+config.record.collection2Name
                ,layout: 'form'
                ,anchor: '100%'
                ,items: [{
                    html: '<p>'+_('goodnews.mailing_resource_collection_desc')+'</p>'
                    ,bodyCssClass: 'panel-desc'
                    ,border: false
                },{
                    xtype: 'goodnewsresource-grid-collect-resources'
                    ,baseParams: {
                        action: 'mgr/collection/getResourceList'
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
                ,autoHeight: true
                ,title: _('goodnews.mailing_resource_collection')+config.record.collection3Name
                ,layout: 'form'
                ,anchor: '100%'
                ,items: [{
                    html: '<p>'+_('goodnews.mailing_resource_collection_desc')+'</p>'
                    ,bodyCssClass: 'panel-desc'
                    ,border: false
                },{
                    xtype: 'goodnewsresource-grid-collect-resources'
                    ,baseParams: {
                        action: 'mgr/collection/getResourceList'
                        ,parentIds: config.record.collection3Parents
                        ,collectionIds: config.record.collection3 || ''
                        ,collectionInternalName: 'collection3'
                    }
                }]
            });
        }
        return cTabs;
    }
    ,getPageHeader: function(config) {
        config = config || {record:{}};
        return {
            html: '<h2>'+_('goodnews.mailing_new')+'</h2>'
            ,id: 'modx-resource-header'
            ,cls: 'modx-page-header'
            ,border: false
            ,forceLayout: true
            ,anchor: '100%'
        };
    }
    ,getMainLeftFields: function(config) {
        config = config || {record:{}};
        var mlf = [{
            xtype: 'hidden'
            ,name: 'class_key'
            ,value: 'GoodNewsResourceMailing'
        },{
            xtype: 'hidden'
            ,name: 'longtitle'
            ,id: 'modx-resource-longtitle'
            ,value: config.record.longtitle || ''
        },{
            xtype: 'hidden'
            ,name: 'menutitle'
            ,id: 'modx-resource-menutitle'
            ,value: config.record.menutitle || ''
        },{
            xtype: 'hidden'
            ,name: 'link_attributes'
            ,id: 'modx-resource-link-attributes'
            ,value: config.record.link_attributes || ''
        },{
            xtype: 'hidden'
            ,name: 'hidemenu'
            ,id: 'modx-resource-hidemenu'
            ,value: config.record.hidemenu
        },{
            xtype: 'hidden'
            ,name: 'groupscategories'
        },{
            xtype: 'hidden'
            ,name: 'collection1'
            ,id: 'goodnewsresource-collection1'
        },{
            xtype: 'hidden'
            ,name: 'collection2'
            ,id: 'goodnewsresource-collection2'
        },{
            xtype: 'hidden'
            ,name: 'collection3'
            ,id: 'goodnewsresource-collection3'
        }];
        mlf.push({
            xtype: 'textfield'
            ,fieldLabel: _('goodnews.mail_subject')+'<span class="required">*</span>'
            ,description: '<b>[[*pagetitle]]</b><br />'+_('goodnews.mail_subject_desc')
            ,name: 'pagetitle'
            ,id: 'modx-resource-pagetitle'
            ,maxLength: 255
            ,anchor: '100%'
            ,allowBlank: false
            ,enableKeyEvents: true
            ,listeners: {
                'keyup': {scope:this,fn:function(f,e) {
                    var title = Ext.util.Format.stripTags(f.getValue());
                    Ext.getCmp('modx-resource-header').getEl().update('<h2>'+title+'</h2>');
                    }
                }
            }
        });
        mlf.push({
            xtype: 'textarea'
            ,fieldLabel: _('goodnews.mail_summary')
            ,description: '<b>[[*introtext]]</b><br />'+_('goodnews.mail_summary_desc')
            ,name: 'introtext'
            ,id: 'modx-resource-introtext'
            ,grow: true
            ,anchor: '100%'
            ,value: config.record.introtext || ''
        });
        var ct = this.getContentField(config);
        if (ct) {
            mlf.push(ct);
        }
        return mlf;
    }
    ,getContentField: function(config) {
        return [{
            id: 'modx-content-above'
            ,border: false
        },{
            xtype: 'textarea'
            ,fieldLabel: _('goodnews.mail_body')
            ,name: 'ta'
            ,id: 'ta'
            ,anchor: '100%'
            ,height: 450
            ,grow: false
            ,value: (config.record.content || config.record.ta) || ''
            ,itemCls: 'contentblocks_replacement'
        },{
            id: 'modx-content-below'
            ,border: false
        }];
    }
    ,getMainRightFields: function(config) {
        config = config || {};
        return [{
            xtype: 'fieldset'
            ,title: _('goodnews.mail_send_to')
            ,id: 'goodnewsmailing-box-send-to'
            ,defaults: {
                msgTarget: 'under'
            }
            ,items: [{
                xtype: 'modx-tree'
                ,id: 'goodnewsresource-tree-groupscategories'
                ,url: GoodNewsResource.connector_url
                ,action: 'mgr/groups/getGroupCatNodes'
                ,baseParams: {
                    addModxGroups: true
                    ,resourceID: config.record.id || 0
                }
                ,autoHeight: false
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
                ,cls: 'gonr-tree-groupscategories'
                ,listeners: {
                    'afterrender': function(){
                        var tree = Ext.getCmp('goodnewsresource-tree-groupscategories');
                        tree.expandAll();
                    },
                    'checkchange': function(node,checked){
                        // make dirty
                        this.fireEvent('fieldChange');
                        node.expand();
                        // check all leafes (categories) if node (group) is checked
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
            xtype: 'fieldset'
            ,title: _('goodnews.mail_options')
            ,id: 'goodnewsmailing-box-options'
            ,defaults: {
                msgTarget: 'under'
            }
            ,items: [{
                xtype: 'modx-combo'
                ,id: 'goodnewsresource-mail-format'
                ,fieldLabel: _('goodnews.mail_format')
                ,description: '<b>[[*richtext]]</b><br />'+_('goodnews.mail_format_desc')
                ,name: 'richtext'
                ,hiddenName: 'richtext'
                ,store: [[1,_('goodnews.mail_format_html')],[0,_('goodnews.mail_format_plaintxt')]]
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
                            var tplsel = Ext.getCmp('modx-resource-template');
                            // Hide/show template selector
                            if (index==0) {
                                tplsel.show();
                                tplsel.setWidth(300); // workaround (elements width isn't set correctly)
                            } else {
                                tplsel.hide();
                            }
                        }
                    }
                }
            },{
                xtype: 'modx-combo'
                ,id: 'modx-resource-template'
                ,url: GoodNewsResource.connector_url
                ,baseParams: {
                    action: 'mgr/mailing/getTplList'
                    ,catid: config.record.templatesCategory || 0
					,limit: '0'
                }
                ,fields: ['id','templatename','description']
                ,fieldLabel: _('goodnews.mail_template')
                ,description: '<b>[[*template]]</b><br />'+_('goodnews.mail_template_desc')
                ,name: 'template'
                ,hiddenName: 'template'
                ,displayField: 'templatename'
                ,valueField: 'id'
                ,tpl: new Ext.XTemplate(
                     '<tpl for=".">'
                    ,'    <div class="x-combo-list-item">'
                    ,'        <span style="font-weight: bold">{templatename}</span>'
                    ,'        <br />{description}'
                    ,'    </div>'
                    ,'</tpl>'
                )
                ,pageSize: 10
                ,anchor: '100%'
                ,listWidth: 350
                ,allowBlank: true
                ,editable: false
                ,hidden: !config.record.richtext
            },{
                xtype: 'textfield'
                ,fieldLabel: _('resource_alias')
                ,description: '<b>[[*alias]]</b><br />'+_('resource_alias_help')
                ,name: 'alias'
                ,id: 'modx-resource-alias'
                ,maxLength: 100
                ,anchor: '100%'
                ,value: config.record.alias || ''
            }]
        },{
            xtype: 'fieldset'
            ,title: _('goodnews.mail_publishing_information')
            ,id: 'goodnewsmailing-box-publishing-information'
            ,defaults: {
                msgTarget: 'under'
            }
            ,items: [{
                xtype: 'modx-combo'
                ,fieldLabel: _('goodnews.mail_status')
                ,name: 'published'
                ,hiddenName: 'published'
                ,store: [[1,_('published')],[0,_('unpublished')]]
                ,value: 0
                ,triggerAction: 'all'
                ,editable: false
                ,selectOnFocus: false
                ,preventRender: true
                ,forceSelection: true
                ,enableKeyEvents: true
            },{
                xtype: 'xdatetime'
                ,fieldLabel: _('resource_publishedon')
                ,description: '<b>[[*publishedon]]</b><br />'+_('resource_publishedon_help')
                ,name: 'publishedon'
                ,id: 'modx-resource-publishedon'
                ,allowBlank: true
                ,dateFormat: MODx.config.manager_date_format
                ,timeFormat: MODx.config.manager_time_format
                ,dateWidth: 120
                ,timeWidth: 120
                ,value: config.record.publishedon
            },{
                xtype: MODx.config.publish_document ? 'xdatetime' : 'hidden'
                ,fieldLabel: _('goodnews.mail_sending_scheduled')
                ,description: '<b>[[*pub_date]]</b><br />'+_('goodnews.mail_sending_scheduled_desc')
                ,name: 'pub_date'
                ,id: 'modx-resource-pub-date'
                ,allowBlank: true
                ,dateFormat: MODx.config.manager_date_format
                ,timeFormat: MODx.config.manager_time_format
                ,dateWidth: 120
                ,timeWidth: 120
                ,value: config.record.pub_date
            },{
                xtype: MODx.config.publish_document ? 'modx-combo-user' : 'hidden'
                ,fieldLabel: _('resource_createdby')
                ,description: '<b>[[*createdby]]</b><br />'+_('resource_createdby_help')
                ,name: 'created_by'
                ,hiddenName: 'createdby'
                ,id: 'modx-resource-createdby'
                ,anchor: '100%'
                ,value: config.record.createdby || MODx.user.id
            }]
        }]
    }
});
Ext.reg('goodnewsresource-panel-mailing',GoodNewsResource.panel.Mailing);
