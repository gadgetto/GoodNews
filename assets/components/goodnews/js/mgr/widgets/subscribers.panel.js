/**
 * Panel to manage subscribers.
 * 
 * @class GoodNews.panel.Subscribers
 * @extends Ext.Panel
 * @param {Object} config An object of options.
 * @xtype goodnews-panel-subscribers
 */
GoodNews.panel.Subscribers = function(config) {
    config = config || {};
    
    Ext.applyIf(config,{
        id: 'goodnews-panel-subscribers'
        ,title: _('goodnews.subscribers')   
        ,defaults: { 
            border: false 
        }
        ,items:[{
            html: '<p>'+_('goodnews.subscribers_desc')+'</p>'
            ,border: false
            ,bodyCssClass: 'panel-desc'
        },{
            xtype: 'goodnews-grid-subscribers'
            ,cls: 'main-wrapper'
            ,preventRender: true
        }]    
    });
    GoodNews.panel.Subscribers.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.panel.Subscribers,Ext.Panel);
Ext.reg('goodnews-panel-subscribers',GoodNews.panel.Subscribers);


/**
 * Grid which lists subscribers.
 * 
 * @class GoodNews.grid.Subscribers
 * @extends MODx.grid.Grid
 * @param {Object} config An object of options.
 * @xtype goodnews-grid-subscribers
 */
GoodNews.grid.Subscribers = function(config){
    config = config || {};

    Ext.applyIf(config,{
        id: 'goodnews-grid-subscribers'
        ,url: GoodNews.config.connectorUrl
        ,baseParams: { action: 'mgr/subscribers/getList' }
        ,fields: [
            'id'
            ,'username'
            ,'fullname'
            ,'email'
            ,'testdummy'
            ,'active'
            ,'createdon'
            ,'ip'
            ,'grpcount'
            ,'menu'
        ]
        ,emptyText: _('goodnews.subscribers_none')
        ,paging: true
        ,remoteSort: true
        ,viewConfig: {
            forceFit: true
            ,scrollOffset: 0
            ,getRowClass: function(record, index) {
                if (record.get('grpcount') == 0) {
                    return 'gon-no-subscriptions';
                } else {
                    return '';
                }
            }
        }
        ,columns: [{
            header: _('goodnews.subscriber_email')
            ,dataIndex: 'email'
            ,sortable: true
            ,width: 100
        },{
            header: _('goodnews.subscriber_fullname')
            ,dataIndex: 'fullname'
            ,sortable: true
            ,width: 100
            ,renderer: Ext.util.Format.htmlEncode
        },{
            header: _('goodnews.subscriber_testdummy')
            ,dataIndex: 'testdummy'
            ,align: 'center'
            ,sortable: true
            ,width: 60
            ,renderer: function(value){
                switch (value){
                    case '0':
                        return '<span class="gon-testdummy-no">'+_('no')+'</span>';
                        break;
                    case '1':
                        return '<span class="gon-testdummy-yes">'+_('yes')+'</span>';
                        break;
                    default:
                        return value;
                }
            }
        },{
            header: _('goodnews.subscriber_active')
            ,dataIndex: 'active'
            ,align: 'center'
            ,sortable: true
            ,editable: false
            ,editor: { xtype: 'combo-boolean', renderer: 'boolean' }
            ,width: 60
        },{
            header: _('goodnews.subscriber_subscribed_on')
            ,dataIndex: 'createdon'
            ,sortable: true
            ,width: 80
        },{
            header: _('goodnews.subscriber_ip')
            ,dataIndex: 'ip'
            ,sortable: true
            ,width: 100
        }]
        ,tbar:[{
            text: _('goodnews.modx_user_create')
            ,handler: this.createSubscriber
            ,scope: this
            ,cls: 'primary-button'
        },'-',{
            text: _('goodnews.import_button')
            ,handler: this.importSubscribers
            ,scope: this
        },'->',{
            xtype: 'modx-combo'
            ,id: 'goodnews-subscribers-group-filter'
            ,emptyText: _('goodnews.subscribers_user_group_filter')
            ,width: 200
            ,listWidth: 200
            ,displayField: 'name'
            ,valueField: 'id'
            ,store: new Ext.data.JsonStore({
                url: GoodNews.config.connectorUrl
                ,baseParams: {
                    action : 'mgr/groups/getGroupFilterList'
                    ,addNoGroupOption: true
                }
                ,fields: ['id','name']
                ,root: 'results'
            })
            ,listeners: {
                'select': {fn: this.filterByUserGroup, scope: this}
            }
        },'-',{
            xtype: 'modx-combo'
            ,id: 'goodnews-subscribers-testdummy-filter'
            ,emptyText: _('goodnews.subscriber_testdummy_filter')
            ,width: 150
            ,listWidth: 150
            ,displayField: 'yesno'
            ,valueField: 'id'
            ,mode: 'local'
            ,store: new Ext.data.ArrayStore({
                fields: ['id','yesno']
                //there is a problem using 0/1 or false/true combination for id field
                ,data: [['nodummy',_('no')],['isdummy',_('yes')]]
            })
            ,listeners: {
                'select': {fn:this.filterByTestDummy,scope:this}
            }
        },'-',{
            xtype: 'textfield'
            ,id: 'goodnews-subscribers-search-filter'
            ,emptyText: _('goodnews.input_search_filter')
            ,listeners: {
                'change': {fn:this.search,scope:this}
                ,'render': {fn: function(cmp) {
                    new Ext.KeyMap(cmp.getEl(), {
                        key: Ext.EventObject.ENTER
                        ,fn: function() {
                            this.fireEvent('change',this);
                            this.blur();
                            return true;
                        }
                        ,scope: cmp
                    });
                }
                ,scope: this}
            }
        },{
            xtype: 'button'
            ,id: 'goodnews-subscribers-filter-clear'
            ,text: _('goodnews.button_filter_clear')
            ,listeners: {
                'click': {fn: this.clearFilter, scope: this}
            }
        }]
    });
    GoodNews.grid.Subscribers.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.grid.Subscribers,MODx.grid.Grid,{
    getMenu: function() {
        var r = this.getSelectionModel().getSelected();
        var p = r.data.perm;

        return [{
            text: _('goodnews.subscriber_update')
            ,handler: this.updateSubscriber
        },{
            text: _('goodnews.subscriber_remove_subscriptions')
            ,handler: this.removeSubscriptions
        },{
            text: _('goodnews.subscriber_remove_meta_data')
            ,handler: this.removeMeta
        },'-',{
            text: _('goodnews.user_update')
            ,handler: this.updateUser
        }];
    }
    ,createSubscriber: function(btn,e) {
        var createUser = MODx.action ? MODx.action['security/user/create'] : 'security/user/create';
        location.href = 'index.php?a='+createUser;
    }
    ,importSubscribers: function(btn,e) {
        if (GoodNews.config.isGoodNewsAdmin) {
            location.href = MODx.config.manager_url + '?a=' + MODx.request.a + '&action=import';
        }
    }
    ,updateSubscriber: function(btn,e) {
        var win = MODx.load({
            xtype: 'goodnews-window-subscriber-update'
            ,record: this.menu.record
        });
        win.setValues(this.menu.record);
        win.show(e.target);
    }
    ,updateUser: function(btn,e) {
        location.href = 'index.php?a='+MODx.action['security/user/update']+'&id='+this.menu.record.id;
    }
    ,removeSubscriptions: function() {
        MODx.msg.confirm({
            title: _('goodnews.subscriber_remove_subscriptions')
            ,text: _('goodnews.subscriber_remove_subscriptions_confirm')
            ,url: this.config.url
            ,params: {
                action: 'mgr/subscribers/removeSubscriptions'
                ,id: this.menu.record.id
            }
            ,listeners: {
                'success': {fn: this.refresh, scope: this}
            }
        });
    }
    ,removeMeta: function() {
        MODx.msg.confirm({
            title: _('goodnews.subscriber_remove_meta_data')
            ,text: _('goodnews.subscriber_remove_meta_data_confirm')
            ,url: this.config.url
            ,params: {
                action: 'mgr/subscribers/removeMeta'
                ,id: this.menu.record.id
            }
            ,listeners: {
                'success': {fn: this.refresh, scope: this}
            }
        });
    }
    ,filterByUserGroup: function(combo) {
        var s = this.getStore();
        s.baseParams.groupfilter = combo.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }
    ,filterByTestDummy: function(combo) {
        var s = this.getStore();
        s.baseParams.testdummyfilter = combo.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }
    ,search: function(tf,nv,ov) {
        var s = this.getStore();
        s.baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }
    ,clearFilter: function() {
    	this.getStore().baseParams = {
            action: 'mgr/subscribers/getList'
    	};
        Ext.getCmp('goodnews-subscribers-group-filter').reset();
        Ext.getCmp('goodnews-subscribers-testdummy-filter').reset();
        Ext.getCmp('goodnews-subscribers-search-filter').reset();
    	this.getBottomToolbar().changePage(1);
    }
});
Ext.reg('goodnews-grid-subscribers',GoodNews.grid.Subscribers);


/**
 * A tree to select GoodNews groups and categories for subscribers.
 * (is used in GoodNews.window.UpdateSubscriptions)
 * 
 * @class GoodNews.tree.GroupsCategories
 * @extends MODx.tree.Tree
 * @param {Object} config An object of options.
 * @xtype goodnews-tree-groupscategories
 */
GoodNews.tree.GroupsCategories = function(config) {
    config = config || {};
    
    Ext.applyIf(config,{
        id: 'goodnews-tree-groupscategories'
        ,url: GoodNews.config.connectorUrl
        ,action: 'mgr/groups/getGroupCatNodes'
        ,autoHeight: false
        ,height: Ext.getBody().getViewSize().height*.30
        ,root_id: 'n_gongrp_0'
        ,root_name: _('goodnews.subscriber_groups_categories')
        ,rootVisible: false
        ,enableDD: false
        ,ddAppendOnly: true
        ,useDefaultToolbar: true
        ,stateful: false
        ,collapsed: false
        ,cls: 'gon-tree-groupscategories'
        ,listeners: {
            'checkchange': function(node,checked){
                // check parent node (group) if child (category) is checked
                if(checked){
                    pn = node.parentNode;
                    pn.getUI().toggleCheck(checked);
                // uncheck all child (category) nodes if parent (group) is unchecked
                }else{
                    node.eachChild(function(n) {                    
                        n.getUI().toggleCheck(checked);
                    });
                }
            }
        }
    });
    GoodNews.tree.GroupsCategories.superclass.constructor.call(this,config);
    this.expandAll();
};
Ext.extend(GoodNews.tree.GroupsCategories,MODx.tree.Tree);
Ext.reg('goodnews-tree-groupscategories',GoodNews.tree.GroupsCategories);


/**
 * Window to edit subscribers/subscriptions.
 * 
 * @class GoodNews.window.UpdateSubscriptions
 * @extends MODx.Window
 * @param {Object} config An object of options.
 * @xtype goodnews-window-subscriber-update
 */
GoodNews.window.UpdateSubscriptions = function(config) {
    config = config || {};

    Ext.applyIf(config,{
        title: _('goodnews.subscriber_update')
        ,width: 480
        ,closeAction: 'close'
        ,url: GoodNews.config.connectorUrl
        ,baseParams: {
            action: 'mgr/subscribers/update'
        }
        ,fields: [{
            xtype: 'hidden'
            ,name: 'id'
        },{
            xtype: 'hidden'
            ,name: 'groupscategories'
        },{
            xtype: 'textfield'
            ,fieldLabel: _('goodnews.subscriber_username')
            ,name: 'username'
            ,readOnly: true
            ,anchor: '100%'
        },{
            xtype: 'goodnews-tree-groupscategories'
            ,fieldLabel: _('goodnews.subscriber_groups_categories')
            ,baseParams: { userID: config.record.id }
        },{
            //todo: if using xcheckbox the checked status isn't set. Why?
            xtype: 'checkbox'
            ,hideLabel: true
            ,boxLabel: _('goodnews.subscriber_select_as_testdummy')
            ,name: 'testdummy'
            ,inputValue: '1'
        }]
        ,listeners: {
            'beforeSubmit': {fn: this.getSelectedGrpCat, scope: this}
            ,'success': {fn: function() {
                    Ext.getCmp('goodnews-grid-subscribers').refresh();
                }
                ,scope: this
            }
        }
    });
    GoodNews.window.UpdateSubscriptions.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.window.UpdateSubscriptions,MODx.Window,{
    getSelectedGrpCat: function() {
        // get selected groups and categories from tree
        var nodeIDs = '';
        var selNodes;
        var tree = Ext.getCmp('goodnews-tree-groupscategories');
        
        selNodes = tree.getChecked();
        Ext.each(selNodes, function(node){
            if (nodeIDs!='') {
                nodeIDs += ',';
            }
            nodeIDs += node.id;
        });
        //console.log('node IDs: ' + nodeIDs);

        // write selected nodes to hidden field
        this.setValues({ 
          groupscategories: nodeIDs
        });
    }    
});
Ext.reg('goodnews-window-subscriber-update',GoodNews.window.UpdateSubscriptions);
