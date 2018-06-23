/**
 * FormPanel to handle error tabs (tabs container).
 * 
 * @class GoodNews.ErrorPanel
 * @extends MODx.FormPanel
 * @param {Object} config An object of options.
 * @xtype goodnews-panel-error
 */
GoodNews.ErrorPanel = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        id: 'goodnews-panel-error'
        ,cls: 'container'
        ,bodyStyle: ''
        ,unstyled: true
        ,items: [{
            html: '<h2 class="gon-cmp-header gon-logo">'+_('goodnews.error')+'</h2>'
            ,border: false
            ,cls: 'modx-page-header'
        },{
            xtype: 'modx-tabs'
            ,itemId: 'tabs'
			,cls: 'structure-tabs'
            ,stateful: true
            ,stateId: 'goodnews-panel-error'
            ,stateEvents: ['tabchange']
            ,getState: function() {
                return {
                    activeTab:this.items.indexOf(this.getActiveTab())
                };
            }
            ,items: [{
                xtype: 'goodnews-panel-error-message'
            }]
            ,listeners: {
                'tabchange': {fn: function(panel) {
                    panel.doLayout();
                }
                ,scope: this}
            }
        }]
    });
    GoodNews.ErrorPanel.superclass.constructor.call(this,config);
    this.init();
};
Ext.extend(GoodNews.ErrorPanel,MODx.FormPanel,{
    init: function(){
        this.actionToolbar = new Ext.Toolbar({
            renderTo: 'modAB'
            ,id: 'modx-action-buttons'
            ,defaults: { scope: this }
            ,items: this.getButtons()
        });                                
        this.actionToolbar.doLayout();
    }
    ,getButtons: function() {
        var buttons = [];
        // Plugin version
        buttons.push('-',{
            xtype: 'tbtext'
            ,html: '<i>'+GoodNews.config.componentVersion+'-'+GoodNews.config.componentRelease+'</i>'
        },'-')
        // Help button
        buttons.push({
            text: (GoodNews.config.legacyMode ? '' : '<i class="icon icon-question-circle icon-lg"></i>&nbsp;') + _('help_ex')
            ,id: 'button-help'
            ,iconCls: GoodNews.config.legacyMode ? 'gon-icn-help' : ''
            ,handler: function(){
                MODx.config.help_url = GoodNews.config.helpUrl;
                MODx.loadHelpPane();
            }
            ,scope: this
        })
        return buttons;
    }
});
Ext.reg('goodnews-panel-error',GoodNews.ErrorPanel);
