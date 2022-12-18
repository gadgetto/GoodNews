/**
 * Panel to manage mailings and newsletters.
 * 
 * @class GoodNews.panel.Newsletters
 * @extends Ext.Panel
 * @param {Object} config An object of options.
 * @xtype goodnews-panel-newsletters
 */
GoodNews.panel.Newsletters = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        id: 'goodnews-panel-newsletters'
        ,title: _('goodnews.newsletters')
        ,layout: 'anchor'
        ,defaults: { 
            border: false
        }
        ,items:[{
            html: '<p>'+_('goodnews.newsletters_management_desc')+'</p>'
            ,xtype: 'modx-description'
        },{
            xtype: 'goodnews-grid-newsletters'
            ,cls: 'main-wrapper'
            ,preventRender: true
        }]
    });
    GoodNews.panel.Newsletters.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.panel.Newsletters,Ext.Panel);
Ext.reg('goodnews-panel-newsletters', GoodNews.panel.Newsletters);


// constants (equivalent to php constants in NewsletterGetListProcessor)
var GON_NEWSLETTER_STATUS_NOT_PUBLISHED     = 0;
var GON_NEWSLETTER_STATUS_NOT_READY_TO_SEND = 1;    
var GON_NEWSLETTER_STATUS_NOT_YET_SENT      = 2;
var GON_NEWSLETTER_STATUS_STOPPED           = 3;
var GON_NEWSLETTER_STATUS_IN_PROGRESS       = 4;
var GON_NEWSLETTER_STATUS_SENT              = 5;
var GON_NEWSLETTER_STATUS_SCHEDULED         = 6;

// constants (equivalent to php constants in GoodNewsRecipientHandler)
var GON_USER_NOT_YET_SENT = 0;
var GON_USER_SENT         = 1;
var GON_USER_SEND_ERROR   = 2;
var GON_USER_RESERVED     = 4;


/**
 * A taskrunner to automatically refresh grid each n seconds
 */
var tr1 = new Ext.util.TaskRunner;
var gridrefresh = {
    run: function(){
        var newslettersGrid = Ext.getCmp('goodnews-grid-newsletters');
        newslettersGrid.loadMask.disable();
        newslettersGrid.refresh();
        newslettersGrid.loadMask.enable();
    }
    ,interval: 5000 // = 5 seconds
}


/**
 * Grid which lists mailings and newsletters.
 * 
 * @class GoodNews.grid.Newsletters
 * @extends MODx.grid.Grid
 * @param {Object} config An object of options.
 * @xtype goodnews-grid-newsletters
 */
GoodNews.grid.Newsletters = function(config) {
    config = config || {};
    
    // Content of the expanded newsletter grid row
    var nlInfos = [
        '<table id="gon-nlinfo-{id}" class="gon-expinfos">',
            '<tr>',
                '<td class="gon-expinfos-key">'+_('goodnews.id')+'</td><td class="gon-expinfos-val">{id}</td>',
                '<td class="gon-expinfos-key">'+_('goodnews.newsletter_createdon')+'</td><td class="gon-expinfos-val">{createdon_formatted}</td>',
                '<td class="gon-expinfos-key">'+_('goodnews.newsletter_publishedon')+'</td><td class="gon-expinfos-val">{publishedon_formatted}</td>',
                '<td class="gon-expinfos-key">'+_('goodnews.newsletter_sender')+'</td><td class="gon-expinfos-val">{sentby_username}</td>',
                '<td class="gon-expinfos-key">'+_('goodnews.newsletter_scheduled')+'</td><td class="gon-expinfos-val gon-scheduled">{pub_date_formatted}</td>',
            '</tr>',
            '<tr>',
                '<td class="gon-expinfos-key"></td><td class="gon-expinfos-val"></td>',
                '<td class="gon-expinfos-key">'+_('goodnews.newsletter_createdby')+'</td><td class="gon-expinfos-val">{createdby_username}</td>',
                '<td class="gon-expinfos-key">'+_('goodnews.newsletter_publishedby')+'</td><td class="gon-expinfos-val">{publishedby_username}</td>',
                '<td class="gon-expinfos-key"></td><td class="gon-expinfos-val"></td>',
                '<td class="gon-expinfos-key"></td><td class="gon-expinfos-val"></td>',
            '</tr>',
            '<tr>',
                '<td class="gon-expinfos-key"></td><td class="gon-expinfos-val"></td>',
                '<td class="gon-expinfos-key">'+_('goodnews.newsletter_sending_errors')+'</td><td class="gon-expinfos-val">{recipients_error}</td>',
                '<td class="gon-expinfos-key">'+_('goodnews.newsletter_soft_bounces')+'</td><td class="gon-expinfos-val">{soft_bounces}</td>',
                '<td class="gon-expinfos-key">'+_('goodnews.newsletter_hard_bounces')+'</td><td class="gon-expinfos-val">{hard_bounces}</td>',
                '<td class="gon-expinfos-key"></td><td class="gon-expinfos-val"></td>',
            '</tr>',
        '</table>'
        ].join('\n');

    // A row expander for newsletter grid rows (additional informations)
    this.exp = new Ext.ux.grid.RowExpander({
        tpl: new Ext.Template(nlInfos)
        ,enableCaching: false
        ,lazyRender: false
    });

    // Newsletter title and action buttons renderer    
    this.tplPageTitle = new Ext.XTemplate(
        '<tpl for=".">'
            +'<h3 class="gon-newsletter-title"><a href="?a=resource/update&id={id}" title="'+_('goodnews.newsletter_update')+'" class="x-grid-link">{pagetitle}</a></h3>'
            +'<tpl if="actions !== null">'
                +'<ul class="actions">'
                    +'<tpl for="actions">'
                        +'<li><button type="button" class="controlBtn {className}"{disabled}>{text}</button></li>'
                    +'</tpl>'
                +'</ul>'
            +'</tpl>'
        +'</tpl>'
    ,{compiled: true});

    Ext.applyIf(config,{
        id: 'goodnews-grid-newsletters'
        ,url: GoodNews.config.connectorUrl
        ,baseParams: { action: 'Bitego\\GoodNews\\Processors\\Mailing\\GetList' }
        ,fields: [
            'id'
            ,'pagetitle'
            ,'createdon_formatted'
            ,'createdby_username'
            ,'publishedon_formatted'
            ,'publishedby_username'
            ,'senton_formatted'
            ,'sentby'
            ,'sentby_username'
            ,'finishedon_formatted'
            ,'pub_date_formatted'
            ,'uri'
            ,'uri_override'
            ,'richtext'
            ,'deleted'
            ,'content'
            ,'preview_url'
            ,'recipients_total'
            ,'test_recipients_total'
            ,'recipients_sent'
            ,'recipients_total_sent'
            ,'recipients_open'
            ,'recipients_error'
            ,'ipc_status'
            ,'scheduled'
            ,'soft_bounces'
            ,'hard_bounces'
            ,'status'
            ,'statusmessage'
            ,'menu'
            ,'actions'
        ]
        ,emptyText: _('goodnews.newsletters_none')
        ,paging: true
        ,pageSize: 10
        ,remoteSort: true
        ,plugins: [this.exp]
        ,autoExpandColumn: 'pagetitle'
        ,columns: [
        this.exp
        ,{
            header: _('goodnews.id')
            ,dataIndex: 'id'
            ,hidden: true
            ,sortable: true
            ,width: 40
        },{
            header: _('goodnews.newsletter_title')
            ,id: 'main' //needed for styling purposes
            ,dataIndex: 'pagetitle'
            ,sortable: false
            ,width: 200
            ,renderer: {fn:this._renderPageTitle,scope:this}
        },{
            header: _('goodnews.newsletter_sent_on')
            ,dataIndex: 'senton_formatted'
            ,sortable: false
            ,width: 80
        },{
            header: _('goodnews.newsletter_finished_on')
            ,dataIndex: 'finishedon_formatted'
            ,sortable: false
            ,width: 80
        },{
            header: _('goodnews.newsletter_recipients_sent')
            ,dataIndex: 'recipients_total_sent'
            ,sortable: false
            ,align: 'center'
            ,width: 80
        },{
            header: _('goodnews.newsletter_status')
            ,dataIndex: 'statusmessage'
            ,sortable: false
            ,width: 150
            ,resizable: false
            ,fixed: true
            ,renderer: function(value,meta,record){
                switch (record.get('status')){
                    case GON_NEWSLETTER_STATUS_NOT_PUBLISHED:
                        return '<span class="gon-nlstatus gon-not-published">'+value+'</span>';
                        break;
                    case GON_NEWSLETTER_STATUS_NOT_READY_TO_SEND:
                        return '<span class="gon-nlstatus gon-not-ready-to-send">'+value+'</span>';
                        break;
                    case GON_NEWSLETTER_STATUS_NOT_YET_SENT:
                        return '<span class="gon-nlstatus gon-not-sent">'+value+'</span>';
                        break;
                    case GON_NEWSLETTER_STATUS_STOPPED:
                        return '<span class="gon-nlstatus gon-stopped">'+value+'</span>';
                        break;
                    case GON_NEWSLETTER_STATUS_IN_PROGRESS:
                        return '<span class="gon-nlstatus gon-in-progress">'+value+'</span>';
                        break;
                    case GON_NEWSLETTER_STATUS_SENT:
                        return '<span class="gon-nlstatus gon-finished">'+value+'</span>';
                        break;
                    case GON_NEWSLETTER_STATUS_SCHEDULED:
                        return '<span class="gon-nlstatus gon-scheduled">'+value+'</span>';
                        break;
                    default:
                        return value;
                }
            }
        }]
        ,tbar:[{
            text: _('goodnews.newsletter_create')
            ,handler: this.createNewsletter
            ,scope: this
            ,cls: 'primary-button'
        },'-',{
            xtype: 'xcheckbox'
            ,disabled: GoodNews.config.isGoodNewsAdmin ? false : true
            ,id: 'workerprocess-emergency-stop'
            ,name: 'worker_process_active'
            ,boxLabel: _('goodnews.newsletter_send_process_stop')
            ,description: _('goodnews.newsletter_send_process_toggle_tooltip')
            ,hideLabel: true
            ,ctCls: 'gon-checkbox-toggle'
            ,cls: 'warning'
            ,inputValue: 1
            ,checked: GoodNews.config.workerProcessActive ? false : true
            ,listeners: {
                'check': function(cbx,checked){
                    this.toggleWorkerProcess(cbx,checked);
                }
                ,scope:this
            }
        },'->',{
            xtype: 'xcheckbox'
            ,id: 'autorefresh'
            ,name: 'autorefresh'
            ,boxLabel: _('goodnews.newsletter_grid_autorefresh')
            ,description: _('goodnews.newsletter_grid_autorefresh_tooltip')
            ,hideLabel: true
            ,ctCls: 'gon-checkbox-toggle'
            ,inputValue: 1
            ,checked: true
            ,listeners: {
                'check': function(cbx,checked){
                    this.toggleAutoRefresh(cbx,checked);
                }
                ,scope:this
            }
        },'-',{
            xtype: 'modx-combo'
            ,id: 'goodnews-newsletters-filter'
            ,emptyText: _('goodnews.newsletter_filter')
            ,store: [
                 ['',_('goodnews.newsletter_filter_all')]
                ,['scheduled',_('goodnews.newsletter_filter_scheduled')]
                ,['published',_('published')]
                ,['unpublished',_('unpublished')]
                ,['deleted',_('deleted')]
            ]
            ,name: 'filter'
            ,hiddenName: 'filter'
            ,value: ''
            ,triggerAction: 'all'
            ,editable: false
            ,selectOnFocus: false
            ,preventRender: true
            ,forceSelection: true
            ,enableKeyEvents: true
            ,listeners: {
                'select': {fn:this.newsletterFilter,scope:this}
            }
        },'-',{
            xtype: 'textfield'
            ,cls: 'x-form-filter'
            ,id: 'goodnews-newsletters-search-filter'
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
                },scope:this}
            }
        },{
            xtype: 'button'
            ,cls: 'x-form-filter-clear'
            ,id: 'modx-newsletter-filter-clear'
            ,text: _('goodnews.button_filter_clear')
            ,listeners: {
                'click': {fn: this.clearFilter, scope: this}
            }
        }]
    });
    GoodNews.grid.Newsletters.superclass.constructor.call(this,config);
    tr1.start(gridrefresh);
    this.on('click',this.handleActionButtons,this);
};
Ext.extend(GoodNews.grid.Newsletters,MODx.grid.Grid,{
    getMenu: function() {
        var r = this.getSelectionModel().getSelected();
        var p = r.data.perm;

        // Context menu configurations for deleted resources
        if (r.data.deleted == true) {
            return [{
                text: _('goodnews.newsletter_undelete')
                ,handler: this.undeleteNewsletter
            }];
        // Context menu configurations for all resources except deleted
        } else {
            switch (r.data.status){
                case GON_NEWSLETTER_STATUS_NOT_PUBLISHED:
                    return [{
                        text: _('goodnews.newsletter_preview')
                        ,handler: this.previewNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_test_send')
                        ,handler: this.testSendNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_update')
                        ,handler: this.updateNewsletter
                    },{
                        text: _('goodnews.newsletter_remove')
                        ,handler: this.removeNewsletter
                    },{
                        text: _('goodnews.newsletter_publish')
                        ,handler: this.publishNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_sendlog_view')
                        ,handler: this.viewLog
                    }];
                    break;
                case GON_NEWSLETTER_STATUS_NOT_READY_TO_SEND:
                    return [{
                        text: _('goodnews.newsletter_preview')
                        ,handler: this.previewNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_test_send')
                        ,handler: this.testSendNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_update')
                        ,handler: this.updateNewsletter
                    },{
                        text: _('goodnews.newsletter_remove')
                        ,handler: this.removeNewsletter
                    },{
                        text: _('goodnews.newsletter_unpublish')
                        ,handler: this.unpublishNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_sendlog_view')
                        ,handler: this.viewLog
                    }];
                    break;
                case GON_NEWSLETTER_STATUS_NOT_YET_SENT:
                    return [{
                        text: _('goodnews.newsletter_start_sending')
                        ,handler: this.startSendNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_preview')
                        ,handler: this.previewNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_test_send')
                        ,handler: this.testSendNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_update')
                        ,handler: this.updateNewsletter
                    },{
                        text: _('goodnews.newsletter_remove')
                        ,handler: this.removeNewsletter
                    },{
                        text: _('goodnews.newsletter_unpublish')
                        ,handler: this.unpublishNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_sendlog_view')
                        ,handler: this.viewLog
                    }];
                    break;
                case GON_NEWSLETTER_STATUS_STOPPED:
                    return [{
                        text: _('goodnews.newsletter_continue_sending')
                        ,handler: this.continueSendNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_view')
                        ,handler: this.previewNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_remove')
                        ,handler: this.removeNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_sendlog_view')
                        ,handler: this.viewLog
                    }];
                    break;
                case GON_NEWSLETTER_STATUS_IN_PROGRESS:
                    return [{
                        text: _('goodnews.newsletter_stop_sending')
                        ,handler: this.stopSendNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_view')
                        ,handler: this.previewNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_sendlog_view')
                        ,handler: this.viewLog
                    }];
                    break;
                case GON_NEWSLETTER_STATUS_SENT:
                    return [{
                        text: _('goodnews.newsletter_view')
                        ,handler: this.previewNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_remove')
                        ,handler: this.removeNewsletter
                    },{
                        text: _('goodnews.newsletter_unpublish')
                        ,handler: this.unpublishNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_sendlog_view')
                        ,handler: this.viewLog
                    }];
                    break;
                case GON_NEWSLETTER_STATUS_SCHEDULED:
                    return [{
                        text: _('goodnews.newsletter_preview')
                        ,handler: this.previewNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_test_send')
                        ,handler: this.testSendNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_update')
                        ,handler: this.updateNewsletter
                    },{
                        text: _('goodnews.newsletter_remove')
                        ,handler: this.removeNewsletter
                    },{
                        text: _('goodnews.newsletter_publish')
                        ,handler: this.publishNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_sendlog_view')
                        ,handler: this.viewLog
                    }];
                    break;
            }
        }
    }
	,handleActionButtons: function(e) {
		var t = e.getTarget();
		var elm = t.className.split(' ')[0];
		if(elm == 'controlBtn') {
			var action = t.className.split(' ')[1];
			var record = this.getSelectionModel().getSelected();
            this.menu.record = record.data;
			switch (action) {
                case 'start':
                    this.startSendNewsletter();
                    break;
                case 'stop':
                    this.stopSendNewsletter();
                    break;
                case 'continue':
                    this.continueSendNewsletter();
                    break;
                case 'test':
                    this.testSendNewsletter();
                    break;
                case 'delete':
                    this.removeNewsletter();
                    break;
                case 'undelete':
                    this.undeleteNewsletter();
                    break;
                case 'edit':
					this.updateNewsletter();
                    break;
				case 'publish':
					this.publishNewsletter();
					break;
				case 'unpublish':
					this.unpublishNewsletter();
					break;
				case 'preview':
					this.previewNewsletter();
					break;
				case 'log':
					this.viewLog(t,e);
					break;
				default:
					break;
            }
		}
	}	
    ,newsletterFilter: function(cb,nv,ov) {
        this.getStore().baseParams.filter = Ext.isEmpty(nv) || Ext.isObject(nv) ? cb.getValue() : nv;
        this.getBottomToolbar().changePage(1);
        this.refresh();
        return true;
    }
    ,search: function(tf,nv,ov) {
        var s = this.getStore();
        s.baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }
    ,toggleWorkerProcess: function(cbx,emergencystop) {
        MODx.Ajax.request({
            url: GoodNews.config.connectorUrl
            ,params: {
                action: 'Bitego\\GoodNews\\Processors\\Send\\SwitchSendProcess'
                ,emergencystop: emergencystop
            }
            ,method: 'post'
            ,scope: this
            ,listeners: {
                'success':{fn:function(r) {
                    Ext.Msg.alert(_('success'), r.message);
                },scope:this}
                ,'failure':{fn:function(r) {
                    // Restore state of button to previous value
                    cbx.reset();
                    Ext.Msg.alert(_('failure'), r.message);
                },scope:this}
            }
        });
    }
    ,toggleAutoRefresh: function(cbx,autorefresh) {
        if (autorefresh == true) {
            tr1.start(gridrefresh);
        } else {
            tr1.stop(gridrefresh);
        }  
    }
    ,clearFilter: function() {
    	this.getStore().baseParams = {
            action: 'Bitego\\GoodNews\\Processors\\Mailing\\GetList'
    	};
        Ext.getCmp('goodnews-newsletters-filter').reset();
        Ext.getCmp('goodnews-newsletters-search-filter').reset();
    	this.getBottomToolbar().changePage(1);
        this.refresh();
    }
    ,createNewsletter: function(btn,e) {
        var tpl = '';
        if (GoodNews.config.mailingTemplate) {
            tpl = '&template='+GoodNews.config.mailingTemplate;
        }
        var classKey = 'Bitego\\GoodNews\\Model\\GoodNewsResourceMailing';
        var parent = GoodNews.config.userCurrentContainer;
        var contextKey = GoodNews.config.contextKey;
        MODx.loadPage(
            'resource/create',
            'class_key='+classKey+'&parent='+parent+'&context_key='+contextKey+tpl
        );
    }
    ,previewNewsletter: function(btn,e) {
        if (this.menu.record.richtext == true) {
            window.open(this.menu.record.preview_url);
        } else {
            var win = MODx.load({
                xtype: 'goodnews-window-preview'
            });
            win.setValues(this.menu.record);
            win.show(); // had to remove "e.target" param because it's undefined here (don't know why)
        }
    }
    ,testSendNewsletter: function(btn,e) {
        if (this.menu.record.test_recipients_total <= 0) {
            Ext.MessageBox.show({
                title : _('goodnews.newsletter_test_send')
                ,msg : _('goodnews.newsletter_no_testrecipients')
                ,width : 300
                ,buttons : Ext.MessageBox.OK
                ,icon : Ext.MessageBox.WARNING
            });
            return;
        }
        Ext.MessageBox.show({
            title: _('goodnews.newsletter_test_send')
            ,msg: _('goodnews.newsletter_sending_test')+' ('+_('goodnews.newsletter_recipients')+this.menu.record.test_recipients_total+')'
            ,width: 300
            ,wait: true
            ,waitConfig: {interval: 200}
        });
        MODx.Ajax.request({
            url: GoodNews.config.connectorUrl
            ,params: {
                action: 'Bitego\\GoodNews\\Processors\\Send\\SendTest'
                ,mailingid: this.menu.record.id
            }
            ,method: 'post'
            ,scope: this
            ,listeners: {
                'success':{fn:function(r) {
                    Ext.MessageBox.hide();
                    MODx.msg.status({
                        title: _('success')
                        ,message: _('goodnews.newsletter_finished_sending_test')
                        ,delay: 3
                    })
                    Ext.getCmp('goodnews-grid-newsletters').refresh();
                },scope:this}
                ,'failure':{fn:function(r) {
                    // @todo: handle test sending failure
                    
                },scope:this}
            }
        });
    }
    ,startSendNewsletter: function(btn,e) {
        MODx.msg.confirm({
            title: _('goodnews.newsletter_start_sending')
            ,text: _('goodnews.newsletter_start_sending_confirm')+' ('+_('goodnews.newsletter_recipients')+this.menu.record.recipients_open+')'
            ,url: GoodNews.config.connectorUrl
            ,params: {
                action: 'Bitego\\GoodNews\\Processors\\Send\\StartSending'
                ,mailingid: this.menu.record.id
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }
    ,stopSendNewsletter: function(btn,e) {
        MODx.msg.confirm({
            title: _('goodnews.newsletter_stop_sending')
            ,text: _('goodnews.newsletter_stop_sending_confirm')+' ('+_('goodnews.newsletter_recipients')+this.menu.record.recipients_open+')'
            ,url: GoodNews.config.connectorUrl
            ,params: {
                action: 'Bitego\\GoodNews\\Processors\\Send\\StopSending'
                ,mailingid: this.menu.record.id
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }
    ,continueSendNewsletter: function(btn,e) {
        MODx.msg.confirm({
            title: _('goodnews.newsletter_continue_sending')
            ,text: _('goodnews.newsletter_continue_sending_confirm')+' ('+_('goodnews.newsletter_recipients')+this.menu.record.recipients_open+')'
            ,url: GoodNews.config.connectorUrl
            ,params: {
                action: 'Bitego\\GoodNews\\Processors\\Send\\ContinueSending'
                ,mailingid: this.menu.record.id
            }
            ,listeners: {
                'success':{fn:this.refresh,scope:this}
            }
        });
    }
    ,updateNewsletter: function(btn,e) {
        MODx.loadPage('resource/update', 'id='+this.menu.record.id);
    }
    ,removeNewsletter: function() {
        MODx.msg.confirm({
            title: _('goodnews.newsletter_remove')
            ,text: _('goodnews.newsletter_remove_confirm')
            ,url: MODx.config.connector_url
            ,params: {
                action: 'Resource/Delete'
                ,id: this.menu.record.id
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }
    ,undeleteNewsletter: function(btn,e) {
        MODx.Ajax.request({
            url: MODx.config.connector_url
            ,params: {
                action: 'Resource/Undelete'
                ,id: this.menu.record.id
            }
            ,listeners: {
                'success':{fn:this.refresh,scope:this}
            }
        });
    }
    ,publishNewsletter: function(btn,e) {
        MODx.Ajax.request({
            url: MODx.config.connector_url
            ,params: {
                action: 'Resource/Publish'
                ,id: this.menu.record.id
            }
            ,listeners: {
                'success':{fn:this.refresh,scope:this}
            }
        });
    }
    ,unpublishNewsletter: function(btn,e) {
        MODx.Ajax.request({
            url: MODx.config.connector_url
            ,params: {
                action: 'Resource/Unpublish'
                ,id: this.menu.record.id
            }
            ,listeners: {
                'success':{fn:this.refresh,scope:this}
            }
        });
    }
	,_renderPageTitle:function(v,md,rec) {
		return this.tplPageTitle.apply(rec.data);
	}
    ,viewLog: function(btn,e) {
        this.NewsletterLogWindow = MODx.load({
            xtype: 'goodnews-window-newsletter-log'
            ,params: {
                mailingid: this.menu.record.id
                ,mailingtitle: this.menu.record.pagetitle
            }
        });
        //this.SendNewsletterWindow.setValues(this.menu.record);
        this.NewsletterLogWindow.show(e.target);
    }
});
Ext.reg('goodnews-grid-newsletters',GoodNews.grid.Newsletters);


/**
 * Window to preview a plain-text mailing.
 * 
 * @class GoodNews.window.Preview
 * @extends MODx.Window
 * @param {Object} config An object of options.
 * @xtype goodnews-window-preview
 */
GoodNews.window.Preview = function(config) {
    config = config || {};
    
    Ext.applyIf(config,{
        id: 'goodnews-window-preview'
        ,title: _('goodnews.mail_plaintext_preview')
        ,width: 800
        ,closeAction: 'close'
        ,fields: [{
            xtype: 'textfield'
            ,fieldLabel: _('goodnews.mail_subject')
            ,name: 'pagetitle'
            ,readOnly: true
            ,anchor: '100%'
        },{
            xtype: 'textarea'
            ,fieldLabel: _('goodnews.mail_body')
            ,name: 'content'
            ,readOnly: true
            ,autoHeight: false
            ,height: Ext.getBody().getViewSize().height*.40
            ,cls: 'gon-mail-preview-textarea'
            ,anchor: '100%'
        }]
        ,buttons: [{
            text: _('close')
            ,scope: this
            ,handler: function() { this.close(); }
        }]
    });
    GoodNews.window.Preview.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.window.Preview,MODx.Window);
Ext.reg('goodnews-window-preview',GoodNews.window.Preview);


/**
 * Window to view the mailing send-log.
 * 
 * @class GoodNews.window.NewsletterLogWindow
 * @extends MODx.Window
 * @param {Object} config An object of options.
 * @xtype goodnews-window-newsletter-log
 */
GoodNews.window.NewsletterLogWindow = function(config) {
    config = config || {};
    
    Ext.applyIf(config,{
        title: _('goodnews.newsletter_sendlog_window_title')+config.params.mailingtitle
        ,id: 'goodnews-window-newsletter-log'
        ,maximizable: true
        ,modal: false
        ,minWidth: 680
        ,minHeight:600
        ,layout: 'fit'
        ,style: {
            width: '680px'
            ,height: '600px'
        }
        ,items : [{
            xtype: 'goodnews-grid-sendlog'
            ,params: {
                mailingid: config.params.mailingid
            }
            ,preventRender: true
        }]
        ,buttons: [{ 
            text: _('goodnews.newsletter_sendlog_export_button')
            ,cls: 'primary-button'
            ,handler: function() {
                Ext.getCmp('goodnews-grid-sendlog').exportSendLog();
            }
        },{ 
            text: _('goodnews.newsletter_sendlog_close_button')
            ,handler: function() {
                this.close();
            }
            ,scope: this
        }]
        ,listeners: {
            'render': {fn: function(win) {
                this.setSize(680,600);
            },scope:this}
        }
    });
    GoodNews.window.NewsletterLogWindow.superclass.constructor.call(this,config); 
};
Ext.extend(GoodNews.window.NewsletterLogWindow,Ext.Window);
Ext.reg('goodnews-window-newsletter-log',GoodNews.window.NewsletterLogWindow);


/**
 * Grid which lists a mailing send-log.
 * 
 * @class GoodNews.grid.SendLog
 * @extends MODx.grid.Grid
 * @param {Object} config An object of options.
 * @xtype goodnews-grid-send-log
 */
GoodNews.grid.SendLog = function(config) {
    config = config || {};
    
    Ext.applyIf(config,{
        id: 'goodnews-grid-sendlog'
        ,url: GoodNews.config.connectorUrl
        ,baseParams: {
            action: 'Bitego\\GoodNews\\Processors\\Mailing\\Sendlog\\GetList'
            ,mailingid: config.params.mailingid
        }
        ,fields: [
            'id'
            ,'mailing_id'
            ,'subscriber_id'
            ,'subscriber_email'
            ,'subscriber_fullname'
            ,'statustime'
            ,'status'
        ]
        ,emptyText: _('goodnews.sendlog_none')
        ,paging: true
        ,remoteSort: true
        ,columns: [{
            header: _('goodnews.id')
            ,dataIndex: 'id'
            ,sortable: true
            ,hidden: true
        },{
            header: _('goodnews.sendlog_subscriber_email')
            ,dataIndex: 'subscriber_email'
            ,sortable: true
        },{
            header: _('goodnews.sendlog_subscriber_fullname')
            ,dataIndex: 'subscriber_fullname'
            ,sortable: true
        },{
            header: _('goodnews.sendlog_statustime')
            ,dataIndex: 'statustime'
            ,sortable: true
        },{
            header: _('goodnews.sendlog_status')
            ,dataIndex: 'status'
            ,sortable: true
            ,width: 80
        }]
        ,tbar:['->',{
            xtype: 'modx-combo'
            ,id: 'goodnews-sendlog-status-filter'
            ,emptyText: _('goodnews.sendlog_status_filter')
            ,width: 200
            ,listWidth: 200
            ,displayField: 'statusname'
            ,valueField: 'status'
            ,mode: 'local'
            ,store: new Ext.data.ArrayStore({
                fields: ['status','statusname']
                ,data: [
                     [GON_USER_SENT,_('goodnews.sendlog_status_sent')]
                    ,[GON_USER_SEND_ERROR,_('goodnews.sendlog_status_send_error')]
                ]
            })
            ,listeners: {
                'select': {fn:this.filterByStatus,scope:this}
            }
        },'-',{
            xtype: 'textfield'
            ,cls: 'x-form-filter'
            ,id: 'goodnews-sendlog-search-filter'
            ,emptyText: _('goodnews.input_search_filter')
            ,listeners: {
                'change': {fn: this.search,scope:this}
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
                },scope:this}
            }
        },{
            xtype: 'button'
            ,cls: 'x-form-filter-clear'
            ,id: 'goodnews-sendlog-filter-clear'
            ,text: _('goodnews.button_filter_clear')
            ,listeners: {
                'click': {fn: this.clearFilter, scope: this}
            }
        }]
    });
    GoodNews.grid.SendLog.superclass.constructor.call(this,config);
};
Ext.extend(GoodNews.grid.SendLog,MODx.grid.Grid,{
    filterByStatus: function(combo) {
        var s = this.getStore();
        s.baseParams.statusfilter = combo.getValue();
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
            action: 'Bitego\\GoodNews\\Processors\\Mailing\\Sendlog\\GetList'
            ,mailingid: this.config.params.mailingid
    	};
        Ext.getCmp('goodnews-sendlog-status-filter').reset();
        Ext.getCmp('goodnews-sendlog-search-filter').reset();
    	this.getBottomToolbar().changePage(1);
        this.refresh();
    }
    ,exportSendLog: function() {
        var s = this.getStore();
        var action = 'Bitego\\GoodNews\\Processors\\Mailing\\Sendlog\\Export';
        var mailingid = s.baseParams.mailingid;
        location.href = GoodNews.config.connectorUrl+'?action='+action+'&mailingid='+mailingid+'&HTTP_MODAUTH='+MODx.siteId;
    }
});
Ext.reg('goodnews-grid-sendlog',GoodNews.grid.SendLog);
