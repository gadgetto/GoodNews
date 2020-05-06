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
        ,layout: 'anchor'
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

    this.selectedRecords = [];
    this.selectedRecords[this.key] = [];
    this.selectionRestoreFinished = false;

    this.sm = new Ext.grid.CheckboxSelectionModel({
        listeners: {
            'rowselect': function(sm,rowIndex,record) {
                this.rememberRow(record.id);
            },scope: this
            ,'rowdeselect': function(sm,rowIndex,record) {
                this.forgetRow(record.id);
            },scope: this
        }
    });

    // Content of the expanded subscriber grid row
    var subscrInfos = [
        '<table id="gon-subscrinfo-{id}" class="gon-expinfos">',
            '<tr>',
                '<td class="gon-expinfos-key">'+_('goodnews.id')+'</td><td class="gon-expinfos-val">{id}</td>',
            '</tr>',
            '<tr>',
                '<td class="gon-expinfos-key">'+_('goodnews.modx_username')+'</td><td class="gon-expinfos-val">{username}</td>',
            '</tr>',
            '<tr>',
                '<td class="gon-expinfos-key">'+_('goodnews.groups')+'</td><td class="gon-expinfos-val">{grpcount}</td>',
            '</tr>',
            '<tr>',
                '<td class="gon-expinfos-key">'+_('goodnews.categories')+'</td><td class="gon-expinfos-val">{catcount}</td>',
            '</tr>',
            '<tr>',
                '<td class="gon-expinfos-key">'+_('goodnews.subscriber_subscribed_on')+'</td><td class="gon-expinfos-val">{subscribedon_formatted}</td>',
            '</tr>',
            '<tr>',
                '<td class="gon-expinfos-key">'+_('goodnews.subscriber_ip')+'</td><td class="gon-expinfos-val">{ip}</td>',
            '</tr>',
            '<tr>',
                '<td class="gon-expinfos-key">'+_('goodnews.subscriber_activated_on')+'</td><td class="gon-expinfos-val">{activatedon_formatted}</td>',
            '</tr>',
            '<tr>',
                '<td class="gon-expinfos-key">'+_('goodnews.subscriber_ip_activated')+'</td><td class="gon-expinfos-val">{ip_activated}</td>',
            '</tr>',
        '</table>'
        ].join('\n');

    // A row expander for subscribers grid rows (additional informations)
    this.exp = new Ext.ux.grid.RowExpander({
        tpl: new Ext.Template(subscrInfos)
        ,enableCaching: false
        ,lazyRender: false
    });

    Ext.applyIf(config,{
        id: 'goodnews-grid-subscribers'
        ,url: GoodNews.config.connectorUrl
        ,baseParams: { action: 'mgr/subscribers/getList' }
        ,fields: [
            'id'
            ,'email'
            ,'fullname'
            ,'testdummy'
            ,'active'
            ,'subscribedon_formatted'
            ,'activatedon_formatted'
            ,'username'
            ,'ip'
            ,'ip_activated'
            ,'soft_bounces'
            ,'hard_bounces'
            ,'hasmeta'
            ,'grpcount'
            ,'catcount'
            ,'menu'
        ]
        ,emptyText: _('goodnews.subscribers_none')
        ,paging: true
        ,remoteSort: true
        ,sm: this.sm
        ,plugins: [this.exp]
        ,autoExpandColumn: 'email'
        ,columns: [
        this.sm
        ,this.exp
        ,{
            header: _('goodnews.subscriber_email')
            ,dataIndex: 'email'
            ,sortable: true
            ,width: 100
            ,renderer: function(value,meta,record){
                var addCls = '';
                if (record.get('hasmeta') === false) {
                    addCls = ' gon-no-goodnewsmeta';
                } else {
                    if (record.get('grpcount') === 0) {
                        addCls = ' gon-no-subscriptions';
                    }
                }
                return '<span class="gon-subscriber-email'+addCls+'">'+value+'</span>';
            }
        },{
            header: _('goodnews.subscriber_fullname')
            ,dataIndex: 'fullname'
            ,sortable: true
            ,width: 100
            ,renderer: function(value,meta,record){
                var addCls = '';
                if (record.get('hasmeta') === false) {
                    addCls = ' gon-no-goodnewsmeta';
                } else {
                    if (record.get('grpcount') === 0) {
                        addCls = ' gon-no-subscriptions';
                    }
                }
                return '<span class="'+addCls+'">'+Ext.util.Format.htmlEncode(value)+'</span>';
            }
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
            ,dataIndex: 'subscribedon_formatted'
            ,sortable: true
            ,width: 80
        },{
            header: _('goodnews.subscriber_soft_bounces')
            ,dataIndex: 'soft_bounces'
            ,align: 'center'
            ,sortable: true
            ,width: 30
        },{
            header: _('goodnews.subscriber_hard_bounces')
            ,dataIndex: 'hard_bounces'
            ,align: 'center'
            ,sortable: true
            ,width: 30
        }]
        ,tbar: {
            xtype: 'container'
            ,layout: 'anchor'
            ,defaults: {
                anchor: '0'
            }
            ,defaultType: 'toolbar'
            ,items: [{
                items: [{
                    text: '<i class="icon icon-download icon-lg"></i>&nbsp;' + _('goodnews.import_button')
                    ,handler: this.importSubscribers
                    ,scope: this
                },{
                    text: '<i class="icon icon-upload icon-lg"></i>&nbsp;' + _('goodnews.export_button')
                    ,handler: this.exportSubscribers
                    ,scope: this
                },'->',{
                    xtype: 'modx-combo'
                    ,id: 'goodnews-subscribers-group-filter'
                    ,emptyText: _('goodnews.subscribers_user_group_filter')
                    ,width: 250
                    ,listWidth: 250
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
                    ,id: 'goodnews-subscribers-category-filter'
                    ,emptyText: _('goodnews.subscribers_user_category_filter')
                    ,width: 250
                    ,listWidth: 250
                    ,displayField: 'name'
                    ,valueField: 'id'
                    ,store: new Ext.data.JsonStore({
                        url: GoodNews.config.connectorUrl
                        ,baseParams: {
                            action : 'mgr/category/getCategoryFilterList'
                            ,addNoCategoryOption: true
                        }
                        ,fields: ['id','name']
                        ,root: 'results'
                    })
                    ,listeners: {
                        'select': {fn: this.filterByUserCategory, scope: this}
                    }
                }]
            },{
                items: [{
                    text: _('goodnews.modx_user_create')
                    ,handler: this.createSubscriber
                    ,scope: this
                    ,cls: 'primary-button'
                },'-',{
                    text: _('bulk_actions')
                    ,xtype: 'splitbutton'
                    ,menu: [{
                        text: _('goodnews.subscriber_assign_multi')
                        ,handler: this.assignMulti
                        ,scope: this
                    },{
                        text: _('goodnews.subscriber_remove_multi')
                        ,handler: this.removeMulti
                        ,scope: this
                    },'-',{
                        text: _('goodnews.subscriber_reset_bounce_counters_multi')
                        ,handler: this.resetBounceCountersMulti
                        ,scope: this
                    },{
                        text: _('goodnews.subscriber_remove_subscriptions_multi')
                        ,handler: this.removeSubscriptionsMulti
                        ,scope: this
                    },{
                        text: _('goodnews.subscriber_remove_meta_data_multi')
                        ,handler: this.removeMetaMulti
                        ,scope: this
                    }]
                },'->',{
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
                        //@todo: there is a problem using 0/1 or false/true combination for id field
                        ,data: [['nodummy',_('no')],['isdummy',_('yes')]]
                    })
                    ,listeners: {
                        'select': {fn:this.filterByTestDummy,scope:this}
                    }
                },'-',{
                    xtype: 'modx-combo'
                    ,id: 'goodnews-subscribers-active-filter'
                    ,emptyText: _('goodnews.subscriber_active_filter')
                    ,width: 150
                    ,listWidth: 150
                    ,displayField: 'yesno'
                    ,valueField: 'id'
                    ,mode: 'local'
                    ,store: new Ext.data.ArrayStore({
                        fields: ['id','yesno']
                        //@todo: there is a problem using 0/1 or false/true combination for id field
                        ,data: [['inactive',_('no')],['active',_('yes')]]
                    })
                    ,listeners: {
                        'select': {fn:this.filterByActive,scope:this}
                    }
                },'-',{
                    xtype: 'textfield'
                    ,cls: 'x-form-filter'
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
                    ,cls: 'x-form-filter-clear'
                    ,id: 'goodnews-subscribers-filter-clear'
                    ,text: _('goodnews.button_filter_clear')
                    ,listeners: {
                        'click': {fn: this.clearFilter, scope: this}
                    }
                }]
            }]
        }
    });
    GoodNews.grid.Subscribers.superclass.constructor.call(this,config);
    this.getView().on('refresh',this.refreshSelection,this);
};
Ext.extend(GoodNews.grid.Subscribers,MODx.grid.Grid,{
    getMenu: function() {
        var r = this.getSelectionModel().getSelected();
        var p = r.data.perm;

        return [{
            text: _('goodnews.subscriber_update')
            ,handler: this.updateSubscriber
        },'-',{
            text: _('goodnews.subscriber_reset_bounce_counters')
            ,handler: this.resetBounceCounters
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
    ,rememberRow: function(userid) {
        if (this.selectedRecords[this.key].indexOf(userid) == -1){
            this.selectedRecords[this.key].push(userid);
        }
    }
    ,forgetRow: function(userid) {
        this.selectedRecords[this.key].remove(userid);
    }
    ,forgetAllRows: function() {
        this.selectedRecords = [];
        this.selectedRecords[this.key] = [];
    }
    ,refreshSelection: function() {
        var rowsToSelect = [];        
        Ext.each(this.selectedRecords[this.key],function(item){
            rowsToSelect.push(this.store.indexOfId(item));
        },this);       
        Ext.getCmp(this.config.id).getSelectionModel().selectRows(rowsToSelect);
        
        // workaround:
        // @todo: find fix for: checkboxes are only checked in first grid
        if (!this.selectionRestoreFinished) {
            this.refresh(); 
            this.selectionRestoreFinished = true;
        }
    }
    ,getSelectedAsList: function(){
        return this.selectedRecords[this.key].join();
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
    ,exportSubscribers: function(btn,e) {
        var s = this.getStore();
        var win = MODx.load({
            xtype: 'goodnews-window-subscribers-export'
            ,exportcount: s.totalLength
        });
        win.setValues({ 
            query: s.baseParams.query
            ,groupfilter: s.baseParams.groupfilter
            ,categoryfilter: s.baseParams.categoryfilter
            ,testdummyfilter: s.baseParams.testdummyfilter
            ,activefilter: s.baseParams.activefilter
        });
        win.show(e.target);
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
    ,resetBounceCounters: function() {
        MODx.msg.confirm({
            title: _('goodnews.subscriber_reset_bounce_counters')
            ,text: _('goodnews.subscriber_reset_bounce_counters_confirm')
            ,url: this.config.url
            ,params: {
                action: 'mgr/subscribers/resetBounceCounters'
                ,id: this.menu.record.id
            }
            ,listeners: {
                'success': {fn: this.refresh, scope: this}
            }
        });
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
    ,assignMulti: function(btn,e) {
        var sel = this.getSelectedAsList();
        if (sel === false) {
            return false;
        }
        var win = MODx.load({
            xtype: 'goodnews-window-subscribers-assign-multi'
        });
        win.setValues({ 
            userIds: sel
        });
        win.show(e.target);
    }
    ,removeMulti: function(btn,e) {
        var sel = this.getSelectedAsList();
        if (sel === false) {
            return false;
        }
        var win = MODx.load({
            xtype: 'goodnews-window-subscribers-remove-multi'
        });
        win.setValues({ 
            userIds: sel
        });
        win.show(e.target);
    }
    ,resetBounceCountersMulti: function() {
        var sel = this.getSelectedAsList();
        if (sel === false) {
            return false;
        }
        MODx.msg.confirm({
            title: _('goodnews.subscriber_reset_bounce_counters_multi')
            ,text: _('goodnews.subscriber_reset_bounce_counters_confirm_multi')
            ,url: this.config.url
            ,params: {
                action: 'mgr/subscribers/resetBounceCountersMulti'
                ,userIds: sel
            }
            ,listeners: {
                'success': {fn:function(r) {
                    // Let rows stay selected!
                    //this.forgetAllRows();
                    //this.getSelectionModel().clearSelections(true);
                    this.refresh();
                },scope:this}
            }
        });
        return true;
    }
    ,removeSubscriptionsMulti: function() {
        var sel = this.getSelectedAsList();
        if (sel === false) {
            return false;
        }
        MODx.msg.confirm({
            title: _('goodnews.subscriber_remove_subscriptions_multi')
            ,text: _('goodnews.subscriber_remove_subscriptions_confirm_multi')
            ,url: this.config.url
            ,params: {
                action: 'mgr/subscribers/removeSubscriptionsMulti'
                ,userIds: sel
            }
            ,listeners: {
                'success': {fn:function(r) {
                    // Let rows stay selected!
                    //this.forgetAllRows();
                    //this.getSelectionModel().clearSelections(true);
                    this.refresh();
                },scope:this}
            }
        });
        return true;
    }
    ,removeMetaMulti: function() {
        var sel = this.getSelectedAsList();
        if (sel === false) {
            return false;
        }
        MODx.msg.confirm({
            title: _('goodnews.subscriber_remove_meta_data_multi')
            ,text: _('goodnews.subscriber_remove_meta_data_confirm_multi')
            ,url: this.config.url
            ,params: {
                action: 'mgr/subscribers/removeMetaMulti'
                ,userIds: sel
            }
            ,listeners: {
                'success': {fn:function(r) {
                    // Let rows stay selected!
                    //this.forgetAllRows();
                    //this.getSelectionModel().clearSelections(true);
                    this.refresh();
                },scope:this}
            }
        });
        return true;
    }
    ,filterByUserGroup: function(combo) {
        var s = this.getStore();
        // reset the category filter
        Ext.getCmp('goodnews-subscribers-category-filter').reset();
        s.baseParams.categoryfilter = '';
        s.baseParams.groupfilter = combo.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }
    ,filterByUserCategory: function(combo) {
        var s = this.getStore();
        // reset the group filter
        Ext.getCmp('goodnews-subscribers-group-filter').reset();
        s.baseParams.groupfilter = '';
        s.baseParams.categoryfilter = combo.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }
    ,filterByTestDummy: function(combo) {
        var s = this.getStore();
        s.baseParams.testdummyfilter = combo.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }
    ,filterByActive: function(combo) {
        var s = this.getStore();
        s.baseParams.activefilter = combo.getValue();
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
        Ext.getCmp('goodnews-subscribers-category-filter').reset();
        Ext.getCmp('goodnews-subscribers-testdummy-filter').reset();
        Ext.getCmp('goodnews-subscribers-active-filter').reset();
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
                if(config.reverse === true){
                    // check all child (category) nodes if parent node is checked
                    if(checked){
                        node.eachChild(function(n) {                    
                            n.getUI().toggleCheck(checked);
                        });
                    }
                }else{
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


/**
 * Window to assign groups/categories and testdummy flag to a batch of subscribers.
 * 
 * @class GoodNews.window.SubscribersAssignMulti
 * @extends MODx.Window
 * @param {Object} config An object of options.
 * @xtype goodnews-window-subscribers-assign-multi
 */
GoodNews.window.SubscribersAssignMulti = function(config) {
    config = config || {};

    Ext.applyIf(config,{
        title: _('goodnews.subscriber_assign_multi')
        ,width: 480
        ,closeAction: 'close'
        ,url: GoodNews.config.connectorUrl
        ,baseParams: {
            action: 'mgr/subscribers/assignmulti'
        }
        ,fields: [{
            xtype: 'hidden'
            ,name: 'userIds'
        },{
            xtype: 'hidden'
            ,name: 'groupscategories'
        },{
            xtype: 'hidden'
            ,name: 'replaceGrpsCats'
        },{
            xtype: 'goodnews-tree-groupscategories'
            ,fieldLabel: _('goodnews.subscriber_groups_categories_multi')
        },{
            //todo: if using xcheckbox the checked status isn't set. Why?
            xtype: 'checkbox'
            ,hideLabel: true
            ,boxLabel: _('goodnews.subscriber_select_as_testdummy_multi')
            ,name: 'testdummy'
            ,inputValue: '1'
        }]
        ,buttons: [{
            text: config.cancelBtnText || _('cancel')
            ,scope: this
            ,handler: function() { config.closeAction !== 'close' ? this.hide() : this.close(); }
        },{
            text: _('goodnews.subscriber_button_save_replace')
            ,scope: this
            ,handler: function() {
                this.setValues({ 
                    replaceGrpsCats: '1'
                });
                this.submit();
            }
        },{
            text: _('goodnews.subscriber_button_save_add')
            ,cls: 'primary-button'
            ,scope: this
            ,handler: function() {
                this.setValues({ 
                    replaceGrpsCats: '0'
                });
                this.submit();
            }
        }]
        ,listeners: {
            'beforeSubmit': {fn: function() {
                    this.getSelectedGrpCat();
                }
                ,scope: this
            }
            ,'success': {fn: function() {
                    Ext.getCmp('goodnews-grid-subscribers').refresh();
                }
                ,scope: this
            }
        }
    });
    GoodNews.window.SubscribersAssignMulti.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.window.SubscribersAssignMulti,MODx.Window,{
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
Ext.reg('goodnews-window-subscribers-assign-multi',GoodNews.window.SubscribersAssignMulti);


/**
 * Window to remove selected groups/categories from a batch of subscribers.
 * 
 * @class GoodNews.window.SubscribersRemoveMulti
 * @extends MODx.Window
 * @param {Object} config An object of options.
 * @xtype goodnews-window-subscribers-remove-multi
 */
GoodNews.window.SubscribersRemoveMulti = function(config) {
    config = config || {};

    Ext.applyIf(config,{
        title: _('goodnews.subscriber_remove_multi')
        ,width: 480
        ,closeAction: 'close'
        ,url: GoodNews.config.connectorUrl
        ,baseParams: {
            action: 'mgr/subscribers/removemulti'
        }
        ,fields: [{
            xtype: 'hidden'
            ,name: 'userIds'
        },{
            xtype: 'hidden'
            ,name: 'groupscategories'
        },{
            xtype: 'goodnews-tree-groupscategories'
            ,fieldLabel: _('goodnews.subscriber_groups_categories_multi')
            ,reverse: true
        }]
        ,buttons: [{
            text: config.cancelBtnText || _('cancel')
            ,scope: this
            ,handler: function() { config.closeAction !== 'close' ? this.hide() : this.close(); }
        },{
            text: _('goodnews.subscriber_button_save_remove')
            ,cls: 'primary-button'
            ,scope: this
            ,handler: this.submit
        }]
        ,listeners: {
            'beforeSubmit': {fn: function() {
                    this.getSelectedGrpCat();
                }
                ,scope: this
            }
            ,'success': {fn: function() {
                    Ext.getCmp('goodnews-grid-subscribers').refresh();
                }
                ,scope: this
            }
        }
    });
    GoodNews.window.SubscribersRemoveMulti.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.window.SubscribersRemoveMulti,MODx.Window,{
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
Ext.reg('goodnews-window-subscribers-remove-multi',GoodNews.window.SubscribersRemoveMulti);


/**
 * Window to export subscribers to CSV file based on selected filter.
 * 
 * @class GoodNews.window.SubscribersExport
 * @extends MODx.Window
 * @param {Object} config An object of options.
 * @xtype goodnews-window-subscribers-export
 */
GoodNews.window.SubscribersExport = function(config) {
    config = config || {};
    
    Ext.applyIf(config,{
        title: _('goodnews.export_subscribers')
        ,width: 480
        ,closeAction: 'close'
        ,url: GoodNews.config.connectorUrl
        ,baseParams: {
            action: 'mgr/subscribers/export'
        }
        ,items:[{
            html: '<p>'+_('goodnews.export_subscribers_desc',{ count: config.exportcount })+'</p>'
            ,border: false
            ,bodyCssClass: 'panel-desc'
        }]
        ,fields: [{
            xtype: 'hidden'
            ,name: 'query'
        },{
            xtype: 'hidden'
            ,name: 'groupfilter'
        },{
            xtype: 'hidden'
            ,name: 'categoryfilter'
        },{
            xtype: 'hidden'
            ,name: 'testdummyfilter'
        },{
            xtype: 'hidden'
            ,name: 'activefilter'
        },{
            xtype: 'textfield'
            ,name: 'delimiter'
            ,id: 'delimiter'
            ,value: ','
            ,maxLength: 1
            ,fieldLabel: _('goodnews.export_subscribers_delimiter')
            ,description: MODx.expandHelp ? '' : _('goodnews.export_subscribers_delimiter_desc')
            ,anchor: '100%'
        },{
            xtype: MODx.expandHelp ? 'label' : 'hidden'
            ,forId: 'delimiter'
            ,html: _('goodnews.export_subscribers_delimiter_desc')
            ,cls: 'desc-under'
        },{
            xtype: 'textfield'
            ,name: 'enclosure'
            ,id: 'enclosure'
            ,value: '"'
            ,maxLength: 1
            ,fieldLabel: _('goodnews.export_subscribers_enclosure')
            ,description: MODx.expandHelp ? '' : _('goodnews.export_subscribers_enclosure_desc')
            ,anchor: '100%'
        },{
            xtype: MODx.expandHelp ? 'label' : 'hidden'
            ,forId: 'enclosure'
            ,html: _('goodnews.export_subscribers_enclosure_desc')
            ,cls: 'desc-under'
        }]
        ,buttons: [{
            text: config.cancelBtnText || _('cancel')
            ,scope: this
            ,handler: function() { config.closeAction !== 'close' ? this.hide() : this.close(); }
        },{
            text: _('goodnews.export_subscribers_button_start')
            ,cls: 'primary-button'
            ,scope: this
            ,handler: this.submit
        }]
        ,listeners: {
            'success': {
                fn: function(o) {
        			MODx.msg.status({
                    	title: _('goodnews.export_subscribers'),
                    	message: o.a.result.message,
                    	delay: 5
                	});
        			if(o.a.result.object.total > 0){
                        location.href = MODx.config.manager_url + '?a=' + MODx.request.a + '&action=export&f=' + o.a.result.object.file;
        			}
                }
                ,scope: this
            }
        }
    });
    GoodNews.window.SubscribersExport.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.window.SubscribersExport,MODx.Window);
Ext.reg('goodnews-window-subscribers-export',GoodNews.window.SubscribersExport);