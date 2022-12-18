/**
 * Panel to handle main tabs (tabs container).
 * 
 * @class GoodNews.HomePanel
 * @extends MODx.Panel
 * @param {Object} config An object of options.
 * @xtype goodnews-panel-home
 */
GoodNews.HomePanel = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        id: 'goodnews-panel-home'
        ,cls: 'container'
        ,bodyStyle: ''
        ,items: [{
            html: _('goodnews.management')
            ,xtype: 'modx-header'
        },{
            xtype: 'modx-tabs'
            ,itemId: 'tabs'
            ,cls: 'structure-tabs'
            /*
            // State currently disabled:
            // @todo: Problem with row expander in goodnews-panel-newsletters (not working) if 
            // newsletter panel isn't the actual panel when CMP is loaded
            ,stateful: true
            ,stateId: 'goodnews-panel-home'
            ,stateEvents: ['tabchange']
            ,getState: function() {
                return {
                    activeTab: this.items.indexOf(this.getActiveTab())
                };
            }
            */
            ,items: [{
                xtype: 'goodnews-panel-newsletters'
            },{
                xtype: 'goodnews-panel-subscribers'
            },{
                xtype: 'goodnews-panel-groups'
            },{
                xtype: 'goodnews-panel-categories'
            }]
            ,listeners: {
                'tabchange': {fn: function(panel) {
                    panel.doLayout();
                }
                ,scope: this}
            }
        }]
    });
    GoodNews.HomePanel.superclass.constructor.call(this,config);
    this.init();
};
Ext.extend(GoodNews.HomePanel,MODx.Panel,{
    init: function(){
        this.actionToolbar = new Ext.Toolbar({
            renderTo: 'modx-action-buttons-container'
            ,id: 'modx-action-buttons'
            ,defaults: { scope: this }
            ,items: this.getElements()
        });                                
        this.actionToolbar.doLayout();

        // A taskrunner to update the cron ping display in actionToolbar
        var cronPingDisplay = Ext.getCmp('goodnews-cron-ping-display');
        var lastPing = 0;
        var cronPingTr = new Ext.util.TaskRunner;
        var cronPingUpdate = {
            run: function(){
                MODx.Ajax.request({
                    url: GoodNews.config.connectorUrl
                    ,params: {
                        action: 'Bitego\\GoodNews\\Processors\\Mailing\\Cron\\Ping'
                    }
                    ,method: 'post'
                    ,scope: this
                    ,listeners: {
                        'success':{fn:function(r) {
                            cronPingDisplay.setText(r.message);
                        },scope:this}
                    }
                });
            }
            ,interval: 500 // milliseconds
        }
        cronPingTr.start(cronPingUpdate);
    }
    ,getElements: function() {
        var elements = [];
        // Cron ping display
        elements.push('-',{
            xtype: 'tbtext'
            ,id: 'goodnews-cron-ping-display'
            ,text: _('goodnews.task_scheduler_touch_waiting')
            ,listeners: {
                'afterrender':{fn:function() {
                    Ext.QuickTips.register({
                        target:  'goodnews-cron-ping-display'
                        ,text: _('goodnews.task_scheduler_touch_desc')
                        ,enabled: true
                        ,dismissDelay: 10000
                    });
                },scope:this}
            }
        },'-')
        // Dropdown for choosing a GoodNews container
        elements.push({
            xtype: 'modx-combo'
            ,id: 'goodnews-choose-container'
            ,emptyText: _('goodnews.choose_container')
            ,width: 200
            ,listWidth: 200
            ,ctCls: 'gon-choose-container'
            ,name: 'container'
            ,hiddenName: 'container'
            ,valueField: 'id'
            ,displayField: 'name'
            ,value: GoodNews.config.userCurrentContainer
            ,editable: false
            ,selectOnFocus: false
            ,preventRender: false
            ,forceSelection: true
            ,enableKeyEvents: true
            ,triggerAction: 'all'
            ,store: new Ext.data.JsonStore({
                url: GoodNews.config.connectorUrl
                ,baseParams: {
                    action : 'Bitego\\GoodNews\\Processors\\Container\\SimpleGetList'
                    ,containerIDs: GoodNews.config.userAvailableContainers
                }
                ,fields: ['id','name']
                ,root: 'results'
            })
            ,listeners: {
                'select': {fn:this.setUserCurrentContainer,scope:this}
            }
        },'-')
        // Settings button
        if (GoodNews.config.isGoodNewsAdmin) {
            elements.push({
                text: '<i class="icon icon-cog icon-lg"></i>'
                ,id: 'button-settings'
                ,handler: this.loadSettingsPanel
                ,scope: this
            },'-')
        }
        // Help button
        elements.push({
            text: '<i class="icon icon-question-circle icon-lg"></i>&nbsp;' + _('help_ex')
            ,id: 'button-help'
            ,handler: function(){
                MODx.config.help_url = GoodNews.config.helpUrl;
                MODx.loadHelpPane();
            }
            ,scope: this
        })
        return elements;
    }
    ,loadSettingsPanel: function() {
        if (GoodNews.config.isGoodNewsAdmin) {
            MODx.loadPage('settings', 'namespace=goodnews');
        }
    }
    ,setUserCurrentContainer: function(cb) {
        MODx.Ajax.request({
            url: GoodNews.config.connectorUrl
            ,params: {
                action: 'Bitego\\GoodNews\\Processors\\Container\\SwitchContainer'
                ,containerid: cb.getValue()
            }
            ,method: 'post'
            ,scope: this
            ,listeners: {
                'success':{fn:function() {
                    MODx.msg.status({
                        title: _('loading')
                        ,message: _('goodnews.container_switching')
                        ,delay: 2
                    })
                    Ext.getCmp('goodnews-grid-newsletters').refresh();
                    GoodNews.config.userCurrentContainer = cb.getValue(); // this is for changing parent parameter on Create Newsletter button on the fly
                },scope:this}
            }
        });
    }
});
Ext.reg('goodnews-panel-home',GoodNews.HomePanel);
