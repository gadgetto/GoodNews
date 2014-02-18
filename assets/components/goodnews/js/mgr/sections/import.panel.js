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
        ,unstyled: true
        ,fileUpload: true
        ,items: [{
            html: '<h2 class="gon-cmp-header gon-logo">'+_('goodnews.import')+'</h2>'
            ,border: false
            ,cls: 'modx-page-header'
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
            renderTo: 'modAB'
            ,id: 'modx-action-buttons'
            ,defaults: { scope: this }
            ,items: this.getButtons()
        });                                
        this.actionToolbar.doLayout();
    }
    ,startSubscriberImport: function(){

        if (this.console == null || this.console == undefined) {
            this.console = MODx.load({
                xtype: 'modx-console'
                ,register: register
                ,topic: topic
                ,listeners: {
                    'shutdown': {fn:function() {
                        //refresh page to reset fields
                        //location.href = MODx.config.manager_url + '?a=' + MODx.request.a + '&action=import';
                    }
                    ,scope:this}
                }
            });
        } else {
            this.console.setRegister(register,topic);
        }
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
                action: 'mgr/subscribers/import'
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
        buttons.push({
            text: _('goodnews.import_subscribers_button_start')
            ,id: 'button-import-start'
            ,cls: 'primary-button'
            ,handler: this.startSubscriberImport
            ,scope: this
        },'-')
        buttons.push({
            text: _('goodnews.import_close_button')
            ,id: 'button-import-close'
            ,iconCls: version_compare(MODx.config.version, '2.3.0-dev', '>=') ? '' : 'gon-icn-close'
            ,handler: this.closeImport
            ,scope: this
        },'-')
        buttons.push({
            text: _('help_ex')
            ,id: 'button-help'
            ,iconCls: version_compare(MODx.config.version, '2.3.0-dev', '>=') ? '' : 'gon-icn-help'
            ,handler: function(){
                MODx.config.help_url = GoodNews.config.helpUrl;
                MODx.loadHelpPane();
            }
            ,scope: this
        })
        return buttons;
    }
    ,closeImport: function(){
        location.href = MODx.config.manager_url + '?a=' + MODx.request.a;
    }
});
Ext.reg('goodnews-panel-import',GoodNews.ImportPanel);
