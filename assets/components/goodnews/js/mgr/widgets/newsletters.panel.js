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
        ,defaults: { 
            border: false 
        }
        ,items:[{
            html: '<p>'+_('goodnews.newsletters_management_desc')+'</p>'
            ,border: false
            ,bodyCssClass: 'panel-desc'
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

/**
 * A taskrunner to automatically refresh grid each n seconds
 */
var tr1 = new Ext.util.TaskRunner;
var gridrefresh = {
    run: function(){
         Ext.getCmp('goodnews-grid-newsletters').refresh();
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

    // Cron trigger status display values
    if (GoodNews.config.cronTriggerStatus == true) {
        var cronStatusText = '<span class="gon-ok"><strong>'+_('goodnews.newsletter_sending_process_enabled')+'</strong></span>';
    } else {
        var cronStatusText = '<span class="gon-nok"><strong>'+_('goodnews.newsletter_sending_process_disabled')+'</strong></span>';
    }
    
    // Content of the expanded newsletter grid row
    var nlInfos = [
        '<table id="gon-nlinfo-{id}" class="gon-nlinfos">',
            '<tr>',
                '<td class="gon-nlinfos-key">'+_('goodnews.newsletter_id')+'</td><td class="gon-nlinfos-val">{id}</td>',
                '<td class="gon-nlinfos-key">'+_('goodnews.newsletter_createdon')+'</td><td class="gon-nlinfos-val">{createdon_formatted}</td>',
                '<td class="gon-nlinfos-key">'+_('goodnews.newsletter_publishedon')+'</td><td class="gon-nlinfos-val">{publishedon_formatted}</td>',
                '<td class="gon-nlinfos-key">'+_('goodnews.newsletter_sender')+'</td><td class="gon-nlinfos-val">{sentby_username}</td>',
                '<td class="gon-nlinfos-key">'+_('goodnews.newsletter_scheduled')+'</td><td class="gon-nlinfos-val gon-scheduled">{pub_date_formatted}</td>',
            '</tr>',
            '<tr>',
                '<td class="gon-nlinfos-key"></td><td class="gon-nlinfos-val"></td>',
                '<td class="gon-nlinfos-key">'+_('goodnews.newsletter_createdby')+'</td><td class="gon-nlinfos-val">{createdby_username}</td>',
                '<td class="gon-nlinfos-key">'+_('goodnews.newsletter_publishedby')+'</td><td class="gon-nlinfos-val">{publishedby_username}</td>',
                '<td class="gon-nlinfos-key"></td><td class="gon-nlinfos-val"></td>',
                '<td class="gon-nlinfos-key"></td><td class="gon-nlinfos-val"></td>',
            '</tr>',
            '<tr>',
                '<td class="gon-nlinfos-key"></td><td class="gon-nlinfos-val"></td>',
                '<td class="gon-nlinfos-key">'+_('goodnews.newsletter_soft_bounces')+'</td><td class="gon-nlinfos-val">{soft_bounces}</td>',
                '<td class="gon-nlinfos-key">'+_('goodnews.newsletter_hard_bounces')+'</td><td class="gon-nlinfos-val">{hard_bounces}</td>',
                '<td class="gon-nlinfos-key"></td><td class="gon-nlinfos-val"></td>',
                '<td class="gon-nlinfos-key"></td><td class="gon-nlinfos-val"></td>',
            '</tr>',
        '</table>'
        ].join('\n');

    // A row expander for newsletter grid rows (additional informations)
    this.expander = new Ext.ux.grid.RowExpander({
        tpl: new Ext.Template(nlInfos)
        ,enableCaching: false
    });

    Ext.applyIf(config,{
        id: 'goodnews-grid-newsletters'
        ,url: GoodNews.config.connectorUrl
        ,baseParams: { action: 'mgr/mailing/getList' }
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
            ,'ipc_status'
            ,'scheduled'
            ,'soft_bounces'
            ,'hard_bounces'
            ,'status'
            ,'statusmessage'
            ,'menu'
        ]
        ,emptyText: _('goodnews.newsletters_none')
        ,paging: true
        ,remoteSort: true
        ,plugins: [this.expander]
        ,autoExpandColumn: 'pagetitle'
        ,columns: [
        this.expander
        ,{
            header: _('goodnews.newsletter_title')
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
        },'->',{
            xtype: 'label'
            ,id: 'crontrigger-status'
            ,cls: 'gon-crontrigger-status'
            ,html: cronStatusText
        },'-',{
            xtype: 'button'
            ,id: 'autorefresh'
            ,cls: 'gon-autorefresh-switch'
            ,iconCls: GoodNews.config.legacyMode ? 'gon-icn-autorefresh' : ''
            ,text: _('goodnews.newsletter_grid_autorefresh')
            ,enableToggle: true
            ,listeners: {
                'toggle': {fn:this.toggleAutoRefresh,scope:this}
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
            ,id: 'modx-newsletter-filter-clear'
            ,text: _('goodnews.button_filter_clear')
            ,listeners: {
                'click': {fn: this.clearFilter, scope: this}
            }
        }]
    });
    GoodNews.grid.Newsletters.superclass.constructor.call(this,config);
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
                        text: _('goodnews.newsletter_publish')
                        ,handler: this.publishNewsletter
                    },{
                        text: _('goodnews.newsletter_update')
                        ,handler: this.updateNewsletter
                    },{
                        text: _('goodnews.newsletter_remove')
                        ,handler: this.removeNewsletter
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
                        text: _('goodnews.newsletter_unpublish')
                        ,handler: this.unpublishNewsletter
                    },{
                        text: _('goodnews.newsletter_update')
                        ,handler: this.updateNewsletter
                    },{
                        text: _('goodnews.newsletter_remove')
                        ,handler: this.removeNewsletter
                    }];
                    break;
                case GON_NEWSLETTER_STATUS_NOT_YET_SENT:
                    return [{
                        text: _('goodnews.newsletter_preview')
                        ,handler: this.previewNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_test_send')
                        ,handler: this.testSendNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_start_sending')
                        ,handler: this.startSendNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_unpublish')
                        ,handler: this.unpublishNewsletter
                    },{
                        text: _('goodnews.newsletter_update')
                        ,handler: this.updateNewsletter
                    },{
                        text: _('goodnews.newsletter_remove')
                        ,handler: this.removeNewsletter
                    }];
                    break;
                case GON_NEWSLETTER_STATUS_STOPPED:
                    return [{
                        text: _('goodnews.newsletter_view')
                        ,handler: this.previewNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_continue_sending')
                        ,handler: this.continueSendNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_remove')
                        ,handler: this.removeNewsletter
                    }];
                    break;
                case GON_NEWSLETTER_STATUS_IN_PROGRESS:
                    return [{
                        text: _('goodnews.newsletter_view')
                        ,handler: this.previewNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_stop_sending')
                        ,handler: this.stopSendNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_remove')
                        ,handler: this.removeNewsletter
                    }];
                    break;
                case GON_NEWSLETTER_STATUS_SENT:
                    return [{
                        text: _('goodnews.newsletter_view')
                        ,handler: this.previewNewsletter
                    },'-',{
                        text: _('goodnews.newsletter_remove')
                        ,handler: this.removeNewsletter
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
                        text: _('goodnews.newsletter_publish')
                        ,handler: this.publishNewsletter
                    },{
                        text: _('goodnews.newsletter_update')
                        ,handler: this.updateNewsletter
                    },{
                        text: _('goodnews.newsletter_remove')
                        ,handler: this.removeNewsletter
                    }];
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
    ,toggleAutoRefresh: function(btn) {
        var arb = Ext.getCmp('autorefresh');
        if (btn.pressed) {
            arb.addClass('gon-autorefresh-on');
            tr1.start(gridrefresh);
        } else {
            arb.removeClass('gon-autorefresh-on');
            tr1.stop(gridrefresh);
        }  
    }
    ,clearFilter: function() {
    	this.getStore().baseParams = {
            action: 'mgr/mailing/getList'
    	};
        Ext.getCmp('goodnews-newsletters-filter').reset();
        Ext.getCmp('goodnews-newsletters-search-filter').reset();
    	this.getBottomToolbar().changePage(1);
        this.refresh();
    }
    ,createNewsletter: function(btn,e) {
        var createPage = MODx.action ? MODx.action['resource/create'] : 'resource/create';
        if (GoodNews.config.mailingTemplate) {
            tpl = '&template='+GoodNews.config.mailingTemplate;
        }
        location.href = 'index.php?a='+createPage+'&class_key=GoodNewsResourceMailing&parent='+GoodNews.config.currentContainer+'&context_key='+GoodNews.config.contextKey+tpl;
    }
    ,previewNewsletter: function(btn,e) {
        if (this.menu.record.richtext == true) {
            window.open(this.menu.record.preview_url);
        } else {
            var win = MODx.load({
                xtype: 'goodnews-window-preview'
            });
            win.setValues(this.menu.record);
            win.show(e.target);
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
                action: 'mgr/send/sendtest'
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
                    // todo: handle test sending failure
                    
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
                action: 'mgr/send/start'
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
                action: 'mgr/send/stop'
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
                action: 'mgr/send/continue'
                ,mailingid: this.menu.record.id
            }
            ,listeners: {
                'success':{fn:this.refresh,scope:this}
            }
        });
    }
    ,updateNewsletter: function(btn,e) {
        var editPage = MODx.action ? MODx.action['resource/update'] : 'resource/update';
        location.href = 'index.php?a='+editPage+'&id='+this.menu.record.id;
    }
    ,removeNewsletter: function() {
        MODx.msg.confirm({
            title: _('goodnews.newsletter_remove')
            ,text: _('goodnews.newsletter_remove_confirm')
            ,url: GoodNews.config.legacyMode ? MODx.config.connectors_url+'resource/index.php' : MODx.config.connector_url
            ,params: {
                action: GoodNews.config.legacyMode ? 'delete' : 'resource/delete'
                ,id: this.menu.record.id
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }
    ,undeleteNewsletter: function(btn,e) {
        MODx.Ajax.request({
            url: GoodNews.config.legacyMode ?  MODx.config.connectors_url+'resource/index.php' : MODx.config.connector_url
            ,params: {
                action: GoodNews.config.legacyMode ? 'undelete' : 'resource/undelete'
                ,id: this.menu.record.id
            }
            ,listeners: {
                'success':{fn:this.refresh,scope:this}
            }
        });
    }
    ,publishNewsletter: function(btn,e) {
        MODx.Ajax.request({
            url: GoodNews.config.legacyMode ? MODx.config.connectors_url+'resource/index.php' : MODx.config.connector_url
            ,params: {
                action: GoodNews.config.legacyMode ? 'publish' : 'resource/publish'
                ,id: this.menu.record.id
            }
            ,listeners: {
                'success':{fn:this.refresh,scope:this}
            }
        });
    }
    ,unpublishNewsletter: function(btn,e) {
        MODx.Ajax.request({
            url: GoodNews.config.legacyMode ? MODx.config.connectors_url+'resource/index.php' : MODx.config.connector_url
            ,params: {
                action: GoodNews.config.legacyMode ? 'unpublish' : 'resource/unpublish'
                ,id: this.menu.record.id
            }
            ,listeners: {
                'success':{fn:this.refresh,scope:this}
            }
        });
    }
	,_renderPageTitle:function(v,md,rec) {
        this.tplPageTitle = new Ext.XTemplate(
            '<tpl for=".">'
                +'<h3 class="gon-newsletter-title">{pagetitle}</h3>'
            +'</tpl>'
		,{compiled:true});
		return this.tplPageTitle.apply(rec.data);
	}
    /*
    ,viewLog: function(btn,e) {
        this.NewsletterLogWindow = MODx.load({
            xtype: 'goodnews-window-newsletter-log'
            ,params: {
                mailingid: this.menu.record.id
            }
            //,listeners: {
            //    'success': {fn:this.refresh,scope:this}
            //}
        });
        //this.SendNewsletterWindow.setValues(this.menu.record);
        this.NewsletterLogWindow.show(e.target);
    }
    */
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


/*
GoodNews.window.NewsletterLogWindow = function(config) {
    config = config || {};
    //console.log(this);

    var logsrc = GoodNews.config.assetsUrl+'log/nl'+config.params.mailingid+'.log'
    
    Ext.applyIf(config,{
        title: _('goodnews.newsletter_send_log_window_title')+config.params.mailingid
        ,id: 'goodnews-window-newsletter-log'
        ,width: 640
        ,height: 480
        ,modal: false
        ,items : [{
            xtype : "component"
            ,id : 'newsletter-log-frame'
            ,autoEl : {
                tag : "iframe",
                src : logsrc
            }
        }]
        ,buttons: [{ 
            text: _('goodnews.newsletter_send_log_close_button')
            ,handler: function() {
                tr2.stop(logrefresh);
                console.log('logrefresh: STOP');
                this.close();
            }
            ,scope: this
        }]
    });
    GoodNews.window.NewsletterLogWindow.superclass.constructor.call(this,config);
    
    var tr2 = new Ext.util.TaskRunner;
    var logrefresh = {
        run: function(){
            Ext.getCmp('newsletter-log-frame').reload(); // bug!!!
            //document.getElementById('newsletter-log-frame').contentWindow.location.reload();
        }
        ,interval: 1000 // = 1 second
    }
    tr2.start(logrefresh);
    console.log('logrefresh: START');
 
};
Ext.extend(GoodNews.window.NewsletterLogWindow,Ext.Window);
Ext.reg('goodnews-window-newsletter-log',GoodNews.window.NewsletterLogWindow);
*/
