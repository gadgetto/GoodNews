/**
 * Generates the Groups/Categories Tree
 *
 * @class GoodNews.tree.GroupsCategories
 * @extends MODx.tree.Tree
 * @param {Object} config An object of options.
 * @xtype goodnews-tree-groupscategories
 */
GoodNews.tree.GroupsCategories = function(config) {
    config = config || {};

    Ext.applyIf(config,{
        xtype: 'modx-tree'
        ,id: 'goodnews-tree-groupscategories'
        ,url: GoodNews.config.connectorUrl
        ,action: 'Bitego\\GoodNews\\Processors\\Group\\GroupCategoryGetNodes'
        ,autoHeight: false
        ,height: 280
        ,root: {
            text: _('goodnews.import_subscribers_grpcat')
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
            'checkchange': function(node,checked){
                // make dirty
                this.fireEvent('fieldChange');
                // check parent node (group) if child (category) is checked
                if(checked){
                    pn = node.parentNode;
                    pn.getUI().toggleCheck(checked);
                    node.expand();
                // uncheck all child (category) nodes if parent (group) is unchecked
                }else{
                    node.collapse();
                    node.eachChild(function(n) {
                        n.getUI().toggleCheck(checked);
                    });
                }
            }
            ,scope:this
        }
    });
    GoodNews.tree.GroupsCategories.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.tree.GroupsCategories,MODx.tree.Tree,{});
Ext.reg('goodnews-tree-groupscategories',GoodNews.tree.GroupsCategories);


/**
 * The Subscribers import panel
 *
 * @class GoodNews.panel.ImportSubscribers
 * @extends Ext.Panel
 * @param {Object} config An object of options.
 * @xtype goodnews-panel-import-subscribers
 */
GoodNews.panel.ImportSubscribers = function(config) {
    config = config || {};

    Ext.applyIf(config,{
        id: 'goodnews-panel-import-subscribers'
        ,title: _('goodnews.import_subscribers_tab')   
        ,defaults: { 
            border: false 
        }
        ,items:[{
            html: '<p>'+_('goodnews.import_subscribers_tab_desc')+'</p>'
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
                        xtype: 'fileuploadfield'
                        ,name: 'csvfile'
                        ,id: 'csvfile'
                        ,buttonText: _('goodnews.import_subscribers_csvfile_button')
                        ,fieldLabel: _('goodnews.import_subscribers_csvfile')
                        ,anchor: '100%'
                    },{
                        xtype: MODx.expandHelp ? 'label' : 'hidden'
                        ,forId: 'csvfile'
                        ,html: _('goodnews.import_subscribers_csvfile_desc')
                        ,cls: 'desc-under'
                    },{
                        xtype: 'numberfield'
                        ,name: 'batchsize'
                        ,id: 'batchsize'
                        ,allowDecimals: false
                        ,allowNegative: false
                        ,autoStripChars: true
                        ,minValue: 0
                        ,value: 0
                        ,fieldLabel: _('goodnews.import_subscribers_batchsize')
                        ,description: MODx.expandHelp ? '' : _('goodnews.import_subscribers_batchsize_desc')
                        ,anchor: '100%'
                    },{
                        xtype: MODx.expandHelp ? 'label' : 'hidden'
                        ,forId: 'batchsize'
                        ,html: _('goodnews.import_subscribers_batchsize_desc')
                        ,cls: 'desc-under'
                    },{
                        xtype: 'textfield'
                        ,name: 'delimiter'
                        ,id: 'delimiter'
                        ,value: ','
                        ,maxLength: 1
                        ,fieldLabel: _('goodnews.import_subscribers_delimiter')
                        ,description: MODx.expandHelp ? '' : _('goodnews.import_subscribers_delimiter_desc')
                        ,anchor: '100%'
                    },{
                        xtype: MODx.expandHelp ? 'label' : 'hidden'
                        ,forId: 'delimiter'
                        ,html: _('goodnews.import_subscribers_delimiter_desc')
                        ,cls: 'desc-under'
                    },{
                        xtype: 'textfield'
                        ,name: 'enclosure'
                        ,id: 'enclosure'
                        ,value: '"'
                        ,maxLength: 1
                        ,fieldLabel: _('goodnews.import_subscribers_enclosure')
                        ,description: MODx.expandHelp ? '' : _('goodnews.import_subscribers_enclosure_desc')
                        ,anchor: '100%'
                    },{
                        xtype: MODx.expandHelp ? 'label' : 'hidden'
                        ,forId: 'enclosure'
                        ,html: _('goodnews.import_subscribers_enclosure_desc')
                        ,cls: 'desc-under'
                    }]
                },{
                    columnWidth: .5
                    ,items: [{
                        xtype: 'hidden'
                        ,name: 'groupscategories'
                    },{
                        xtype: 'fieldset'
                        ,title: _('goodnews.import_subscribers_assign_grpcat')
                        ,id: 'goodnews-assign-grpcat'
                        ,defaults: {
                            msgTarget: 'under'
                        }
                        ,items: [{
                            xtype: 'goodnews-tree-groupscategories'
                        },{
                            xtype: 'xcheckbox'
                            ,name: 'update'
                            ,id: 'update'
                            ,hideLabel: true
                            ,boxLabel: _('goodnews.import_subscribers_update_existing')
                            ,description: MODx.expandHelp ? '' : _('goodnews.import_subscribers_update_existing_desc')
                            ,anchor: '100%'
                        },{
                            xtype: MODx.expandHelp ? 'label' : 'hidden'
                            ,forId: 'update'
                            ,html: _('goodnews.import_subscribers_update_existing_desc')
                            ,cls: 'desc-under'
                        }]
                    }]
                }]
            }]
        }]
    });
    GoodNews.panel.ImportSubscribers.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.panel.ImportSubscribers,Ext.Panel,{});
Ext.reg('goodnews-panel-import-subscribers', GoodNews.panel.ImportSubscribers);
