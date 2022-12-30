/**
 * FormPanel to handle import tabs (tabs container).
 * 
 * @class GoodNews.ImportPanel
 * @extends MODx.FormPanel
 * @param {Object} config An object of options.
 * @xtype goodnews-panel-import
 */

var topic = '/goodnewsimport/';
var register = 'mgr';

GoodNews.ImportPanel = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        id: 'goodnews-panel-import'
        ,cls: 'container'
        ,bodyStyle: ''
        ,fileUpload: true
        ,items: [{
            html: _('goodnews.import')
            ,xtype: 'modx-header'
        },{
            xtype: 'modx-tabs'
            ,itemId: 'tabs'
			,cls: 'structure-tabs'
            ,stateful: true
            ,stateId: 'goodnews-panel-import'
            ,stateEvents: ['tabchange']
            ,getState: function() {
                return {
                    activeTab:this.items.indexOf(this.getActiveTab())
                };
            }
            ,items: [{
                xtype: 'goodnews-panel-import-subscribers'
            }]
        }]
    });
    Ext.Ajax.timeout = 0;
    GoodNews.ImportPanel.superclass.constructor.call(this,config);
    this.init();
};
Ext.extend(GoodNews.ImportPanel,MODx.FormPanel,{
    init: function(){
        this.actionToolbar = new Ext.Toolbar({
            renderTo: 'modx-action-buttons-container'
            ,id: 'modx-action-buttons'
            ,defaults: { scope: this }
            ,items: this.getButtons()
        });                                
        this.actionToolbar.doLayout();
    }
    ,startSubscriberImport: function(){

        this.console = MODx.load({
            xtype: 'modx-console'
            ,register: register
            ,topic: topic
            ,closeAction: 'close'
            ,listeners: {
                'shutdown': {fn:function() {
                    //refresh page to reset fields
                    //MODx.loadPage('import', 'namespace=goodnews');
                }
                ,scope:this}
            }
        });
        this.console.show(Ext.getBody());

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
        // write selected nodes to hidden field
        this.getForm().setValues({
          groupscategories: nodeIDs
        });
        
        this.getForm().submit({
            url: GoodNews.config.connectorUrl
            ,params: {
                action: 'Bitego\\GoodNews\\Processors\\Subscribers\\Import'
                ,register: register
                ,topic: topic
            }
            ,success: function(form,action){
                if(action.result.success){
                    this.console.fireEvent('complete');
                }
            }
            ,failure: function(result,request) {
                this.console.fireEvent('complete');
            }
            ,scope: this
        });
    }
    ,getButtons: function() {
        var buttons = [];
        // Plugin version
        buttons.push({
            xtype: 'tbtext'
            ,html: '<i>'+GoodNews.config.componentVersion+'-'+GoodNews.config.componentRelease+'</i>'
        })
        // Start Import button
        buttons.push({
            text: '<i class="icon icon-download icon-lg"></i>&nbsp;' + _('goodnews.import_subscribers_button_start')
            ,id: 'button-import-start'
            ,cls: 'primary-button'
            ,handler: this.startSubscriberImport
            ,scope: this
        })
        // Close Import button
        buttons.push({
            text: '<i class="icon icon-arrow-circle-left icon-lg"></i>&nbsp;' + _('goodnews.import_close_button')
            ,id: 'button-import-close'
            ,handler: this.closeImport
            ,scope: this
        })
        // Help button
        buttons.push({
            text: '<i class="icon icon-question-circle icon-lg"></i>&nbsp;' + _('help_ex')
            ,id: 'button-help'
            ,handler: function(){
                MODx.config.help_url = GoodNews.config.helpUrl;
                MODx.loadHelpPane();
            }
            ,scope: this
        })
        return buttons;
    }
    ,closeImport: function(){
        MODx.loadPage('index', 'namespace=goodnews');
    }
});
Ext.reg('goodnews-panel-import',GoodNews.ImportPanel);
